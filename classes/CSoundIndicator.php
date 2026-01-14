<?php
namespace app\forms\classes;

use php\time\Timer;
use action\Animation;
use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\UXApplication;

class CSoundIndicator
{
    private $icon;
    private $ownerModel;
    private $followTimer;
    private $isHiding = false;

    private const ICON_SIZE = 56;
    private const FOLLOW_INTERVAL = 1;

    public function __construct($ownerModel)
    {
        $this->ownerModel = $ownerModel;

        $this->icon = new UXImageView(new UXImage('res://.data/ui/maingame/speaker.png'));

        $this->icon->fitWidth  = self::ICON_SIZE;
        $this->icon->fitHeight = self::ICON_SIZE;
        $this->icon->mouseTransparent = true;
        $this->icon->visible = false;
        $this->icon->opacity = 0.85;
        $this->icon->smooth = true;

        $ownerModel->parent->add($this->icon);
    }

    public function playFor(int $durationMs): void
    {
        $this->fxLater(function () {
            if (!$this->icon) return;

            $this->isHiding = false;
            $this->updatePosition();
            $this->icon->opacity = 0;
            $this->icon->visible = true;
            
            Animation::fadeTo($this->icon, 150, 0.85);
        });

        $this->startFollow();

        Timer::after($durationMs, function () {
            $this->fxLater(function () {
                $this->hide();
            });
        });
    }

    private function startFollow(): void
    {
        $this->stopFollow();

        $this->followTimer = Timer::every(self::FOLLOW_INTERVAL, function () {
            $this->fxLater(function () {
                if ($this->icon) {
                    $this->updatePosition();
                }
            });
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

    private function updatePosition(): void
    {
        if (!$this->icon || !$this->ownerModel) return;

        $m = $this->ownerModel;

        $this->icon->x = $m->x + ($m->width / 2) - (self::ICON_SIZE / 2);
        $this->icon->y = $m->y - self::ICON_SIZE;
    }

    private function hide(): void
    {
        if ($this->isHiding) return;
        $this->isHiding = true;

        $icon = $this->icon;
        if (!$icon) return;

        Animation::fadeTo($icon, 0, 200, function () use ($icon) {
            $icon->visible = false;
            $this->stopFollow();
            $this->isHiding = false;
        });
    }

    public function destroy(): void
    {
        $this->stopFollow();

        $icon = $this->icon;
        $this->icon = null;
        $this->ownerModel = null;

        if ($icon)
        {
            $icon->visible = false;
            $icon->opacity = 0;

            if ($icon->parent)
            {
                $icon->parent->remove($icon);
            }
        }
    }

    private function fxLater(callable $fn): void
    {
        UXApplication::runLater($fn);
    }
}
