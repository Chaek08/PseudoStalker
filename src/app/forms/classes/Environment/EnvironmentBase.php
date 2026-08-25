<?php
namespace app\forms\classes\Environment;

use Throwable;
use app\forms\classes\Debug;
use php\time\Time;
use php\time\Timer;
use app\forms\classes\Log;
use app\forms\classes\Environment\EnvironmentData;
use app\forms\classes\Environment\EnvironmentBrightness;
use app\forms\classes\Environment\EnvironmentRender;
use app\forms\classes\Environment\EnvironmentSound;

class EnvironmentBase
{
    protected $texturesBasePath = './gamedata/textures/';

    protected $renderManager;
    protected $brightnessManager;    
    protected $soundManager; 

    protected $currentCycle = '';
    protected $manualCycle = null;
    protected $onCycleChange = null;
    
    protected $currentLocationIndex = -1;
    protected $lastLocationIndex    = -1;
    
    protected $isPaused = false;    
   
    public function __construct($mediaView, EnvironmentBrightness $brightnessManager)
    {
        $this->brightnessManager = $brightnessManager;
        
        $this->soundManager = new EnvironmentSound($this);
        $this->renderManager = new EnvironmentRender($mediaView);
        
        if ($mediaView == null)
        {
            Debug::fatal('Environment: mediaView is null');
            return;
        }        

        $this->update();
        
        $cycle = $this->currentCycle ?: 'day';
        
        $this->brightnessManager->setTarget(EnvironmentData::$brightnessByCycle[$cycle] ?? 0.0);
        $this->brightnessManager->force();
        $this->brightnessManager->start();      

        $self = $this;
        $this->timerId = Timer::every(60 * 1000, function () use ($self) {
            $self->update();
        });

        $this->soundManager->start();
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
            $maxIndex = count(EnvironmentData::$locations) - 1; //ебать
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
    
        if (!isset(EnvironmentData::$locations[$locId]))
        {
            Debug::fail("Environment: location '{$locId}' not found");
            return [null, false, false, null, $index, $cycle];
        }    
    
        if (empty(EnvironmentData::$locations[$locId][$cycle]))
        {
            if ($locId === 'L5' && isset(EnvironmentData::$locations[$locId]['underground']))
            {
                $finalCycle = 'underground';
            }
            else
            {
                Debug::fail("Environment: missing location '{$locId}' for cycle '{$cycle}'");
                return [null, false, false, null, $index, $finalCycle];
            }
        }
    
        $useCycle = $finalCycle;
    
        if (empty(EnvironmentData::$locations[$locId][$useCycle]))
        {
            Debug::fail("Environment: missing location '{$locId}' for real cycle '{$useCycle}'");
            return [null, false, false, null, $index, $finalCycle];
        }
    
        $item    = EnvironmentData::$locations[$locId][$useCycle];
        $rawPath = $item['path'];
        $path    = $this->texturesBasePath . $rawPath . '.mp4';
        $rain    = !empty($item['rain']);
        $anomaly = !empty($item['anomaly']);
    
        return [$path, $rain, $anomaly, $rawPath, $index, $finalCycle];
    }


