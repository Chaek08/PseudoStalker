<?php
namespace app\forms\classes\Environment;

use script\MediaPlayerScript;
use app\forms\classes\Debug;
use Throwable;
use php\time\Timer;
use app\forms\classes\Log;
use app\forms\classes\Environment\EnvironmentData;
use app\forms\classes\Environment\EnvironmentBase;

class EnvironmentSound 
{
    protected $env;

    public const SOUND_BASE_PATH = './gamedata/sounds/';

    public $sfxTimerId;
    public $effectTimerId;
    public $ambientTimerId;

    protected $ambientPlayer;
    protected $sfxPlayer;
    protected $effectPlayer;
    protected $rainPlayer;
    protected $anomalyPlayer;

    public $isPaused  = false;
    public $isRainy   = false;
    public $isAnomalyHum = false;

    protected $volumeSfx     = 0.15;
    protected $volumeAmbient = 0.25;
    protected $volumeEffect  = 0.15;
    protected $volumeRain    = 0.20;
    protected $volumeAnomaly = 0.15;
    
    protected $anomalyLoopPath = 'environment/anomaly/gravi_gudenie';
    protected $rainLoopPath = 'environment/weather/rain';
    
    protected $currentAmbientPath = null;    
    
    public function __construct(EnvironmentBase $env)
    {
        $this->env = $env;
    }
    
    public function start()
    {
        $this->isPaused = false;    
    
        $this->scheduleNextSfx();
        $this->scheduleNextEffect();
        $this->scheduleNextAmbient();
        $this->startAmbient();
    }    
    
    public function startAmbient()
    {
        if (!$this->isActive()) return;

        $this->playRandomAmbient();
    }    
    
    public function stopAmbient()
    {
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;

        if ($this->ambientTimerId !== null)
        {
            $this->ambientTimerId->cancel();
            $this->ambientTimerId = null;
        }
        
        $this->ambientPlayer->stop();
    }

    public function pauseAmbient()
    {
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;
        
        $this->ambientPlayer->pause();
    }    
    
    public function resumeAmbient()
    {
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;
        
        if (!$this->isActive()) return;
        
        $this->ambientPlayer->resume();
    }
    
    public function restoreAmbient($path, $raw, $positionMs = 0)
    {
        if ($this->ambientPlayer)
        {
            $this->ambientPlayer->stop();
        }
    
        $this->currentAmbientPath = $path;
    
        $this->ambientPlayer = new MediaPlayerScript();
        $this->ambientPlayer->open($path);
        $this->ambientPlayer->volume = $this->volumeAmbient;
    
        $this->ambientPlayer->play();
        $this->ambientPlayer->positionMs = (int)$positionMs;
    
        $this->scheduleNextAmbient();
    }   
    
    protected function startAnomalyHum()
    {
        if (!$this->isActive())
        {
            return;
        }
    
        $this->stopAnomalyHum();
    
        $this->anomalyPlayer = new MediaPlayerScript();
        $this->anomalyPlayer->open(self::SOUND_BASE_PATH . $this->anomalyLoopPath . '.mp3');
    
        $this->anomalyPlayer->loop = true;
        $this->anomalyPlayer->volume = $this->volumeAnomaly;
        $this->anomalyPlayer->play();
    
        if (isset($GLOBALS['AllSounds']) && !$GLOBALS['AllSounds'])
        {
            $this->anomalyPlayer->pause();
        }
    }
    
    protected function stopAnomalyHum()
    {
        if ($this->anomalyPlayer)
        {
            $this->anomalyPlayer->stop();
            $this->anomalyPlayer = null;
        }
    }    
    
    protected function startRain()
    {
        if (!$this->isActive())
            return;
    
        $this->stopRain();
    
        $this->rainPlayer = new MediaPlayerScript();
        $this->rainPlayer->open(self::SOUND_BASE_PATH . $this->rainLoopPath . '.mp3');
        $this->rainPlayer->loop = true;
        $this->rainPlayer->volume = $this->volumeRain;
        $this->rainPlayer->play();
    }
    
    protected function stopRain()
    {
        if ($this->rainPlayer)
        {
            $this->rainPlayer->stop();
            $this->rainPlayer = null;
        }
    }
    
    public function playRandomAmbient()
    {
        list($path, $rawPath, $length) = $this->pickRandomAmbient();
        $this->playAmbientInternal($path, $rawPath, $length);
    }     
    
    public function playAmbientByIndex($index)
    {
        $index = (int)$index;
        if ($index < 0 || $index >= count(EnvironmentData::$ambientSounds))
        {
            Log::info("[Environment]: playAmbientByIndex invalid index {$index}");
            return false;
        }
    
        $item   = EnvironmentData::$ambientSounds[$index];
        $raw    = $item['path'];
        $path   = self::SOUND_BASE_PATH . $raw . '.mp3';
        $length = isset($item['length']) ? (int)$item['length'] : 0;
    
        $this->playAmbientInternal($path, $raw, $length, "[{$index}] forced");
    
        return true;
    }      
    
