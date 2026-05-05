<?php
namespace app\forms\classes;

use app\forms\classes\PseudoSound;
use app\forms\classes\EnvironmentBrightness;
use php\gui\animation\UXAnimationTimer;
use app\forms\classes\Log;
use Throwable;
use app\forms\classes\Debug;
use php\framework\Logger;
use php\time\Time;
use php\time\Timer;
use script\MediaPlayerScript;
use php\lang\Logger;

class Environment
{
    protected $soundBasePath    = './gamedata/sounds/';
    protected $texturesBasePath = './gamedata/textures/';

    const CH_AMBIENT = 'env_ambient';
    const CH_SFX     = 'env_sfx';
    const CH_EFFECT  = 'env_effect';
    const CH_RAIN    = 'env_rain';
    const CH_ANOMALY = 'env_anomaly';

    protected $mediaView;

    protected $currentCycle = '';

    protected $currentLocationIndex = -1;
    protected $lastLocationIndex    = -1;

    protected $manualCycle = null;

    protected $currentBackgroundPath = null;
    protected $currentAmbientPath    = null;

    public $timerId;
    public $sfxTimerId;
    public $effectTimerId;
    public $ambientTimerId;

    public $isPaused     = false;
    public $isRainy      = false;
    public $isAnomalyHum = false;

    protected $onCycleChange = null;

    protected $brightnessByCycle = [
        'morning'     => -0.1,
        'day'         =>  0.0,
        'evening'     => -0.2,
        'night'       => -0.4,
        'underground' => -0.4,
    ];
    
    public $brightnessTimerId = null;
    
    public $brightnessCurrent = 0.0;
    public $brightnessTarget  = 0.0;
    
    public $brightnessLerp   = 0.25;
    
    protected $brightnessManager;    

  //  protected $volumeSfx     = 0.04;
  //  protected $volumeAmbient = 0.25;
 //   protected $volumeEffect  = 0.15;
  //  protected $volumeRain    = 0.02;
  //  protected $volumeAnomaly = 0.05;
    protected $volumeSfx     = 1;
    protected $volumeAmbient = 1;
    protected $volumeEffect  = 1;
    protected $volumeRain    = 1;
    protected $volumeAnomaly = 1;  

    protected $sfxPeriods = [
        'evening'     => [6, 9],
        'night'       => [6, 9],
        'morning'     => [8, 14],
        'day'         => [8, 14],
        'underground' => [5, 10],
    ];

    protected $effectPeriods = [
        'morning' => [30, 60],
        'day'     => [40, 90],
        'evening' => [90, 120],
        'night'   => [130, 160],
    ];

    protected $locations = [
        'L0' => [
            'morning' => [ 'path' => 'environment/L0/L0_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L0/L0_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L0/L0_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L0/L0_Night',   'rain' => true, 'anomaly' => true ],
        ],
        'L1' => [
            'morning' => [ 'path' => 'environment/L1/L1_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L1/L1_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L1/L1_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L1/L1_Night',   'anomaly' => true ],
        ],
        'L2' => [
            'morning' => [ 'path' => 'environment/L2/L2_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L2/L2_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L2/L2_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L2/L2_Night',   'anomaly' => true ],
        ],
        'L3' => [
            'morning' => [ 'path' => 'environment/L3/L3_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L3/L3_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L3/L3_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L3/L3_Night',   'anomaly' => true ],
        ],
        'L4' => [
            'morning' => [ 'path' => 'environment/L4/L4_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L4/L4_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L4/L4_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L4/L4_Night',   'rain' => true, 'anomaly' => true ],
        ],
        'L5' => [
            'underground' => [ 'path' => 'environment/L5/L5U_Underground'],
        ],
    ];

    protected $anomalyLoopPath = 'environment/anomaly/gravi_gudenie';
    protected $rainLoopPath    = 'environment/weather/rain';

    protected $thunderSounds = [
        'environment/weather/thunder-0',
        'environment/weather/thunder-1',
        'environment/weather/thunder-2',
        'environment/weather/thunder-3',
    ];

