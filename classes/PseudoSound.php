<?php
namespace app\forms\classes;

use action\Media;
use php\lang\Thread;
use Throwable;
use app\forms\classes\Debug;
use script\MediaPlayerScript;
use app\forms\classes\Log;

class PseudoSound
{
    protected static $instance;

    const TYPE_SFX = 'sfx';
    const TYPE_MUSIC = 'music';

    const CH_ACTIVE = 'active';
    const CH_MUTED = 'muted';
    const CH_FADING = 'fading';

    protected $players = [];
    protected $channelVolumes = [];
    protected $channelStates = [];

    protected $masterVolume = 1.0;
    protected $volumeMusic = 0.2;
    protected $volumeSfx = 0.3;

    protected $maxPlayersPerChannel = 10;
    protected $maxTotalPlayers = 50;
    
    protected $channelCooldowns = [];    

    protected $muted = false;
    
    protected $channelMuted = [];
    protected $typeMuted = [];     

    public static function get()
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function log($msg)
    {
        Log::info("[PseudoSound] " . $msg);
    }

    public static function play(string $path, string $channel = 'default', bool $loop = false, ?string $type = null, $forceUnmute = false, int $cooldownMs = 0, float $volume = 1.0)
    {
        //if (empty($GLOBALS['AllSounds'])) return;
    
        $self = self::get();
    
        if ($cooldownMs > 0)
        {
            $now = microtime(true);
    
            if (isset($self->channelCooldowns[$channel]))
            {
                $diff = ($now - $self->channelCooldowns[$channel]) * 1000;
    
                if ($diff < $cooldownMs)
                {
                    return;
                }
            }
    
            $self->channelCooldowns[$channel] = $now;
        }
    
        if (isset($self->players[$channel]))
        {
            foreach ($self->players[$channel] as &$entry)
            {
                $entry['fade'] = [
                    'active' => true,
                    'from' => $entry['fadeMultiplier'],
                    'to' => 0.0,
                    'start' => microtime(true),
                    'duration' => 0.2
                ];
                $entry['destroyAfterFade'] = true;
            }
        }
    
        if (!$path)
        {
            Debug::fail("Sound not found:\n$path");
            return;
        }
    
        if ($type === null)
        {
            $type = $loop ? self::TYPE_MUSIC : self::TYPE_SFX;
        }
    
        $entry = $self->createPlayer($channel, $type, $loop);
        $entry['volume'] = max(0.0, $volume);
        $player = $entry['player'];
        
    
        try {
            $player->open($path);
            $player->loop = $loop;
            $player->play();
        } catch (Throwable $e) {
            Debug::fail("Failed to play:\n$path");
            return;
        }
    
        if ($forceUnmute)
        {
            $self->channelMuted[$channel] = false;
        }
    
        $isChannelMuted = $self->channelMuted[$channel] ?? false;
        $isTypeMuted = $self->typeMuted[$type] ?? false;
    
        $entry['fadeMultiplier'] = ($isChannelMuted || $isTypeMuted) ? 0.0 : 1.0;
    
        $self->players[$channel][] = $entry;
        $self->applyVolume($entry, $channel);
        
        if (empty($GLOBALS['AllSounds']))
        {
            $self->muteChannel($channel, false);
        }
    }
     
    public static function playAsync(string $path, bool $autoplay = true, $channel = null)
    {
        (new Thread(function() use ($path, $autoplay, $channel)
        {
            if (is_bool($channel))
            {
                $channel = $channel ? 'true' : 'false';
            }
    
            if ($channel != null)
            {
                Media::open($path, $autoplay, (string)$channel);
            }
            else
            {
                Media::open($path, $autoplay);
            }
        }))->start();
    }    
    
