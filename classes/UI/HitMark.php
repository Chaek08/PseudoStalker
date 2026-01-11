<?php
namespace app\forms\classes\UI;

use php\gui\UXImage;
use php\time\Timer;
use action\Animation;
use php\time\Time;

class HitMark 
{
    protected $view;

    protected $lastHitTime = 0;
    protected $level = 1;
    protected $visibleUntil = 0;

    public function __construct($imageView)
    {
        $this->view = $imageView;
        $this->view->visible = false;
    }

    public function play(): void
    {
        $now = Time::millis();
        $diff = $now - $this->lastHitTime;
        $this->lastHitTime = $now;

        if ($diff < 500 && $this->level < 4)
        {
            $this->level++;
        }

        $this->updateImage();

        $this->view->opacity = 0;
        $this->view->visible = true;
        Animation::fadeIn($this->view, 100);

        $this->visibleUntil = $now + 500;

        Timer::after(500, function () {
            if (Time::millis() >= $this->visibleUntil)
            {
                Animation::fadeOut($this->view, 300);
                Timer::after(300, function () {
                    $this->level = 1;
                });
            }
        });

        Timer::after(1500, function () {
            if (Time::millis() - $this->lastHitTime >= 1500 && $this->level > 1)
            {
                $this->level--;
            }
        });
    }
    
    public function isVisible(): bool
    {
        return $this->view->visible === true;
    }    

    protected function updateImage(): void
    {
        $this->view->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_{$this->level}.png");
    }

    public function reset(): void
    {
        $this->level = 1;
        $this->view->visible = false;
    }

    public function hide(): void
    {
        $this->view->visible = false;
    }
}