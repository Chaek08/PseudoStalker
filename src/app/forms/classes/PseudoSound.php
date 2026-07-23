<?php
namespace app\forms\classes;

use Throwable;
use script\MediaPlayerScript;
use app\forms\classes\Log;

/**
 * PseudoSound — менеджер звука для DevelNext/JPHP.
 *
 * Ключевые отличия от старой версии:
 *  - никаких потоков: вся работа с плеерами идёт в GUI-потоке (тикер);
 *  - никаких unset() внутри foreach по ссылке — итерация по снимку ключей;
 *  - mute-состояния (master / канал / тип) не смешаны с фейдом:
 *    громкость всегда пересчитывается из текущего состояния, поэтому
 *    рассинхрон невозможен в принципе;
 *  - пул каналов вычисляется из живых плееров, а не из отдельного счётчика,
 *    который мог «протечь»;
 *  - любые ошибки плеера ловятся, битые записи автоматически удаляются.
 */
class PseudoSound
{
    const TYPE_SFX   = 'sfx';
    const TYPE_MUSIC = 'music';

    /** Что делать, если в канале уже что-то играет */
    const MODE_REPLACE = 'replace'; // погасить старое и запустить новое (по умолчанию)
    const MODE_LAYER   = 'layer';   // играть поверх
    const MODE_IGNORE  = 'ignore';  // не запускать, пока канал занят

    /** @var PseudoSound */
    protected static $instance;

    /** @var array [channel => [id => entry]] */
    protected $players = [];
    protected $nextId  = 1;

    protected $enabled = true;

    protected $masterVolume   = 1.0;
    protected $typeVolumes    = [];
    protected $channelVolumes = [];

    protected $masterMuted  = false;
    protected $channelMuted = [];
    protected $typeMuted    = [];

    protected $maxPerChannel = 8;
    protected $maxTotal      = 40;

    protected $cooldowns = [];
    protected $poolIndex = [];

    protected $lastTick     = 0.0;
    protected $ticker       = null;
    protected $tickerFailed = false;
    protected $tickMs       = 30;

    protected $defaultFade   = 0.15;
    protected $defaultSfxTtl = 30.0;
    protected $statusProbe   = true;

    protected function __construct()
    {
        $this->typeVolumes = [
            self::TYPE_MUSIC => 0.2,
            self::TYPE_SFX   => 0.3,
        ];
    }

    /** @return PseudoSound */
    public static function get()
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // ------------------------------------------------------------------
    // ВОСПРОИЗВЕДЕНИЕ
    // ------------------------------------------------------------------

    /**
     * Совместимо со старой сигнатурой, плюс необязательный $options:
     *   mode        — MODE_REPLACE | MODE_LAYER | MODE_IGNORE
     *   fadeIn      — плавное появление, сек
     *   fadeOut     — с какой скоростью гасить то, что играло раньше, сек
     *   ttl         — страховочное время жизни для не-зацикленных, сек (0 = без лимита)
     *   pool        — размер пула каналов; канал станет "<channel>_N"
     *   cooldownKey — ключ кулдауна (по умолчанию имя канала)
     *
     * @return int|null id запущенного звука
     */
    public static function play(
        string $path,
        string $channel = 'default',
        bool $loop = false,
        ?string $type = null,
        bool $forceUnmute = false,
        int $cooldownMs = 0,
        float $volume = 1.0,
        array $options = []
    )
    {
        return self::get()->doPlay($path, $channel, $loop, $type, $forceUnmute, $cooldownMs, $volume, $options);
    }

    /** Короткий звук поверх остальных. */
    public static function playSfx(string $path, string $channel = 'sfx', float $volume = 1.0, int $cooldownMs = 0)
    {
        return self::play($path, $channel, false, self::TYPE_SFX, false, $cooldownMs, $volume, [
            'mode' => self::MODE_LAYER,
        ]);
    }

    /** Зацикленная музыка с плавным появлением; старая в этом канале плавно гаснет. */
    public static function playMusic(string $path, string $channel = 'music', float $fadeIn = 0.8, float $volume = 1.0)
    {
        return self::play($path, $channel, true, self::TYPE_MUSIC, false, 0, $volume, [
            'mode'    => self::MODE_REPLACE,
            'fadeIn'  => $fadeIn,
            'fadeOut' => max(0.2, $fadeIn),
        ]);
    }