    protected function createPlayer(string $channel, string $type, bool $loop): array
    {
        if (!isset($this->players[$channel]))
        {
            $this->players[$channel] = [];
        }

        if ($this->countAllPlayers() >= $this->maxTotalPlayers)
        {
            $this->killOldest();
        }

        if (count($this->players[$channel]) >= $this->maxPlayersPerChannel)
        {
            $old = array_shift($this->players[$channel]);
            $this->safeStop($old['player'] ?? null);
        }

        return [
            'player' => new MediaPlayerScript(),
            'type' => $type,
            'loop' => $loop,
            'startTime' => microtime(true),
            'lifeTime' => $this->resolveLifeTime($type),

            'fadeMultiplier' => 1.0,
            'fade' => [
                'active' => false,
                'from' => 1.0,
                'to' => 1.0,
                'start' => 0,
                'duration' => 0
            ]
        ];
    }
    
    protected function fadeByType(string $type, float $to, float $duration)
    {
        $now = microtime(true);
    
        foreach ($this->players as $channel => &$players)
        {
    
            foreach ($players as &$entry)
            {
    
                if ($entry['type'] !== $type) continue;
    
                $entry['fade'] = [
                    'active' => true,
                    'from' => $entry['fadeMultiplier'],
                    'to' => $to,
                    'start' => $now,
                    'duration' => $duration
                ];
            }
        }
    }    
    
    protected function applyVolume(array &$entry, string $channel)
    {
        try {
            $base = $this->resolveVolume($entry['type'], $channel);
            $entryVolume = $entry['volume'] ?? 1.0;
    
            $entry['player']->volume = $base * $entryVolume * ($entry['fadeMultiplier'] ?? 1.0);
    
        } catch (Throwable $e) {}
    }

    protected function resolveVolume(string $type, string $channel): float
    {
        if ($this->muted) return 0.0;

        $base = ($type === self::TYPE_MUSIC) ? $this->volumeMusic : $this->volumeSfx;

        return $base * $this->masterVolume * ($this->channelVolumes[$channel] ?? 1.0);
    }

    public static function muteChannel(string $channel, bool $smooth = true, float $duration = 0.6)
    {
        $self = self::get();
        $self->channelMuted[$channel] = true;
    
        if (!isset($self->players[$channel])) return;
    
        $now = microtime(true);
    
        foreach ($self->players[$channel] as &$entry)
        {
    
            if (!$smooth)
            {
    
                $entry['fade'] = [
                    'active' => false,
                    'from' => 0.0,
                    'to' => 0.0,
                    'start' => 0,
                    'duration' => 0
                ];
    
                $entry['fadeMultiplier'] = 0.0;
    
                $self->applyVolume($entry, $channel);
                continue;
            }
    
            $entry['fade'] = [
                'active' => true,
                'from' => $entry['fadeMultiplier'],
                'to' => 0.0,
                'start' => $now,
                'duration' => $duration
            ];
        }
    
        unset($entry);
    }
    
    public static function unmuteChannel(string $channel, bool $smooth = true, float $duration = 0.4)
    {
        $self = self::get();
        $self->channelMuted[$channel] = false;
    
        if (!isset($self->players[$channel])) return;
    
        $now = microtime(true);
    
        foreach ($self->players[$channel] as &$entry)
        {
            if (!$smooth)
            {
                $entry['fade'] = [
                    'active' => false,
                    'from' => 1.0,
                    'to' => 1.0,
                    'start' => 0,
                    'duration' => 0
                ];
    
                $entry['fadeMultiplier'] = 1.0;
    
                $self->applyVolume($entry, $channel);
                continue;
            }
    
            $entry['fade'] = [
                'active' => true,
                'from' => $entry['fadeMultiplier'],
                'to' => 1.0,
                'start' => $now,
                'duration' => $duration
            ];
        }
    
        unset($entry);
    }

    public static function destroyChannel(string $channel)
    {
        $self = self::get();
    
        if (!isset($self->players[$channel])) return;
    
        $now = microtime(true);
    
        foreach ($self->players[$channel] as &$entry)
        {
            $entry['fade'] = [
                'active' => true,
                'from' => $entry['fadeMultiplier'],
                'to' => 0.0,
                'start' => $now,
                'duration' => 0.2
            ];
    
            $entry['destroyAfterFade'] = true;
        }
        
        unset($self->channelMuted[$channel]);
    }
    
