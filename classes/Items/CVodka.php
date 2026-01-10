<?php
namespace app\forms\classes\Items;

use action\Geometry;
use action\Animation;
use php\gui\UXImageArea;

class CVodka 
{
    private $game;
    private $view;
    private $actor;
    private $enemy;

    private $defaultPosition = [256, 696];
    private $defaultOpacity  = 100;

    public function __construct($game, UXImageArea $view, $actor, $enemy)
    {
        $this->game  = $game;
        $this->view  = $view;
        $this->actor = $actor;
        $this->enemy = $enemy;
    }

    public function enable(): void
    {
        $this->view->enabled = true;
    }

    public function disable(): void
    {
        $this->view->enabled = false;
    }

    public function show(): void
    {
        $this->view->show();
    }

    public function hide(): void
    {
        $this->view->hide();
    }

    public function setOpacity(int $value): void
    {
        $this->view->opacity = $value;
    }

    public function resetOpacity(): void
    {
        $this->view->opacity = $this->defaultOpacity;
    }

    public function setPosition(int $x, int $y): void
    {
        $this->view->position = [$x, $y];
    }

    public function resetPosition(): void
    {
        [$x, $y] = $this->defaultPosition;
        $this->view->position = [$x, $y];
    }

    public function resetVisual(): void
    {
        $this->resetOpacity();
        $this->resetPosition();
        //$this->enable();
        //$this->show();
    }

    public function enableShadow(): void
    {
        if ($this->view->dropShadowEffect)
            $this->view->dropShadowEffect->enable();
    }

    public function disableShadow(): void
    {
        if ($this->view->dropShadowEffect)
            $this->view->dropShadowEffect->disable();
    }

    public function spawn(): void
    {
        $actor = $this->actor->GetModel();

        $this->view->x = $actor->x + ($actor->width * 1.2);
        $this->view->y = $actor->y + $actor->height - $this->view->height - 10;

        $this->resetOpacity();
        //$this->enable();
        $this->show();
    }

    public function despawn(): void
    {
        $this->hide();
        //$this->enable();
        $this->resetVisual();
    }

    public function isVisible(): bool
    {
        return $this->view->visible;
    }

    public function isEnabled(): bool
    {
        return $this->view->enabled;
    }
    
    public function setBrightness(float $value): void
    {
        if ($this->view->colorAdjustEffect)
            $this->view->colorAdjustEffect->brightness = $value;
    }
    
    public function throwAtEnemy(callable $onHit = null): void
    {
        $vodka = $this->view;
        $enemy = $this->enemy->GetModel();
    
        $targetX = $enemy->x + ($enemy->width / 2) - ($vodka->width / 2);
        $targetY = $enemy->y;
    
        $startX = $vodka->x;
        $startY = $vodka->y;
    
        $dx = $targetX - $startX;
        $dy = $targetY - $startY;
    
        $distance = sqrt($dx * $dx + $dy * $dy);
        $duration = (int)($distance / 0.9);
    
        $initialEnemyX = $enemy->x;
        $initialEnemyY = $enemy->y;
    
        $floorY = $enemy->y + $enemy->height - $vodka->height - 10;
    
        Animation::moveTo(
            $vodka,
            $duration,
            $targetX,
            $targetY,
            function () use ($vodka, $enemy, $initialEnemyX, $initialEnemyY, $floorY, $onHit)
            {
                $enemyStillHere = $enemy->x === $initialEnemyX && $enemy->y === $initialEnemyY;
    
                if ($enemyStillHere && Geometry::intersect($vodka, $enemy))
                {
                    if ($onHit) {
                        $onHit($enemy);
                    }
    
                    Animation::displace($vodka, 300, -150, -10, function () use ($vodka, $floorY) {
                        Animation::moveTo($vodka, 300, $vodka->x, $floorY);
                    });
                }
                else
                {
                    Animation::moveTo($vodka, 400, $vodka->x, $floorY);
                }
            }
        );
    }

    private function onHit(): void
    {
        $this->game->DamageEnemy(null, false);
        $this->game->SpawnParticle($this->enemy->GetModel());
    }

    private function returnToFloor(): void
    {
        $enemy = $this->enemy->GetModel();
        $floorY = $enemy->y + $enemy->height - $this->view->height - 10;

        Animation::moveTo($this->view, 300, $this->view->x, $floorY);
    }

    public function returnToActor(): void
    {
        $actor = $this->actor->GetModel();

        Animation::moveTo(
            $this->view,
            400,
            $actor->x + ($actor->width * 1.2),
            $this->view->y
        );
    }    
}