    /** Звук в пуле каналов: одинаковые сэмплы не перебивают друг друга. */
    public static function playPooled(string $path, string $base = 'sfx', int $size = 8, float $volume = 1.0, int $cooldownMs = 0)
    {
        return self::play($path, $base, false, self::TYPE_SFX, false, $cooldownMs, $volume, [
            'pool' => $size,
        ]);
    }

    protected function doPlay($path, $channel, $loop, $type, $forceUnmute, $cooldownMs, $volume, array $o)
    {
        if (!$this->isEnabled())
        {
            return null;
        }

        $path = (string) $path;

        if ($path === '')
        {
            $this->logError('пустой путь к файлу (channel=' . $channel . ')');
            return null;
        }

        if ($channel === '')
        {
            $channel = 'default';
        }

        if ($type === null)
        {
            $type = $loop ? self::TYPE_MUSIC : self::TYPE_SFX;
        }

        $mode    = isset($o['mode'])    ? $o['mode']            : self::MODE_REPLACE;
        $fadeIn  = isset($o['fadeIn'])  ? (float) $o['fadeIn']  : 0.0;
        $fadeOut = isset($o['fadeOut']) ? (float) $o['fadeOut'] : $this->defaultFade;
        $ttl     = isset($o['ttl'])     ? (float) $o['ttl']     : ($loop ? 0.0 : $this->defaultSfxTtl);

        $now = microtime(true);

        if ($cooldownMs > 0)
        {
            $key = isset($o['cooldownKey']) ? (string) $o['cooldownKey'] : $channel;

            if (isset($this->cooldowns[$key]) && ($now - $this->cooldowns[$key]) * 1000 < $cooldownMs)
            {
                return null;
            }

            $this->cooldowns[$key] = $now;
        }

        $poolBase  = '';
        $poolIndex = -1;

        if (isset($o['pool']))
        {
            $poolBase  = $channel;
            $poolIndex = $this->allocPoolSlot($poolBase, max(1, (int) $o['pool']));
            $channel   = $poolBase . '_' . $poolIndex;
        }

        if ($mode === self::MODE_IGNORE && $this->hasLive($channel))
        {
            return null;
        }

        if ($mode !== self::MODE_LAYER)
        {
            $this->fadeOutChannel($channel, $fadeOut);
        }

        if ($forceUnmute)
        {
            $this->channelMuted[$channel] = false;
        }

        $this->enforceLimits($channel);

        try
        {
            $player = new MediaPlayerScript();
            $player->open($path);
            $player->loop = $loop;
        }
        catch (Throwable $e)
        {
            $this->logError('не удалось открыть: ' . $path);
            return null;
        }

        $id = $this->nextId++;

        $entry = [
            'id'        => $id,
            'player'    => $player,
            'path'      => $path,
            'type'      => $type,
            'channel'   => $channel,
            'loop'      => $loop,
            'gain'      => max(0.0, min(1.0, $volume)),
            'env'       => 0.0,
            'fadeTime'  => $fadeIn,
            'stopping'  => false,
            'born'      => $now,
            'ttl'       => max(0.0, $ttl),
            'poolBase'  => $poolBase,
            'poolIndex' => $poolIndex,
            'errors'    => 0,
            'applied'   => -1.0,
        ];

        if ($fadeIn <= 0.0)
        {
            $entry['env'] = $this->isSilenced($channel, $type) ? 0.0 : 1.0;
        }

        $this->applyVolume($entry);

        try
        {
            $player->play();
        }
        catch (Throwable $e)
        {
            $this->logError('не удалось запустить: ' . $path);
            $this->safeStop($player);
            return null;
        }

        $this->players[$channel][$id] = $entry;
        $this->ensureTicker();

        return $id;
    }

    // ------------------------------------------------------------------
    // ОСТАНОВКА
    // ------------------------------------------------------------------

    /** Плавно погасить и удалить всё в канале. */
    public static function stop(string $channel, float $fade = 0.2)
    {
        self::get()->fadeOutChannel($channel, $fade);
    }

