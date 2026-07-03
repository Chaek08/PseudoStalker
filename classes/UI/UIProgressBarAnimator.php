<?php
namespace app\forms\classes\UI;

use php\gui\animation\UXAnimationTimer;

class UIProgressBarAnimator
{
    public static $active = [];

    public static function resizeWidth($node, $target, $speed = 200, $callback = null)
    {
        if (!$node) return;

        $id = spl_object_hash($node);
        
        if (isset(self::$active[$id]))
        {
            self::$active[$id]->stop();
            unset(self::$active[$id]);
        }

        $last = microtime(true);

        $timer = new UXAnimationTimer(function() use ($node, $target, $speed, &$timer, &$last, $callback, $id)
        {
            if (!$node)
            {
                if (isset(self::$active[$id]))
                {
                    unset(self::$active[$id]);
                }
                $timer->stop();
                return;
            }

            $now = microtime(true);
            $dt = min($now - $last, 0.05);
            $last = $now;

            $step = $speed * $dt;
            $diff = $target - $node->width;

            if (abs($diff) <= $step)
            {
                $node->width = $target;
                $timer->stop();
                unset(self::$active[$id]);

                if ($callback) $callback();
                return;
            }

            $node->width += ($diff > 0 ? $step : -$step);
        });
        
        self::$active[$id] = $timer;

        $timer->start();
    }
}

