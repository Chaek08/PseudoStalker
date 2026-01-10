<?php
namespace app\forms\classes;

use app\forms\classes\Weapons\CWeapon;
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
        $this->icon->opacity = 0;

        $ownerModel->parent->add($this->icon);
    }

    public function playFor(int $durationMs): void
    {
        $this->fxLater(function () {
            $this->updatePosition();
            $this->icon->visible = true;
            Animation::fadeIn($this->icon, 150);
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
                $this->updatePosition();
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
        $m = $this->ownerModel;
        if (!$m) return;

        $this->icon->x = $m->x + ($m->width / 2) - (self::ICON_SIZE / 2);
        $this->icon->y = $m->y - self::ICON_SIZE;
    }

    private function hide(): void
    {
        Animation::fadeOut($this->icon, 200, function () {
            $this->icon->visible = false;
            $this->stopFollow();
        });
    }

    public function destroy(): void
    {
        $this->stopFollow();

        if ($this->icon && $this->icon->parent)
        {
            $this->icon->parent->remove($this->icon);
        }

        $this->icon = null;
        $this->ownerModel = null;
    }

    private function fxLater(callable $fn): void
    {
        UXApplication::runLater($fn);
    }
}