    protected $ambientSounds = [
        [ 'path' => 'environment/ambient/amb00', 'length' => 322 ],
        [ 'path' => 'environment/ambient/amb01', 'length' => 247 ],
        [ 'path' => 'environment/ambient/amb02', 'length' => 248 ],
        [ 'path' => 'environment/ambient/amb03', 'length' => 299 ],
        [ 'path' => 'environment/ambient/amb04', 'length' => 796 ],
        [ 'path' => 'environment/ambient/amb05', 'length' => 363 ],
        [ 'path' => 'environment/ambient/amb06', 'length' => 191 ],
    ];

    protected function isUnderground()
    {
        return $this->currentCycle === 'underground';
    }

    protected $rndSoundsByCycle = [
        'evening' => [
            'environment/rnd_outdoor/rnd_boar', 'environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/rnd_horror','environment/rnd_outdoor/rnd_pdog',
            'environment/rnd_outdoor/rnd_pdog1','environment/rnd_outdoor/rnd_cat2',
            'environment/rnd_outdoor/rnd_cat1','environment/rnd_outdoor/rnd_boar3',
            'environment/rnd_outdoor/crickets_1','environment/rnd_outdoor/crickets_2',
            'environment/rnd_outdoor/crickets_3','environment/rnd_outdoor/rnd_dog',
            'environment/rnd_outdoor/rnd_dog1','environment/rnd_outdoor/rnd_dog2',
            'environment/rnd_outdoor/rnd_dog3','environment/rnd_outdoor/rnd_fly2',
            'environment/rnd_outdoor/rnd_fly3','environment/rnd_outdoor/rnd_krik1',
            'environment/rnd_outdoor/rnd_krik2','environment/rnd_outdoor/rnd_krik3',
            'environment/rnd_outdoor/rnd_moan','environment/rnd_outdoor/rnd_moan1',
            'environment/rnd_outdoor/owl_1','environment/rnd_outdoor/owl_2',
            'environment/rnd_outdoor/owl_3','environment/rnd_outdoor/rnd_dark',
            'environment/rnd_outdoor/rnd_dark0','environment/rnd_outdoor/rnd_dark1',
            'environment/rnd_outdoor/rnd_shooting_5','environment/rnd_outdoor/rnd_shooting_7',
            'environment/rnd_outdoor/rnd_swamp','environment/rnd_outdoor/rnd_dark4',
            'environment/rnd_outdoor/rnd_howling_1','environment/rnd_outdoor/rnd_howling_2',
        ],
        'night' => [
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/rnd_shooting_6','environment/rnd_outdoor/rnd_horror',
            'environment/rnd_outdoor/rnd_pdog1','environment/rnd_outdoor/rnd_pdog2',
            'environment/rnd_outdoor/rnd_obval','environment/rnd_outdoor/rnd_cat1',
            'environment/rnd_outdoor/rnd_dark5','environment/rnd_outdoor/rnd_dark8',
            'environment/rnd_outdoor/crickets_2','environment/rnd_outdoor/rnd_dark9',
            'environment/rnd_outdoor/rnd_dark3','environment/rnd_outdoor/rnd_dark10',
            'environment/rnd_outdoor/rnd_horror1','environment/rnd_outdoor/owl_1',
            'environment/rnd_outdoor/owl_2','environment/rnd_outdoor/rnd_krik9',
            'environment/rnd_outdoor/rnd_krik8','environment/rnd_outdoor/rnd_krik7',
            'environment/rnd_outdoor/rnd_moan5','environment/rnd_outdoor/rnd_moan6',
            'environment/rnd_outdoor/rnd_rock2','environment/rnd_outdoor/rnd_rock3',
            'environment/rnd_outdoor/rnd_rock4','environment/rnd_outdoor/rnd_dark',
            'environment/rnd_outdoor/rnd_dark0','environment/rnd_outdoor/rnd_dark1',
            'environment/rnd_outdoor/rnd_shooting_9','environment/rnd_outdoor/rnd_shOOTing_10',
            'environment/rnd_outdoor/rnd_dark4','environment/rnd_outdoor/rnd_howling_1',
            'environment/rnd_outdoor/rnd_howling_2',
        ],
        'morning' => [
            'environment/rnd_outdoor/rnd_boar1','environment/rnd_outdoor/rnd_bird1',
            'environment/rnd_outdoor/rnd_bird2','environment/rnd_outdoor/rnd_bird4',
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_boar2',
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_darkwind5',
            'environment/rnd_outdoor/rnd_dog','environment/rnd_outdoor/rnd_dog1',
            'environment/rnd_outdoor/rnd_dog2','environment/rnd_outdoor/rnd_dog3',
            'environment/rnd_outdoor/rnd_fly','environment/rnd_outdoor/rnd_fly1',
            'environment/rnd_outdoor/rnd_fly2','environment/rnd_outdoor/rnd_fly3',
            'environment/rnd_outdoor/rnd_krik6','environment/rnd_outdoor/rnd_krik8',
            'environment/rnd_outdoor/rnd_krik9','environment/rnd_outdoor/rnd_moan',
            'environment/rnd_outdoor/rnd_moan3','environment/rnd_outdoor/rnd_shooting_4',
            'environment/rnd_outdoor/rnd_krik3','environment/rnd_outdoor/rnd_shooting_9',
            'environment/rnd_outdoor/rnd_shooting_3','environment/rnd_outdoor/rnd_swamp',
            'environment/rnd_outdoor/rnd_wind_tree',
        ],
        'day' => [
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_dark10',
            'environment/rnd_outdoor/rnd_dark6','environment/rnd_outdoor/rnd_dark2',
            'environment/rnd_outdoor/rnd_dark5','environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/crow1','environment/rnd_outdoor/crow2',
            'environment/rnd_outdoor/crow3','environment/rnd_outdoor/rnd_bird2',
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_boar2',
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_darkwind3',
            'environment/rnd_outdoor/rnd_darkwind4','environment/rnd_outdoor/rnd_darkwind5',
            'environment/rnd_outdoor/rnd_dog','environment/rnd_outdoor/rnd_dog1',
            'environment/rnd_outdoor/rnd_dog2','environment/rnd_outdoor/rnd_dog3',
            'environment/rnd_outdoor/rnd_fly','environment/rnd_outdoor/rnd_fly1',
            'environment/rnd_outdoor/rnd_fly2','environment/rnd_outdoor/rnd_fly3',
            'environment/rnd_outdoor/rnd_krik3','environment/rnd_outdoor/rnd_krik2',
            'environment/rnd_outdoor/rnd_krik1','environment/rnd_outdoor/rnd_krik4',
            'environment/rnd_outdoor/rnd_krik5','environment/rnd_outdoor/rnd_krik6',
            'environment/rnd_outdoor/rnd_moan2','environment/rnd_outdoor/rnd_moan3',
            'environment/rnd_outdoor/rnd_shooting_1','environment/rnd_outdoor/rnd_shooting_2',
            'environment/rnd_outdoor/rnd_shooting_3','environment/rnd_outdoor/rnd_shooting_4',
            'environment/rnd_outdoor/rnd_shooting_5','environment/rnd_outdoor/rnd_shooting_7',
            'environment/rnd_outdoor/rnd_shooting_8','environment/rnd_outdoor/rnd_shooting_9',
            'environment/rnd_outdoor/rnd_shooting_10','environment/rnd_outdoor/rnd_swamp',
            'environment/rnd_outdoor/rnd_wind_tree',
        ],
        'underground' => [
            'environment/underground/breath_1','environment/underground/breath_2',
            'environment/underground/hit_2','environment/underground/hit_1',
            'environment/underground/strange_noise_1','environment/underground/strange_noise_2',
            'environment/underground/strange_noise_3','environment/underground/rnd_drop_1',
            'environment/underground/rnd_drop_2','environment/underground/rnd_drop_3',
            'environment/underground/rnd_drop_4','environment/underground/rnd_drop_5',
            'environment/underground/rnd_drop_6','environment/underground/rnd_metal1',
            'environment/underground/rnd_metal2','environment/underground/rnd_metal3',
            'environment/underground/rnd_rat_panic_1','environment/underground/rnd_rat_panic_2',
            'environment/underground/rnd_rat_panic_3',
        ],
    ];

