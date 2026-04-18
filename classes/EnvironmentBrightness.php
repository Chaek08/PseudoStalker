<?php
namespace app\forms\classes;

use app\forms\classes\Log;
use behaviour\custom\ColorAdjustEffectBehaviour;

class EnvironmentBrightness
{
    protected $brightness = 0.0;
    protected $targets = [];

    public function set(float $value): void
    {
        $this->brightness = $value;

        foreach ($this->targets as $target)
        {
            $this->applyTo($target);
        }
    }

    public function register($node): void
    {
        if (!$node) return;

        $key = spl_object_hash($node);
        
        if (isset($this->targets[$key])) return;
         
        $this->targets[$key] = $node;      
         
        $this->applyTo($node);
        
        //Log::info("[EnvironmentBrightness]: REGISTER node {$key}");
    }

    public function unregister($node): void
    {
        if (!$node) return;

        $key = spl_object_hash($node);

        if (isset($this->targets[$key]))
        {
            unset($this->targets[$key]);
            //Log::info("[EnvironmentBrightness]: UNREGISTER node {$key}");
        }   
    }

    protected function applyTo($node): void
    {
        if (!$node) return;

        if (!$node->colorAdjustEffect)
        {
            (new ColorAdjustEffectBehaviour())->apply($node);
        }

        $node->colorAdjustEffect->brightness = $this->brightness;
    }

    public function get(): float
    {
        return $this->brightness;
    }
}