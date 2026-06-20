<?php
namespace app\forms\classes\Environment;

use behaviour\custom\ColorAdjustEffectBehaviour;
use php\gui\animation\UXAnimationTimer;

class EnvironmentBrightness
{
    protected $brightness = 0.0;

    protected $targets = [];

    protected $current = 0.0;
    protected $target  = 0.0;

    protected $lerp = 0.25;

    protected $timer = null;

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
        if (!$node)
        {
            return;
        }

        $key = spl_object_hash($node);

        if (isset($this->targets[$key]))
        {
            return;
        }

        $this->targets[$key] = $node;

        $this->applyTo($node);
    }

    public function unregister($node): void
    {
        if (!$node)
        {
            return;
        }

        $key = spl_object_hash($node);

        unset($this->targets[$key]);
    }

    protected function applyTo($node): void
    {
        if (!$node)
        {
            return;
        }

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

    public function setTarget(float $value): void
    {
        $this->target = $value;
    }

    public function force(): void
    {
        $this->current = $this->target;

        $this->set($this->current);
    }

    public function start(): void
    {
        if ($this->timer)
        {
            return;
        }

        $this->timer = new UXAnimationTimer(function ()
        {
            $next = $this->current
                  + ($this->target - $this->current) * $this->lerp;

            if (abs($this->target - $next) < 0.005)
            {
                $next = $this->target;
            }

            $next = max(-1.0, min(1.0, $next));

            $this->current = $next;

            $this->set($next);
        });

        $this->timer->start();
    }

    public function stop(): void
    {
        if (!$this->timer)
        {
            return;
        }

        $this->timer->stop();
        $this->timer = null;
    }
}