    public function setLocationIndex($index)
    {
        $maxIndex = count(EnvironmentData::$locations) - 1;
        $index = max(0, min($index, $maxIndex));     
    
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
    
        $this->updateWithCycle($cycle);
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


    public function pause()
    {
        if ($this->isPaused)
        {
            return;
        }
    
        $this->isPaused = true;
    
        $this->soundManager->pause();
        $this->renderManager->pause();
    }

    public function resume()
    {
        if (!$this->isPaused)
        {
            return;
        }
    
        $this->isPaused = false;    
    
        $this->soundManager->resume();
        $this->renderManager->resume();
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
            Debug::fail("Environment: no background for cycle '{$cycle}'");
            return;
        }        
        $cycleChanged = (!$firstInit && $realCycle !== $old);
        $bgChanged    = ($backgroundPath !== $this->renderManager->getCurrentPath());
    
        if ($cycleChanged || $bgChanged)
        {
            if ($old !== $realCycle)
            {
                if (!$firstInit)
                {
                    Log::info("[Environment]: cycle changed {$old} -> {$realCycle}");
                }
                $this->currentCycle = $realCycle;
                $this->brightnessManager->setTarget(EnvironmentData::$brightnessByCycle[$realCycle] ?? 0.0);                
            }
    
            $this->renderManager->play($backgroundPath);

            $this->soundManager->setRainy($rain);
            $this->soundManager->setAnomalyHum($anomaly);
    
            $this->soundManager->scheduleNextSfx();
            $this->soundManager->scheduleNextEffect();
    
            if ($cycleChanged && $this->onCycleChange !== null)
            {
                call_user_func($this->onCycleChange, $old, $realCycle);
            }
        }
        else
        {
            $this->renderManager->resume();
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
        $this->brightnessManager->stop();
    
        if ($this->soundManager)
        {
            $this->soundManager->stop();
        }
        
        if ($this->renderManager)
        {
            $this->renderManager->stop();
        }        
    
        if ($this->timerId)
        {
            $this->timerId->cancel();
            $this->timerId = null;
        }
    }
    
    public function reset()
    {
        $this->stop();
        $this->currentCycle = '';
        $this->manualCycle  = null;
    
        $this->isPaused = false;
    
        $this->currentLocationIndex = -1;
        $this->lastLocationIndex    = -1;
    
        $this->update();
        
        $cycle = $this->currentCycle ?: 'day';
        $this->brightnessManager->setTarget(EnvironmentData::$brightnessByCycle[$cycle] ?? 0.0);
        $this->brightnessManager->force();
        $this->brightnessManager->start();       
    
        $self = $this;
        $this->timerId = Timer::every(60 * 1000, function () use ($self) {
            $self->update();
        });
    
        $this->soundManager->start(); 
    }
    
    public function playBackgroundPath($path)
    {
        if (!$this->isActive()) return;
        if (!$path) return;

        $this->renderManager->play($path);
    }
    
    public function playAmbientByIndex($index)
    {
        return $this->soundManager->playAmbientByIndex($index);
    }    

    public function getState()
    {
        return [
            'location_index'   => $this->currentLocationIndex,
            'cycle'            => $this->currentCycle,
            'manual_cycle'     => $this->manualCycle,
            'background_path'  => $this->renderManager->getCurrentPath(),
            'ambient_path'     => $this->soundManager->getAmbientPath(),
            'ambient_position' => is_object($this->soundManager->getAmbientPlayer()) ? $this->soundManager->getAmbientPlayer()->positionMs : 0        
        ];
    }
    
    public function isActive()
    {
        return !$this->isPaused;
    }   
    
    public function isPaused()
    {
        return $this->isPaused;
    }    
        
    public function getCurrentCycle()
    {
        return $this->currentCycle;
    }    

    public function restoreState(array $state, $timeHm)
    {
        if (!is_array($state))
        {
            Debug::fail('Environment: restoreState got invalid state');
            return;
        }
    
        if (isset($state['location_index']))
        {
            $this->currentLocationIndex = (int)$state['location_index'];
        }

        if (array_key_exists('manual_cycle', $state))
        {
            $this->manualCycle = $state['manual_cycle'] !== null ? (string)$state['manual_cycle'] : null;
        }
        
        $cycle = !empty($state['cycle']) ? $state['cycle'] : ($timeHm ? $this->getTimeCycleByString($timeHm) : $this->getTimeCycleByString(Time::now()->toString('HH:mm')));
        $this->updateWithCycle($cycle);    

        if (!empty($state['ambient_path']))
        {
            $path = $state['ambient_path'];
            $raw = str_replace(EnvironmentSound::SOUND_BASE_PATH, '', $path);
            $raw = preg_replace('/\.mp3$/', '', $raw);
        
            $this->soundManager->restoreAmbient($path, $raw, (int)($state['ambient_position'] ?? 0));         
        }

        if ($this->onCycleChange !== null)
        {
            call_user_func($this->onCycleChange, $this->currentCycle, $this->currentCycle);
        }       
    }
}