    /** Мгновенно оборвать канал. */
    public static function stopInstant(string $channel)
    {
        $self = self::get();

        if (!isset($self->players[$channel]))
        {
            return;
        }

        foreach (array_keys($self->players[$channel]) as $id)
        {
            $self->destroyEntry($channel, $id);
        }

        unset($self->players[$channel]);
    }

    /** Погасить вообще всё. */
    public static function stopAll(float $fade = 0.2)
    {
        $self = self::get();

        foreach (array_keys($self->players) as $channel)
        {
            $self->fadeOutChannel($channel, $fade);
        }
    }

    /** Погасить все звуки заданного типа. */
    public static function stopType(string $type, float $fade = 0.2)
    {
        $self = self::get();

        foreach (array_keys($self->players) as $channel)
        {
            foreach (array_keys($self->players[$channel]) as $id)
            {
                if ($self->players[$channel][$id]['type'] === $type)
                {
                    $self->players[$channel][$id]['stopping'] = true;
                    $self->players[$channel][$id]['fadeTime'] = max(0.0, $fade);
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // MUTE
    // ------------------------------------------------------------------

    public static function muteChannel(string $channel, bool $smooth = true, float $duration = 0.6)
    {
        $self = self::get();
        $self->channelMuted[$channel] = true;
        $self->setFadeTimeForChannel($channel, $smooth ? $duration : 0.0);
    }

    public static function unmuteChannel(string $channel, bool $smooth = true, float $duration = 0.4)
    {
        $self = self::get();
        $self->channelMuted[$channel] = false;
        $self->setFadeTimeForChannel($channel, $smooth ? $duration : 0.0);
    }

    public static function muteType(string $type, float $duration = 0.4)
    {
        $self = self::get();
        $self->typeMuted[$type] = true;
        $self->setFadeTimeForType($type, $duration);
    }

    public static function unmuteType(string $type, float $duration = 0.4)
    {
        $self = self::get();
        $self->typeMuted[$type] = false;
        $self->setFadeTimeForType($type, $duration);
    }

    public static function muteSfx(float $duration = 0.4)   { self::muteType(self::TYPE_SFX, $duration); }
    public static function unmuteSfx(float $duration = 0.4) { self::unmuteType(self::TYPE_SFX, $duration); }
    public static function muteMusic(float $duration = 0.6)   { self::muteType(self::TYPE_MUSIC, $duration); }
    public static function unmuteMusic(float $duration = 0.6) { self::unmuteType(self::TYPE_MUSIC, $duration); }

    public static function muteAll(bool $mute = true, float $duration = 0.3)
    {
        $self = self::get();
        $self->masterMuted = $mute;

        foreach (array_keys($self->players) as $channel)
        {
            $self->setFadeTimeForChannel($channel, $duration);
        }
    }

    // ------------------------------------------------------------------
    // ГРОМКОСТЬ И НАСТРОЙКИ
    // ------------------------------------------------------------------

    public static function setMasterVolume(float $v)
    {
        self::get()->masterVolume = max(0.0, min(1.0, $v));
        self::get()->refreshVolumes();
    }

    public static function setTypeVolume(string $type, float $v)
    {
        self::get()->typeVolumes[$type] = max(0.0, min(1.0, $v));
        self::get()->refreshVolumes();
    }

    public static function setChannelVolume(string $channel, float $v)
    {
        self::get()->channelVolumes[$channel] = max(0.0, min(1.0, $v));
        self::get()->refreshVolumes();
    }

    /**
     * Глобальный выключатель. При выключении всё плавно гаснет и выгружается,
     * поэтому после setEnabled(true) музыку нужно запустить заново.
     */
    public static function setEnabled(bool $enabled, float $fade = 0.25)
    {
        $self = self::get();

        if ($self->enabled === $enabled)
        {
            return;
        }

        $self->enabled = $enabled;

        if (!$enabled)
        {
            foreach (array_keys($self->players) as $channel)
            {
                $self->setFadeTimeForChannel($channel, $fade);
            }
        }
    }

    public static function isEnabledNow(): bool
    {
        return self::get()->isEnabled();
    }

    /** limits: maxPerChannel, maxTotal, tickMs, defaultSfxTtl, statusProbe */
    public static function configure(array $opts)
    {
        $self = self::get();

        foreach (['maxPerChannel', 'maxTotal', 'tickMs'] as $k)
        {
            if (isset($opts[$k])) $self->$k = max(1, (int) $opts[$k]);
        }

        if (isset($opts['defaultSfxTtl'])) $self->defaultSfxTtl = max(0.0, (float) $opts['defaultSfxTtl']);
        if (isset($opts['statusProbe']))   $self->statusProbe   = (bool) $opts['statusProbe'];
    }

    public static function isPlaying(string $channel = ''): bool
    {
        $self = self::get();

        if ($channel !== '')
        {
            return $self->hasLive($channel);
        }

        foreach (array_keys($self->players) as $ch)
        {
            if ($self->hasLive($ch)) return true;
        }

        return false;
    }

    /** Для отладки: сколько плееров живо и где. */
    public static function stats(): array
    {
        $self  = self::get();
        $out   = ['total' => 0, 'channels' => []];

        foreach ($self->players as $channel => $list)
        {
            $out['channels'][$channel] = count($list);
            $out['total'] += count($list);
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // ТИКЕР
    // ------------------------------------------------------------------

    /**
     * Вызывать каждые ~30 мс из GUI-потока.
     * Если тикер не смог запуститься сам — дёргайте этот метод из TimerScript.
     */
    public static function update()
    {
        $self = self::get();
        $now  = microtime(true);

        $dt = ($self->lastTick > 0.0) ? ($now - $self->lastTick) : 0.016;
        $self->lastTick = $now;

        if ($dt < 0.0)  $dt = 0.0;
        if ($dt > 0.25) $dt = 0.25; // защита от «прыжка» после фриза/паузы отладчика

        foreach (array_keys($self->players) as $channel)
        {
            if (!isset($self->players[$channel]))
            {
                continue;
            }

            foreach (array_keys($self->players[$channel]) as $id)
            {
                if (!isset($self->players[$channel][$id]))
                {
                    continue;
                }

                $self->tickEntry($channel, $id, $now, $dt);
            }

            if (empty($self->players[$channel]))
            {
                unset($self->players[$channel]);
            }
        }
    }

    public static function startTicker(int $ms = 0)
    {
        $self = self::get();

        if ($ms > 0)
        {
            $self->tickMs = $ms;
        }

        $self->tickerFailed = false;
        $self->ensureTicker();

        return $self->ticker !== null;
    }

    public static function stopTicker()
    {
        $self = self::get();

        if ($self->ticker === null)
        {
            return;
        }

        foreach (['cancel', 'free', 'stop'] as $m)
        {
            try
            {
                if (method_exists($self->ticker, $m))
                {
                    $self->ticker->$m();
                    break;
                }
            }
            catch (Throwable $e) {}
        }

        $self->ticker = null;
    }

    protected function ensureTicker()
    {
        if ($this->ticker !== null || $this->tickerFailed)
        {
            return;
        }

        try
        {
            if (class_exists('php\\gui\\framework\\Timer'))
            {
                $this->ticker = \php\gui\framework\Timer::every($this->tickMs, function ()
                {
                    PseudoSound::update();
                });
            }
            else
            {
                $this->tickerFailed = true;
            }
        }
        catch (Throwable $e)
        {
            $this->tickerFailed = true;
            $this->logError('автотикер недоступен, вызывайте PseudoSound::update() вручную');
        }
    }

    // ------------------------------------------------------------------
    // ВНУТРЕННЕЕ
    // ------------------------------------------------------------------

    protected function tickEntry($channel, $id, $now, $dt)
    {
        $e = $this->players[$channel][$id];

        if (empty($e['player']))
        {
            $this->destroyEntry($channel, $id);
            return;
        }

        $target = ($e['stopping'] || $this->isSilenced($channel, $e['type'])) ? 0.0 : 1.0;

        if ($e['env'] !== $target)
        {
            $step = ($e['fadeTime'] > 0.0) ? ($dt / $e['fadeTime']) : 1.0;

            $e['env'] = ($e['env'] < $target)
                ? min($target, $e['env'] + $step)
                : max($target, $e['env'] - $step);

            if ($e['env'] === $target)
            {
                $e['fadeTime'] = $this->defaultFade;
            }

            $this->applyVolume($e);
        }

        $this->players[$channel][$id] = $e;

        // 1) отыграл фейд-аут после stop()/replace
        if ($e['stopping'] && $e['env'] <= 0.0001)
        {
            $this->destroyEntry($channel, $id);
            return;
        }

        // 2) звук выключили глобально — выгружаем, чтобы не висел в памяти
        if (!$this->isEnabled() && $e['env'] <= 0.0001)
        {
            $this->destroyEntry($channel, $id);
            return;
        }

        // 3) плеер начал сыпать ошибками
        if ($e['errors'] >= 5)
        {
            $this->destroyEntry($channel, $id);
            return;
        }

        if ($e['loop'])
        {
            return;
        }

        $alive = $now - $e['born'];

        // 4) плеер сам сообщил, что закончил
        if ($this->statusProbe && $alive > 1.0 && $this->isFinished($e['player']))
        {
            $this->destroyEntry($channel, $id);
            return;
        }

        // 5) страховка на случай, если статус недоступен
        if ($e['ttl'] > 0.0 && $alive >= $e['ttl'])
        {
            $this->destroyEntry($channel, $id);
        }
    }

    protected function destroyEntry($channel, $id)
    {
        if (!isset($this->players[$channel][$id]))
        {
            return;
        }

        $entry = $this->players[$channel][$id];
        unset($this->players[$channel][$id]);

        $this->safeStop(isset($entry['player']) ? $entry['player'] : null);

        if (isset($this->players[$channel]) && empty($this->players[$channel]))
        {
            unset($this->players[$channel]);
        }
    }

    protected function fadeOutChannel($channel, $fade)
    {
        if (!isset($this->players[$channel]))
        {
            return;
        }

        foreach (array_keys($this->players[$channel]) as $id)
        {
            $this->players[$channel][$id]['stopping'] = true;
            $this->players[$channel][$id]['fadeTime'] = max(0.0, (float) $fade);
        }

        $this->ensureTicker();
    }

    protected function setFadeTimeForChannel($channel, $duration)
    {
        if (!isset($this->players[$channel]))
        {
            return;
        }

        foreach (array_keys($this->players[$channel]) as $id)
        {
            $this->players[$channel][$id]['fadeTime'] = max(0.0, (float) $duration);
        }

        $this->ensureTicker();
    }

    protected function setFadeTimeForType($type, $duration)
    {
        foreach (array_keys($this->players) as $channel)
        {
            foreach (array_keys($this->players[$channel]) as $id)
            {
                if ($this->players[$channel][$id]['type'] === $type)
                {
                    $this->players[$channel][$id]['fadeTime'] = max(0.0, (float) $duration);
                }
            }
        }

        $this->ensureTicker();
    }

    protected function refreshVolumes()
    {
        foreach (array_keys($this->players) as $channel)
        {
            foreach (array_keys($this->players[$channel]) as $id)
            {
                $e = $this->players[$channel][$id];
                $e['applied'] = -1.0;
                $this->applyVolume($e);
                $this->players[$channel][$id] = $e;
            }
        }
    }

    protected function applyVolume(array &$entry)
    {
        $v = $this->resolveVolume($entry);

        if (abs($v - $entry['applied']) < 0.001)
        {
            return;
        }

        try
        {
            $entry['player']->volume = $v;
            $entry['applied'] = $v;
        }
        catch (Throwable $e)
        {
            $entry['errors']++;
        }
    }

    protected function resolveVolume(array $entry): float
    {
        $type    = $entry['type'];
        $channel = $entry['channel'];

        $v = $this->masterVolume
            * (isset($this->typeVolumes[$type]) ? $this->typeVolumes[$type] : 1.0)
            * (isset($this->channelVolumes[$channel]) ? $this->channelVolumes[$channel] : 1.0)
            * $entry['gain'];

        $env = max(0.0, min(1.0, $entry['env']));
        $v  *= $env * $env; // мягкая кривая нарастания

        return max(0.0, min(1.0, $v));
    }

    protected function isSilenced($channel, $type): bool
    {
        if (!$this->isEnabled())  return true;
        if ($this->masterMuted)   return true;
        if (!empty($this->channelMuted[$channel])) return true;
        if (!empty($this->typeMuted[$type]))       return true;

        return false;
    }

    protected function isEnabled(): bool
    {
        if (!$this->enabled)
        {
            return false;
        }

        // совместимость со старым глобальным флагом
        if (isset($GLOBALS['AllSounds']) && !$GLOBALS['AllSounds'])
        {
            return false;
        }

        return true;
    }

    protected function hasLive($channel): bool
    {
        if (empty($this->players[$channel]))
        {
            return false;
        }

        foreach ($this->players[$channel] as $e)
        {
            if (empty($e['stopping'])) return true;
        }

        return false;
    }

    protected function countAll(): int
    {
        $n = 0;

        foreach ($this->players as $list)
        {
            $n += count($list);
        }

        return $n;
    }

    protected function enforceLimits($channel)
    {
        $guard = 0;

        while ($this->countAll() >= $this->maxTotal && $guard++ < 100)
        {
            if (!$this->killOldest(null)) break;
        }

        $guard = 0;

        while (isset($this->players[$channel])
            && count($this->players[$channel]) >= $this->maxPerChannel
            && $guard++ < 100)
        {
            if (!$this->killOldest($channel)) break;
        }
    }

    /** Сначала жертвуем тем, что уже гаснет, потом самым старым. */
    protected function killOldest($onlyChannel): bool
    {
        $bestCh = null;
        $bestId = null;
        $bestScore = null;

        foreach ($this->players as $channel => $list)
        {
            if ($onlyChannel !== null && $channel !== $onlyChannel)
            {
                continue;
            }

            foreach ($list as $id => $e)
            {
                $score = $e['born'] - (empty($e['stopping']) ? 0 : 1e9);

                if ($bestScore === null || $score < $bestScore)
                {
                    $bestScore = $score;
                    $bestCh    = $channel;
                    $bestId    = $id;
                }
            }
        }

        if ($bestCh === null)
        {
            return false;
        }

        $this->destroyEntry($bestCh, $bestId);

        return true;
    }

    /** Слот считается занятым, если его реально держит живой плеер — «протечь» нечему. */
    protected function allocPoolSlot(string $base, int $size): int
    {
        $busy = [];

        foreach ($this->players as $list)
        {
            foreach ($list as $e)
            {
                if ($e['poolBase'] === $base && $e['poolIndex'] >= 0)
                {
                    $busy[$e['poolIndex']] = true;
                }
            }
        }

        for ($i = 0; $i < $size; $i++)
        {
            if (empty($busy[$i]))
            {
                return $i;
            }
        }

        $i = isset($this->poolIndex[$base]) ? $this->poolIndex[$base] : 0;
        $this->poolIndex[$base] = ($i + 1) % $size;

        return $i;
    }

    protected function isFinished($player): bool
    {
        try
        {
            $status = $player->status;

            if (is_string($status))
            {
                $status = strtoupper($status);

                return $status === 'STOPPED' || $status === 'HALTED' || $status === 'DISPOSED';
            }
        }
        catch (Throwable $e) {}

        return false;
    }

    protected function safeStop($player)
    {
        if (!$player)
        {
            return;
        }

        try { $player->stop(); } catch (Throwable $e) {}

        foreach (['free', 'dispose', 'release'] as $m)
        {
            try
            {
                if (method_exists($player, $m))
                {
                    $player->$m();
                    break;
                }
            }
            catch (Throwable $e) {}
        }
    }

    protected function logError(string $msg)
    {
        try
        {
            if (class_exists('app\\forms\\classes\\Log'))
            {
                Log::info('[PseudoSound] ' . $msg);
            }
        }
        catch (Throwable $e) {}
    }

    // ------------------------------------------------------------------
    // СОВМЕСТИМОСТЬ СО СТАРЫМ API
    // ------------------------------------------------------------------

    public static function destroyChannel(string $channel)
    {
        self::stop($channel, 0.2);
    }

    public static function stopChannelInstant(string $channel)
    {
        self::stopInstant($channel);
    }

    public function getPooledChannel(string $base, int $size = 8): string
    {
        return $base . '_' . $this->allocPoolSlot($base, $size);
    }

    public function clearPool(string $base)
    {
        unset($this->poolIndex[$base]);
    }

    public function clearAllPools()
    {
        $this->poolIndex = [];
    }
}