    public static function muteSfx(float $duration = 0.4)
    {
        $self = self::get();
        
        $self->typeMuted[self::TYPE_SFX] = true;
        
        $self->fadeByType(self::TYPE_SFX, 0.0, $duration);
    }
    
    public static function unmuteSfx(float $duration = 0.4)
    {
        $self = self::get();
        
        $self->typeMuted[self::TYPE_SFX] = false;
        
        $self->fadeByType(self::TYPE_SFX, 1.0, $duration);
    }
    
    public static function muteMusic(float $duration = 0.6)
    {
        $self = self::get();
        
        $self->typeMuted[self::TYPE_MUSIC] = true;
        
        $self->fadeByType(self::TYPE_MUSIC, 0.0, $duration);
    }
    
    public static function unmuteMusic(float $duration = 0.6)
    {
        $self = self::get();
        
        $self->typeMuted[self::TYPE_MUSIC] = false;
        
        $self->fadeByType(self::TYPE_MUSIC, 1.0, $duration);
    }    

    public static function update()
    {
        $self = self::get();
        $now = microtime(true);

        foreach ($self->players as $channel => &$players)
        {
        
            foreach ($players as $i => &$entry)
            {

                if (empty($entry['player'])) continue;

                if ($entry['fade']['active'])
                {
                    $f = $entry['fade'];
                    $t = ($now - $f['start']) / $f['duration'];

                    if ($t >= 1.0)
                    {
                        $entry['fade']['active'] = false;
                        $entry['fadeMultiplier'] = $f['to'];
                    } 
                    else
                    {
                        $t = max(0.0, min(1.0, $t));
                        $t = 1 - pow(1 - $t, 3);
                        
                        $entry['fadeMultiplier'] = $f['from'] + ($f['to'] - $f['from']) * $t;
                    }

                    $self->applyVolume($entry, $channel);
                }
                
                if (!empty($entry['destroyAfterFade']) && !$entry['fade']['active'])
                {
                    $self->safeStop($entry['player']);
                    unset($players[$i]);
                    continue;
                }                

                if (!$entry['loop'] && ($now - $entry['startTime']) >= $entry['lifeTime'])
                {
                /*
                    $self->log(
                        "SFX cleanup | " .
                        "channel={$channel} | " .
                        "type={$entry['type']} | " .
                        "lifeTime={$entry['lifeTime']} | " .
                        "aliveFor=" . round($now - $entry['startTime'], 2)
                    );
                */
                
                    $self->safeStop($entry['player']);
                    unset($players[$i]);
                }
            }         

            if (empty($players))
            {
                unset($self->players[$channel]);
            }
            else
            {
                $players = array_values($players);
            }
        }              
    }

    protected function resolveLifeTime(string $type): float
    {
        return $type === self::TYPE_MUSIC ? 99999 : 10.0;
    }
    
    public static function stopChannelInstant(string $channel)
    {
        $self = self::get();
    
        if (!isset($self->players[$channel])) return;
    
        foreach ($self->players[$channel] as $i => $entry)
        {
            $self->safeStop($entry['player']);
            unset($self->players[$channel][$i]);
        }
    
        unset($self->players[$channel]);
    }    

    protected function safeStop($player)
    {
        if (!$player) return;
        try { $player->stop(); } catch (Throwable $e) {}
    }

    protected function countAllPlayers(): int
    {
        $count = 0;
        foreach ($this->players as $players)
        {
            $count += count($players);
        }
        return $count;
    }

    protected function killOldest()
    {
        $oldestTime = 999999999;
        $targetChannel = null;
        $targetIndex = null;

        foreach ($this->players as $channel => $players)
        {
            foreach ($players as $i => $entry)
            {
                if ($entry['startTime'] < $oldestTime)
                {
                    $oldestTime = $entry['startTime'];
                    $targetChannel = $channel;
                    $targetIndex = $i;
                }
            }
        }

        if ($targetChannel !== null)
        {
            $entry = $this->players[$targetChannel][$targetIndex];
            $this->safeStop($entry['player'] ?? null);
            unset($this->players[$targetChannel][$targetIndex]);
        }
    }    
}