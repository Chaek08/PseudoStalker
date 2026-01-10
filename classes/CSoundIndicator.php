<?php
namespace app\forms\classes;

use php\time\Timer;
use action\Animation;
use php\gui\UXImageView;
use php\gui\UXImage;

class CSoundIndicator
{
    private $icon;
    private $ownerModel;
    private $followTimer;

    private const ICON_SIZE = 56;
    private const FOLLOW_INTERVAL = 8;

    public function __construct($ownerModel)
    {
        $this->ownerModel = $ownerModel;

        $this->icon = new UXImageView(new UXImage('res://.data/ui/maingame/speaker.png'));

        $this->icon->fitWidth  = self::ICON_SIZE;
        $this->icon->fitHeight = self::ICON_SIZE;

        $this->icon->visible = false;
        $this->icon->opacity = 0;
        $this->icon->mouseTransparent = true;

        $ownerModel->parent->children->add($this->icon);
    }

    public function playFor(int $durationMs): void
    {
        $this->forceUpdatePosition();

        uiLater(function () {
            $this->forceUpdatePosition();
        });

        $this->startFollow();
        $this->show();

        Timer::after($durationMs, function () {
            $this->hide();
        });
    }

    private function show(): void
    {
        $this->icon->visible = true;
        Animation::fadeIn($this->icon, 150);
    }

    private function hide(): void
    {
        Animation::fadeOut($this->icon, 200, function () {
            $this->icon->visible = false;
            $this->stopFollow();
        });
    }

    private function startFollow(): void
    {
        $this->stopFollow();

        $this->followTimer = Timer::every(self::FOLLOW_INTERVAL, function () {
            $this->safeFollowTick();
        });
    }

    private function stopFollow(): void
    {
        if ($this->followTimer)
        {
            $this->followTimer->cancel();
            $this->followTimer = null;
        }
    }

    private function safeFollowTick(): void
    {
        $m = $this->ownerModel;

        if (!$m || $m->width <= 0 || $m->height <= 0)
        {
            return;
        }

        if ($m->x == 0 && $m->y == 0)
        {
            return;
        }

        $this->updatePosition();
    }

    private function forceUpdatePosition(): void
    {
        $m = $this->ownerModel;

        if (!$m) return;

        $x = $m->x + ($m->width / 2) - (self::ICON_SIZE / 2);
        $y = $m->y - self::ICON_SIZE;

        if (!is_numeric($x) || !is_numeric($y)) return;

        $this->icon->x = $x;
        $this->icon->y = $y;
    }

    private function updatePosition(): void
    {
        $this->forceUpdatePosition();
    }
    
    public function destroy(): void
    {
        if (method_exists($this, 'stopFollow'))
        {
            $this->stopFollow();
        }
    
        if ($this->icon && $this->icon->parent)
        {
            $this->icon->parent->children->remove($this->icon);
        }
    
        $this->icon = null;
        $this->ownerModel = null;
    }
}
