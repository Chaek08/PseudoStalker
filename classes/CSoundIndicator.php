<?php
namespace app\forms\classes;

use php\time\Timer;
use php\gui\UXApplication;
use action\Animation;
use php\gui\UXImageView;
use php\gui\UXImage;

class CSoundIndicator
{
    private $icon;
    private $model;
    private $parent;
    private $followTimer;

    private const ICON_SIZE = 56;
    private const FOLLOW_INTERVAL = 8;

    public function __construct($model)
    {
        $this->model  = $model;
        $this->parent = $model->parent;

        $this->icon = new UXImageView(new UXImage('res://.data/ui/maingame/speaker.png'));

        $this->icon->fitWidth  = self::ICON_SIZE;
        $this->icon->fitHeight = self::ICON_SIZE;
        $this->icon->visible  = false;
        $this->icon->opacity  = 0;
        $this->icon->mouseTransparent = true;

        $this->parent->children->add($this->icon);
    }

    public function playFor(int $durationMs): void
    {
        $this->updatePosition();

        $this->icon->visible = true;
        Animation::fadeIn($this->icon, 150);

        $this->startFollow();

        Timer::after($durationMs, function () {
            $this->hide();
        });
    }

    private function startFollow(): void
    {
        $this->stopFollow();

        $this->followTimer = Timer::every(self::FOLLOW_INTERVAL, function () {
            UXApplication::runLater(function () {
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
        $m = $this->model;
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
            $this->icon->parent->children->remove($this->icon);
        }

        $this->icon  = null;
        $this->model = null;
        $this->parent = null;
    }
}