    public function playRandomSfx()
    {
        if (!$this->isActive()) return;

        $cycle = $this->isUnderground() ? 'underground' : ($this->env->getCurrentCycle() ?: 'day');

        $list = EnvironmentData::$rndSoundsByCycle[$cycle] ?? [];

        $sound = ($this->isRainy && mt_rand(1, 100) <= 50) ? $this->pickRandom(EnvironmentData::$thunderSounds) : $this->pickRandom($list);

        if ($sound === null) return;

        $file = $sound . '.mp3';
        $path = self::SOUND_BASE_PATH . $file;

        try {
            $this->sfxPlayer = new MediaPlayerScript();
            $this->sfxPlayer->open($path);
            $this->sfxPlayer->volume = $this->volumeSfx;
            $this->sfxPlayer->play();
        } catch (\Throwable $e) {
            Debug::fail("Environment: sfx open failed '{$path}'");
        }
    }

    public function playRandomEffect()
    {
        if (!$this->isActive()) return;

        $cycle = $this->env->getCurrentCycle() ?: 'day';

        if ($cycle === 'underground')
        {
            return;
        }

        $effectNames = EnvironmentData::$effectsByCycle[$cycle] ?? [];
        $effectName  = $this->pickRandom($effectNames);
        if ($effectName === null) return;

        if (empty(EnvironmentData::$effectsByName[$effectName])) return;
        $effect = EnvironmentData::$effectsByName[$effectName];

        $file      = $effect['sound'] . '.mp3';
        $soundPath = self::SOUND_BASE_PATH . $file;

        $this->effectPlayer = new MediaPlayerScript();
        $this->effectPlayer->open($soundPath);
        $this->effectPlayer->volume = $this->volumeEffect;
        $this->effectPlayer->play();
    }

    protected function playAmbientInternal($path, $rawPath, $length, $tag = '')
    {
        if (!$this->isActive()) return;
        if ($path === null) return;
    
        $this->currentAmbientPath = $path;
        
        if ($this->ambientPlayer)
        {
            $this->ambientPlayer->stop();
        }
        
        $this->ambientPlayer = new MediaPlayerScript();
        $this->ambientPlayer->open($path);
        $this->ambientPlayer->volume = $this->volumeAmbient;
        $this->ambientPlayer->play();

        $this->scheduleNextAmbient($length);
    }      
    
    public function scheduleNextSfx()
    {
        $cycle = $this->env->getCurrentCycle() ?: 'day';

        if (empty(EnvironmentData::$rndSoundsByCycle[$cycle])) return;

        $periods = EnvironmentData::$sfxPeriods;
        $period  = $periods[$cycle] ?? [8, 14];

        $this->scheduleTimer($this->sfxTimerId, $period, function ($delaySec) {
            $this->playRandomSfx();
            $this->scheduleNextSfx();
        });
    }

    public function scheduleNextEffect()
    {
        $cycle = $this->env->getCurrentCycle() ?: 'day';

        if ($cycle === 'underground')
        {
            return;
        }

        if (empty(EnvironmentData::$effectsByCycle[$cycle])) return;
        
        $periods = EnvironmentData::$effectPeriods;
        $period  = $periods[$cycle] ?? [40, 90];

        $this->scheduleTimer($this->effectTimerId, $period, function ($delaySec) {
            $this->playRandomEffect();
            $this->scheduleNextEffect();
        });
    }

    public function scheduleNextAmbient($lengthSec = null)
    {
        if ($this->ambientTimerId !== null)
        {
            $this->ambientTimerId->cancel();
            $this->ambientTimerId = null;
        }

        if (empty(EnvironmentData::$ambientSounds) || !$this->isActive()) return;

        if ($lengthSec === null)
        {
            list($_path, $_raw, $len) = $this->pickRandomAmbient();
            $lengthSec = $len > 0 ? $len : 120;
        }

        $jitter = 5;
        $min = max(1, $lengthSec - $jitter);
        $max = $lengthSec + $jitter;

        $delaySec = mt_rand($min, $max);
        $delayMs  = $delaySec * 1000;

        $self = $this;

        $this->ambientTimerId = Timer::after($delayMs, function () use ($self, $delaySec) {
            if (!$self->isActive()) return;

            $self->playRandomAmbient();
        });
    }

