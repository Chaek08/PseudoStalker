<?php
namespace app\forms\classes;

use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\UXApplication;
use php\time\Timer;
use action\Animation;
use php\lang\Thread;

class ParticleManager
{
    protected $form;

    public function __construct($form)
    {
        $this->form = $form;
    }
    
    public function weaponShot($actorModel, $enemyModel, float $muzzleOffsetX, float $muzzleOffsetY): void
    {
        if (!$actorModel) return;
    
        $this->spawnParticle(
            function () use ($actorModel, $muzzleOffsetX, $muzzleOffsetY) {
                $p = new UXImageView(new UXImage('res://.data/ui/particles/shoot.png'));
                $p->width = 128;
                $p->height = 128;
                $p->opacity = 1;
                $p->x = $actorModel->x + $muzzleOffsetX;
                $p->y = $actorModel->y + $muzzleOffsetY;
                return $p;
            },
            function ($p) {
                Animation::fadeOut($p, 150, function () use ($p) {
                    $p->free();
                });
            }
        );
    
        if (!$enemyModel || !$enemyModel->visible) return;
        if ($actorModel->x > $enemyModel->x) return;
    
        $hitX   = $enemyModel->x + ($enemyModel->width / 2);
        $hitY   = $actorModel->y + $muzzleOffsetY;
        $floorY = $enemyModel->y + $enemyModel->height - 20;
    
        $this->bloodBurstAtPoint($hitX, $hitY, $floorY, 4, 7);
    }

    public function bloodBurstAtPoint(float $originX, float $originY, float $floorY, int $countMin = 6, int $countMax = 10): void
    {
        $count = rand($countMin, $countMax);

        foreach (range(1, $count) as $_)
        {
            $scatterX = rand(-4, 4);
            $scatterY = rand(-4, 4);

            $angleRad = deg2rad(rand(0, 359));
            $force    = rand(35, 70);

            $impulseX = cos($angleRad) * $force;
            $impulseY = sin($angleRad) * $force - rand(15, 30);

            $this->spawnParticle(
                $this->makeBloodFactory($originX, $originY, $scatterX, $scatterY),
                $this->makeBurstAnimator($impulseX, $impulseY, $floorY)
            );
        }
    }

    public function bloodConeAtTarget($targetModel, int $countMin = 4, int $countMax = 6): void
    {
        $hitX   = $targetModel->x + ($targetModel->width / 2);
        $hitY   = $targetModel->y + 5;
        $floorY = $targetModel->y + $targetModel->height - 20;

        $this->bloodConeAtPoint($hitX, $hitY, $floorY, $countMin, $countMax);
    }

    public function bloodConeAtPoint(float $originX, float $originY, float $floorY, int $countMin = 4, int $countMax = 6): void
    {
        $count = rand($countMin, $countMax);

        foreach (range(1, $count) as $_)
        {
            $scatterX = rand(-12, 12);
            $scatterY = rand(-8, 8);

            $angleRad = deg2rad(rand(70, 100));
            $force    = rand(140, 220);

            $impulseX = cos($angleRad) * $force;
            $impulseY = -sin($angleRad) * $force;

            $this->spawnParticle(
                $this->makeBloodFactory($originX, $originY, $scatterX, $scatterY),
                $this->makeConeAnimator($impulseX, $impulseY, $floorY)
            );
        }
    }

    protected function spawnParticle(callable $factory, callable $animator): void
    {
        (new Thread(function () use ($factory, $animator) {

            $particle = $factory();

            UXApplication::runLater(function () use ($particle, $animator) {
                $this->form->form('Client')->add($particle);
                $animator($particle);
            });

        }))->start();
    }

    protected function makeBloodFactory(float $originX, float $originY, int $scatterX, int $scatterY): callable
    {
        return function () use ($originX, $originY, $scatterX, $scatterY) {

            $p = new UXImageView();
            $p->enabled = false;
            $p->opacity = 1;
            $p->image   = new UXImage('res://.data/ui/particles/blood.png');
            $p->scale   = $this->form->form('Client')->MainGame->scale;
            $p->width   = 86;
            $p->height  = 86;

            $p->x = $originX - ($p->width / 2) + $scatterX;
            $p->y = $originY - ($p->height / 2) + $scatterY;

            return $p;
        };
    }
    
    protected function makeBurstAnimator(float $impulseX, float $impulseY, float $floorY): callable
    {
        return function ($p) use ($impulseX, $impulseY, $floorY) {

            Animation::displace($p, 200, $impulseX, $impulseY);

            Timer::after(210, function () use ($p, $floorY) {

                if ($p->y < $floorY)
                {
                    Animation::moveTo(
                        $p,
                        360,
                        $p->x,
                        $floorY,
                        function () use ($p) {
                            Animation::fadeOut($p, 420, function () use ($p) {
                                $p->free();
                            });
                        }
                    );
                }
                else
                {
                    Animation::fadeOut($p, 400, function () use ($p) {
                        $p->free();
                    });
                }
            });
        };
    }

    protected function makeConeAnimator(float $impulseX, float $impulseY, float $floorY): callable
    {
        return function ($p) use ($impulseX, $impulseY, $floorY) {

            Animation::displace($p, 260, $impulseX, $impulseY);

            Timer::after(280, function () use ($p, $floorY) {

                $fall = max(0, $floorY - $p->y);
                $time = min(600, max(220, $fall * 2));

                Animation::moveTo(
                    $p,
                    $time,
                    $p->x,
                    $floorY,
                    function () use ($p) {
                        Animation::fadeOut($p, 500, function () use ($p) {
                            $p->free();
                        });
                    }
                );
            });
        };
    }
}