    protected $effectsByName = [
        'ae0_effect_0' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_1' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_2' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_3' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_4' => ['life_time' => 15, 'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_5' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_6' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_1'],
        'ae0_effect_7' => ['life_time' => 8,  'sound' => 'environment/rnd_outdoor/rnd_wind_1'],
        'ae0_effect_8' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_9' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
    ];

    protected $effectsByCycle = [
        'morning' => ['ae0_effect_4'],
        'day'     => ['ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_5','ae0_effect_6','ae0_effect_7','ae0_effect_8','ae0_effect_9'],
        'evening' => ['ae0_effect_0','ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_8'],
        'night'   => ['ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_8'],
    ];

    public function __construct($mediaView, EnvironmentBrightness $brightnessManager)
    {
        $this->mediaView = $mediaView;
        $this->brightnessManager = $brightnessManager;
        
        if ($mediaView == null)
        {
            Debug::fatal('Environment: mediaView is null', __FILE__, __LINE__);
            return;
        }        

        try {
            $this->videoPlayer = new MediaPlayerScript();
            $this->videoPlayer->view = $this->mediaView;
            $this->videoPlayer->loop = true;
        } catch (\Throwable $e) {
            Debug::fatal('Environment: video player init failed', __FILE__, __LINE__);
        }

        $this->update();
        
        $this->forceBrightnessNow();
        $this->startBrightnessTimer();        

        $self = $this;
        $this->timerId = Timer::every(60 * 1000, function () use ($self) {
            $self->update();
        });

        $this->scheduleNextSfx();
        $this->scheduleNextEffect();
    }
    