    public function pause()
    {
        if ($this->isPaused) return;

        $this->isPaused = true;
        
        if (is_object($this->ambientPlayer))
        {
            $this->ambientPlayer->pause();
        }
        
        if (is_object($this->sfxPlayer))
        {
            $this->sfxPlayer->pause();
        }
        
        if (is_object($this->effectPlayer))
        {
            $this->effectPlayer->pause();
        }
        
        if (is_object($this->rainPlayer))
        {
            $this->rainPlayer->pause();
        }
        
        if (is_object($this->anomalyPlayer))
        {
            $this->anomalyPlayer->pause();
        }
    }

    public function resume()
    {
        if (!$this->isPaused)
        {
            return;
        }
    
        $this->isPaused = false;
    
        if (!isset($GLOBALS['AllSounds']) || $GLOBALS['AllSounds'])
        {
            if (!isset($GLOBALS['AmbientSound']) || $GLOBALS['AmbientSound'])
            {
                if (is_object($this->ambientPlayer))
                {
                    $this->ambientPlayer->play();
                }
            }
    
            if (is_object($this->sfxPlayer))
            {
                $this->sfxPlayer->play();
            }
    
            if (is_object($this->effectPlayer))
            {
                $this->effectPlayer->play();
            }
    
            if ($this->isRainy && is_object($this->rainPlayer))
            {
                $this->rainPlayer->play();
            }
    
            if ($this->isAnomalyHum && is_object($this->anomalyPlayer))
            {
                $this->anomalyPlayer->play();
            }
        }
    
        if ($this->sfxTimerId)
        {
            $this->sfxTimerId->cancel();
            $this->sfxTimerId = null;
        }
    
        if ($this->effectTimerId)
        {
            $this->effectTimerId->cancel();
            $this->effectTimerId = null;
        }
    
        if ($this->ambientTimerId)
        {
            $this->ambientTimerId->cancel();
            $this->ambientTimerId = null;
        }
    
        $this->scheduleNextSfx();
        $this->scheduleNextEffect();
        $this->scheduleNextAmbient();
    }   
    
    public function stop()
    {
        if ($this->sfxTimerId)
        {
            $this->sfxTimerId->cancel();
            $this->sfxTimerId = null;
        }

        if ($this->effectTimerId)
        {
            $this->effectTimerId->cancel();
            $this->effectTimerId = null;
        }

        if ($this->ambientTimerId)
        {
            $this->ambientTimerId->cancel();
            $this->ambientTimerId = null;
        }
        
        foreach ([$this->ambientPlayer, $this->sfxPlayer, $this->effectPlayer, $this->rainPlayer, $this->anomalyPlayer] as $player)
        {
            if ($player)
            {
                $player->stop();
            }
        }
        
        $this->ambientPlayer = null;
        $this->sfxPlayer = null;
        $this->effectPlayer = null;
        $this->rainPlayer = null;
        $this->anomalyPlayer = null;
    }     
    
    protected function pickRandom(array $list)
    {
        if (empty($list)) return null;
        return $list[array_rand($list)];
    }    
    
    protected function pickRandomAmbient()
    {
        if (empty(EnvironmentData::$ambientSounds)) return [null, null, 0];
    
        $item = EnvironmentData::$ambientSounds[array_rand(EnvironmentData::$ambientSounds)];
    
        $rawPath = $item['path'];
        $path    = self::SOUND_BASE_PATH . $rawPath . '.mp3';
        $length  = isset($item['length']) ? (int)$item['length'] : 0;
    
        return [$path, $rawPath, $length];
    }
    
    public function setRainy($flag)
    {
        $this->isRainy = (bool)$flag;
        $this->isRainy ? $this->startRain() : $this->stopRain();
    }

    public function setAnomalyHum($flag)
    {
        $this->isAnomalyHum = (bool)$flag;
        $this->isAnomalyHum ? $this->startAnomalyHum() : $this->stopAnomalyHum();
    }    
    
    protected function scheduleTimer(&$timerField, array $period, callable $callback)
    {
        if ($timerField !== null)
        {
            $timerField->cancel();
            $timerField = null;
        }

        $min = $period[0];
        $max = $period[1];

        $delaySec = mt_rand($min, $max);
        $delayMs  = $delaySec * 1000;

        $self = $this;

        $timerField = Timer::after($delayMs, function () use ($self, $callback, $delaySec) {
            if ($self->isPaused) return;
            $callback($delaySec);
        });
    }
     
    public function isActive()
    {
        return !$this->env->isPaused();
    }
    
    protected function isUnderground()
    {
        return $this->env->getCurrentCycle() === 'underground';
    }
    
    public function isPaused()
    {
        return $this->isPaused;
    }
    
    public function isRainy()
    {
        return $this->isRainy;
    }
    
    public function isAnomalyHum()
    {
        return $this->isAnomalyHum;
    }
    
    public function getAmbientPath()
    {
        return $this->currentAmbientPath;
    }           
    
    public function getAmbientPlayer()
    {
        return $this->ambientPlayer;
    }      
}