    public function updateBrightnessTarget()
    {
        $cycle = $this->currentCycle ?: 'day';
        $this->brightnessTarget = (float)($this->brightnessByCycle[$cycle] ?? 0.0);
    }
    
    public function applyBrightness($value)
    {
        $value = (float)$value;
    
        if ($this->brightnessManager)
        {
            $this->brightnessManager->set($value);
        }
    }
    
    public function startBrightnessTimer()
    {
        if ($this->brightnessTimerId)
        {
            return;
        }
    
        $this->brightnessTimerId = new UXAnimationTimer(function () {
            if (!$this->isActive()) return;
    
            $this->updateBrightnessTarget();
    
            $cur = (float) $this->brightnessCurrent;
            $tar = (float) $this->brightnessTarget;
    
            $next = $cur + ($tar - $cur) * $this->brightnessLerp;
    
            if (abs($tar - $next) < 0.005)
            {
                $next = $tar;
            }
    
            $next = max(-1.0, min(1.0, $next));
            $this->brightnessCurrent = $next;
    
            $this->applyBrightness($next);
        });
    
        $this->brightnessTimerId->start();
    }
    
    public function forceBrightnessNow()
    {
        $this->updateBrightnessTarget();
        $this->brightnessCurrent = $this->brightnessTarget;
        $this->applyBrightness($this->brightnessCurrent);
    }    

    protected function pickRandom(array $list)
    {
        if (empty($list)) return null;
        return $list[array_rand($list)];
    }

    protected function pickRandomAmbient()
    {
        if (empty($this->ambientSounds)) return [null, null, 0];
    
        $item = $this->ambientSounds[array_rand($this->ambientSounds)];
    
        $rawPath = $item['path'];
        $path    = $this->soundBasePath . $rawPath . '.mp3';
        $length  = isset($item['length']) ? (int)$item['length'] : 0;
    
        return [$path, $rawPath, $length];
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

    protected function pickLocationForCycle($cycle)
    {
        $finalCycle = $cycle;
    
        if ($this->currentLocationIndex >= 0)
        {
            $index = $this->currentLocationIndex;
        }
        else
        {
            $maxIndex = 5; // L0..L5
            if ($this->lastLocationIndex < 0)
            {
                $index = mt_rand(0, $maxIndex);
            }
            else
            {
                if ($maxIndex == 0)
                {
                    $index = 0;
                }
                else
                {
                    do { $index = mt_rand(0, $maxIndex);
                    } while ($index === $this->lastLocationIndex);
                }
            }
    
            $this->currentLocationIndex = $index;
            $this->lastLocationIndex    = $index;
        }
    
        $locId = 'L' . $index;
    
        if (empty($this->locations[$locId][$cycle]))
        {
            if ($locId === 'L5' && isset($this->locations[$locId]['underground']))
            {
                $finalCycle = 'underground';
            }
            else
            {
                Debug::fail("Environment: missing location '{$locId}' for cycle '{$cycle}'", __FILE__, __LINE__);
                return [null, false, false, null, $index, $finalCycle];
            }
        }
    
        $useCycle = $finalCycle;
    
        if (empty($this->locations[$locId][$useCycle]))
        {
            Debug::fail("Environment: missing location '{$locId}' for real cycle '{$useCycle}'", __FILE__, __LINE__);
            return [null, false, false, null, $index, $finalCycle];
        }
    
        $item    = $this->locations[$locId][$useCycle];
        $rawPath = $item['path'];
        $path    = $this->texturesBasePath . $rawPath . '.mp4';
        $rain    = !empty($item['rain']);
        $anomaly = !empty($item['anomaly']);
    
        return [$path, $rain, $anomaly, $rawPath, $index, $finalCycle];
    }


    public function setLocationIndex($index)
    {
        $index = (int)$index;
        if ($index < 0) $index = 0;
        if ($index > 5) $index = 5;
    
        $this->currentLocationIndex = $index;
        $this->lastLocationIndex    = $index;
        Log::info("[Environment]: manual location set to L{$index}");
    
        if ($index === 5)
        {
            $cycle = 'underground';
        }
        else
        {
            if ($this->manualCycle !== null)
            {
                $cycle = ($this->manualCycle === 'underground') ? $this->getTimeCycleByString(Time::now()->toString('HH:mm')) : $this->manualCycle;
            }
            else
            {
                $cycle = $this->getTimeCycleByString(Time::now()->toString('HH:mm'));
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
    
        $this->updateWithCycle($cycle);
    }

    public function playAmbientByIndex($index)
    {
        $index = (int)$index;
        if ($index < 0 || $index >= count($this->ambientSounds))
        {
            Log::info("[Environment]: playAmbientByIndex invalid index {$index}");
            return false;
        }
    
        $item   = $this->ambientSounds[$index];
        $raw    = $item['path'];
        $path   = $this->soundBasePath . $raw . '.mp3';
        $length = isset($item['length']) ? (int)$item['length'] : 0;
    
        $this->playAmbientInternal($path, $raw, $length, "[{$index}] forced");
    
        return true;
    }

    public function isActive()
    {
        return !$this->isPaused;
    }

    public function update($timeStr = null)
    {
        if (!$this->isActive()) return;

        if ($this->manualCycle !== null)
        {
            $this->updateWithCycle($this->manualCycle);
            return;
        }

        $cycle = $this->getTimeCycleByString($timeStr ?: Time::now()->toString('HH:mm'));

        if ($this->currentLocationIndex === 5)
        {
            $cycle = 'underground';
        }

        $this->updateWithCycle($cycle);
    }

    public function scheduleNextSfx()
    {
        $cycle = $this->currentCycle ?: 'day';

        if (empty($this->rndSoundsByCycle[$cycle])) return;

        $periods = $this->sfxPeriods;
        $period  = $periods[$cycle] ?? [8, 14];

        $this->scheduleTimer($this->sfxTimerId, $period, function ($delaySec) {
            $this->playRandomSfx();
            $this->scheduleNextSfx();
        });
    }

    public function scheduleNextEffect()
    {
        $cycle = $this->currentCycle ?: 'day';

        if ($cycle === 'underground')
        {
            return;
        }

        if (empty($this->effectsByCycle[$cycle])) return;
        
        $periods = $this->effectPeriods;
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

        if (empty($this->ambientSounds) || !$this->isActive()) return;

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

    public function playRandomSfx()
    {
        if (!$this->isActive()) return;

        $cycle = $this->isUnderground() ? 'underground' : ($this->currentCycle ?: 'day');

        $list = $this->rndSoundsByCycle[$cycle] ?? [];

        $sound = ($this->isRainy && mt_rand(1, 100) <= 50) ? $this->pickRandom($this->thunderSounds) : $this->pickRandom($list);

        if ($sound === null) return;

        $file = $sound . '.mp3';
        $path = $this->soundBasePath . $file;

        try {
            PseudoSound::play($path, self::CH_SFX, false, null, true, 0, $this->volumeSfx);
        } catch (\Throwable $e) {
            Debug::fail("Environment: sfx open failed '{$path}'", __FILE__, __LINE__);
        }
    }

    public function playRandomEffect()
    {
        if (!$this->isActive()) return;

        $cycle = $this->currentCycle ?: 'day';

        if ($cycle === 'underground')
        {
            return;
        }

        $effectNames = $this->effectsByCycle[$cycle] ?? [];
        $effectName  = $this->pickRandom($effectNames);
        if ($effectName === null) return;

        if (empty($this->effectsByName[$effectName])) return;
        $effect = $this->effectsByName[$effectName];

        $file      = $effect['sound'] . '.mp3';
        $soundPath = $this->soundBasePath . $file;

        PseudoSound::play($soundPath, self::CH_EFFECT, false, null, true, 0, $this->volumeEffect);
    }

    protected function playAmbientInternal($path, $rawPath, $length, $tag = '')
    {
        if (!$this->isActive()) return;
        if ($path === null) return;
    
        $this->currentAmbientPath = $path;
        
        PseudoSound::stopChannelInstant(self::CH_AMBIENT);
        PseudoSound::play($path, self::CH_AMBIENT, false, PseudoSound::TYPE_MUSIC, false, 0, $this->volumeAmbient);  
        PseudoSound::muteChannel(self::CH_AMBIENT, false);
        
        if (isset($GLOBALS['AmbientSound']) && $GLOBALS['AmbientSound'])
        {
             PseudoSound::unmuteChannel(self::CH_AMBIENT);
        }    
    
        if ($tag === '')
        {
            Log::info("[Environment]: ambient '{$rawPath}' played (length={$length}s)");
        }
        else
        {
            Log::info("[Environment]: ambient {$tag} '{$rawPath}' played (length={$length}s)");
        }
    
        $this->scheduleNextAmbient($length);
    }

    public function playRandomAmbient()
    {
        list($path, $rawPath, $length) = $this->pickRandomAmbient();
        $this->playAmbientInternal($path, $rawPath, $length);
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
        
        PseudoSound::destroyChannel(self::CH_AMBIENT);
    }

    public function pauseAmbient()
    {
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;
        
        PseudoSound::muteChannel(self::CH_AMBIENT);
    }

    public function resumeAmbient()
    {
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;
        
        if (!$this->isActive()) return;
        
        PseudoSound::unmuteChannel(self::CH_AMBIENT);
    }

    public function setRainy($flag)
    {
        $this->isRainy = (bool)$flag;
        $this->isRainy ? $this->startRain() : $this->stopRain();
    }

    protected function startRain()
    {
        if (!$this->isActive()) return;
        
        try {
        
            PseudoSound::stopChannelInstant(self::CH_RAIN);
            PseudoSound::play($this->soundBasePath . $this->rainLoopPath . '.mp3', self::CH_RAIN, true, PseudoSound::TYPE_MUSIC, true, 0, $this->volumeRain);
            PseudoSound::muteChannel(self::CH_RAIN, false);
            
            if (isset($GLOBALS['AllSounds']) && $GLOBALS['AllSounds'])
            {
                 PseudoSound::unmuteChannel(self::CH_RAIN);
            }          
        } catch (\Throwable $e) {
            Debug::fail("Environment: rain sound failed", __FILE__, __LINE__);
        }
        
        Log::info("[Environment]: rain started '{$this->rainLoopPath}'");
    }

    protected function stopRain()
    {
        PseudoSound::destroyChannel(self::CH_RAIN);
    }

    public function setAnomalyHum($flag)
    {
        $this->isAnomalyHum = (bool)$flag;
        
            Log::info("ANOMALY HUM: " . ($this->isAnomalyHum ? 'ON' : 'OFF'));
        $this->isAnomalyHum ? $this->startAnomalyHum() : $this->stopAnomalyHum();
    }

    protected function startAnomalyHum()
    {
        if (!$this->isActive()) return;

        PseudoSound::stopChannelInstant(self::CH_ANOMALY);
        PseudoSound::play($this->soundBasePath . $this->anomalyLoopPath . '.mp3', self::CH_ANOMALY, true, PseudoSound::TYPE_MUSIC, false, 0, $this->volumeAnomaly);
        PseudoSound::muteChannel(self::CH_ANOMALY, false);
        
        if (isset($GLOBALS['AllSounds']) && $GLOBALS['AllSounds'])
        {
            PseudoSound::unmuteChannel(self::CH_ANOMALY);
        }
    }

    protected function stopAnomalyHum()
    {
        PseudoSound::destroyChannel(self::CH_ANOMALY);
    }

    public function pause()
    {
        if ($this->isPaused) return;

        $this->isPaused = true;
        
        $this->videoPlayer->pause();

        PseudoSound::muteChannel(self::CH_AMBIENT);
        PseudoSound::muteChannel(self::CH_SFX);
        PseudoSound::muteChannel(self::CH_EFFECT);
        PseudoSound::muteChannel(self::CH_RAIN);
        PseudoSound::muteChannel(self::CH_ANOMALY);
    }

    public function resume()
    {
        if (!$this->isPaused) return;

        $this->isPaused = false;

        $this->videoPlayer->play();

        if (!isset($GLOBALS['AllSounds']) || $GLOBALS['AllSounds'])
        {
            if (!isset($GLOBALS['AmbientSound']) || $GLOBALS['AmbientSound'])
            {
                PseudoSound::unmuteChannel(self::CH_AMBIENT);
            }
    
            PseudoSound::unmuteChannel(self::CH_SFX);
            PseudoSound::unmuteChannel(self::CH_EFFECT);
    
            if ($this->isRainy)
            {
                PseudoSound::unmuteChannel(self::CH_RAIN);
            }
            if ($this->isAnomalyHum)
            {
                PseudoSound::unmuteChannel(self::CH_ANOMALY);
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

    public function setCycle($cycle)
    {
        if ($cycle === null)
        {
            $this->manualCycle = null;
            Log::info("[Environment]: manual cycle cleared, using time");
            $this->update();
            return;
        }

        $allowed = ['morning', 'day', 'evening', 'night', 'underground'];
        if (!in_array($cycle, $allowed, true))
        {
            Log::info("[Environment]: invalid manual cycle '{$cycle}'");
            return;
        }

        $this->manualCycle = $cycle;
        Log::info("[Environment]: manual cycle set to '{$cycle}'");

        $this->updateWithCycle($cycle);
    }

    protected function updateWithCycle($cycle)
    {
        if (!$this->isActive()) return;
    
        $old = $this->currentCycle;
        $firstInit = ($old === '' || $old === null);
        
        list($backgroundPath, $rain, $anomaly, $rawPath, $locIndex, $realCycle) = $this->pickLocationForCycle($cycle);
        if ($backgroundPath === null)
        {
            Debug::fail("Environment: no background for cycle '{$cycle}'", __FILE__, __LINE__);
            return;
        }        
        $cycleChanged = (!$firstInit && $realCycle !== $old);
        $bgChanged    = ($backgroundPath !== $this->currentBackgroundPath);
    
        if ($cycleChanged || $bgChanged)
        {
            if ($old !== $realCycle)
            {
                if (!$firstInit)
                {
                    Log::info("[Environment]: cycle changed {$old} -> {$realCycle}");
                }
                $this->currentCycle = $realCycle;
            }
    
            $this->currentBackgroundPath = $backgroundPath;
    
            try {
                $this->videoPlayer->stop();
                $this->videoPlayer->open($backgroundPath);
                $this->videoPlayer->play();
            } catch (\Throwable $e) {
                Debug::fail("Environment: video open failed '{$backgroundPath}'", __FILE__, __LINE__);
                return;
            }

            Log::info("[Environment]: video '{$rawPath}' started for cycle '{$realCycle}' (L{$locIndex}, rain=" . ($rain ? '1' : '0') . ", anomaly=" . ($anomaly ? '1' : '0') . ")");
    
            $this->setRainy($rain);
            $this->setAnomalyHum($anomaly);
    
            $this->scheduleNextSfx();
            $this->scheduleNextEffect();
    
            if ($cycleChanged && $this->onCycleChange !== null)
            {
                call_user_func($this->onCycleChange, $old, $realCycle);
            }
        }
        else
        {
            $this->videoPlayer->play();
        }
    }

    protected function getTimeCycleByString($timeStr)
    {
        $hour = (int) substr($timeStr, 0, 2);

        if ($hour >= 5 && $hour < 11)  return 'morning';
        if ($hour >= 11 && $hour < 18) return 'day';
        if ($hour >= 18 && $hour < 21) return 'evening';
        return 'night';
    }

    public function stop()
    {
        if ($this->timerId)
        {
            $this->timerId->cancel();
            $this->timerId = null;
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
        
        if ($this->brightnessTimerId)
        {
            $this->brightnessTimerId->stop();
            $this->brightnessTimerId = null;
        }        

        PseudoSound::destroyChannel(self::CH_AMBIENT);
        PseudoSound::destroyChannel(self::CH_SFX);
        PseudoSound::destroyChannel(self::CH_EFFECT);
        PseudoSound::destroyChannel(self::CH_RAIN);
        PseudoSound::destroyChannel(self::CH_ANOMALY);
    }
    
    public function reset()
    {
        $this->stop();
    
        $this->isPaused     = false;
        $this->isRainy      = false;
        $this->isAnomalyHum = false;
    
        $this->currentCycle = '';
        $this->manualCycle  = null;
    
        $this->currentBackgroundPath = null;
        $this->currentAmbientPath    = null;
    
        $this->currentLocationIndex = -1;
        $this->lastLocationIndex    = -1;
    
        $this->update();
        
        $this->forceBrightnessNow();
        $this->startBrightnessTimer();        
    
        $self = $this;
        $this->timerId = Timer::every(60 * 1000, function () use ($self) {
            $self->update();
        });
    
        $this->scheduleNextSfx();
        $this->scheduleNextEffect();
    
        $this->scheduleNextAmbient();
        $this->startAmbient();        
    
        Log::info("[Environment]: is reset");
    }
    
    public function playBackgroundPath($path)
    {
        if (!$this->isActive()) return;
        if (!$path) return;

        $this->currentBackgroundPath = $path;

        $this->videoPlayer->stop();
        $this->videoPlayer->open($path);
        $this->videoPlayer->play();

        Log::info("[Environment]: background restored '{$path}'");
    }

    public function playAmbientPath($path) //DEPRECATED
    {
        if (!$this->isActive()) return;//DEPRECATED
        if (isset($GLOBALS['AmbientSound']) && !$GLOBALS['AmbientSound']) return;//DEPRECATED
        if (!$path) return;//DEPRECATED

        $this->currentAmbientPath = $path;//DEPRECATED

        PseudoSound::play($path, self::CH_AMBIENT, false, PseudoSound::TYPE_MUSIC);//DEPRECATED//DEPRECATED//DEPRECATED//DEPRECATED//DEPRECATED//DEPRECATED//DEPRECATED
    }

    public function getState()
    {
        return [
            'location_index'   => $this->currentLocationIndex,
            'cycle'            => $this->currentCycle,
            'manual_cycle'     => $this->manualCycle,
            'is_rainy'         => $this->isRainy,
            'is_anomaly_hum'   => $this->isAnomalyHum,
            'background_path'  => $this->currentBackgroundPath,
            'ambient_path'     => $this->currentAmbientPath,
            'ambient_position' => 0, //$this->ambientPlayer ? $this->ambientPlayer->positionMs : 0,
        ];
    }

    public function restoreState(array $state, $timeHm)
    {
        if (!is_array($state))
        {
            Debug::fail('Environment: restoreState got invalid state', __FILE__, __LINE__);
            return;
        }
    
        if (!empty($state['cycle']))
        {
            $this->currentCycle = $state['cycle'];
        }
        elseif ($timeHm)
        {
            $this->currentCycle = $this->getTimeCycleByString($timeHm);
        }

        if (isset($state['location_index']))
        {
            $this->currentLocationIndex = (int)$state['location_index'];
        }

        if (array_key_exists('manual_cycle', $state))
        {
            $this->manualCycle = $state['manual_cycle'] !== null ? (string)$state['manual_cycle'] : null;
        }

        $this->updateWithCycle($this->currentCycle);

        if (!empty($state['background_path']))
        {
            $this->playBackgroundPath($state['background_path']);
        }

        if (!empty($state['ambient_path']))
        {
            //$this->playAmbientPath($state['ambient_path']);
            $path = $state['ambient_path'];
        
            //не уверен за эту хуйню, надо тестить
            //но я не хочу и мне лень
            $raw = str_replace($this->soundBasePath, '', $path);
            $raw = preg_replace('/\.mp3$/', '', $raw);
        
            $this->playAmbientInternal($path, $raw, 0, '[restore]');            
        }

        if (isset($state['is_rainy']))
        {
            $this->setRainy((bool)$state['is_rainy']);
        }

        if (isset($state['is_anomaly_hum']))
        {
            $this->setAnomalyHum((bool)$state['is_anomaly_hum']);
        }

        if ($this->onCycleChange !== null)
        {
            Log::info("[Environment]: restoreState -> force onCycleChange for cycle={$this->currentCycle}");
            call_user_func($this->onCycleChange, $this->currentCycle, $this->currentCycle);
        }
    }
}
