<?php
namespace app\forms\classes;

use behaviour\custom\ColorAdjustEffectBehaviour;
use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\UXApplication;
use php\time\Timer;
use action\Animation;
use php\lang\Thread;
use behaviour\custom\GlowEffectBehaviour;
use behaviour\custom\BloomEffectBehaviour;
use php\gui\animation\UXAnimationTimer;

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
                $p->opacity = 0;
                $p->x = $actorModel->x + $muzzleOffsetX;
                $p->y = $actorModel->y + $muzzleOffsetY;
        
                $bloom = new BloomEffectBehaviour();
                $bloom->threshold = 1.0;
                $bloom->apply($p);
                
                $glow = new GlowEffectBehaviour();
                $glow->level = 0.0;
                $glow->apply($p);
                
                $p->data['bloom'] = $bloom;
                $p->data['glow']  = $glow;
            
                return $p;
            },
            function ($p) {
                $p->opacity = 0;
            
                $bloom = $p->data['bloom'] ?? null;
                $glow  = $p->data['glow']  ?? null;
            
                if ($bloom) $bloom->threshold = 0.15;
                if ($glow)  $glow->level = 0.35;
            
                Animation::fadeIn($p, 30, function () use ($p, $bloom, $glow) {
            
                    Timer::after(70, function () use ($bloom, $glow) {
                        if ($bloom) $bloom->threshold = 1.0;
                        if ($glow)  $glow->level = 0.0;
                    });
            
                    Timer::after(80, function () use ($p) {
                        Animation::fadeOut($p, 260, function () use ($p) {
                            $p->free();
                        });
                    });
                });
            }
        );
        
        $hitX   = $enemyModel->x + ($enemyModel->width / 2);
        $hitY   = $actorModel->y + $muzzleOffsetY;
        $floorY = $enemyModel->y + $enemyModel->height - 80;        
        
        $this->spawnBullet($actorModel, $enemyModel, $muzzleOffsetX, $muzzleOffsetY);
        $this->spawnShell($actorModel, $muzzleOffsetX, $muzzleOffsetY, $floorY);    

        if (!$enemyModel || !$enemyModel->visible) return;
        if ($actorModel->x > $enemyModel->x) return;    
    
        $this->bloodBurstAtPoint($hitX, $hitY, $floorY, 4, 7);
    }
    
    public function spawnBullet($actorModel, $enemyModel, float $offsetX, float $offsetY): void
    {
        if (!$actorModel || !$enemyModel) return;
    
        $this->spawnParticle(
    
            function () use ($actorModel, $offsetX, $offsetY) {
    
                $p = new UXImageView();
                $p->enabled = false;
                $p->opacity = 1;
                $p->image = new UXImage('res://.data/ui/particles/bullet_.png');
    
                $p->width  = 32;
                $p->height = 32;
    
                $muzzleX = $actorModel->x + $offsetX;
                $muzzleY = $actorModel->y + $offsetY + 60; //пуля летит ровно
    
                $p->x = $muzzleX - ($p->width / 2);
                $p->y = $muzzleY - ($p->height / 2);
    
                return $p;
            },
    
            function ($p) use ($actorModel, $enemyModel, $offsetX, $offsetY) {
    
                $muzzleX = $actorModel->x + $offsetX;
                $muzzleY = $actorModel->y + $offsetY;
    /*
                if (!$enemyModel || !$enemyModel->visible || $actorModel->x > $enemyModel->x)
                {
                    $targetX = $muzzleX + 2000;
                    $targetY = $muzzleY + rand(-50, 50);
                }
                else
                {
                    $targetX = $enemyModel->x + ($enemyModel->width / 2);
                    $targetY = $enemyModel->y + ($enemyModel->height / 2);
                }
*/
                $targetX = $muzzleX + 2000;
                $targetY = $muzzleY + rand(-50, 50);
                    
                $distanceX = $targetX - $muzzleX;
                $distanceY = $targetY - $muzzleY;
                
                $distance = sqrt($distanceX * $distanceX + $distanceY * $distanceY);
                
                $speed = 12;
                $duration = $distance / $speed;
                
                Animation::displace(
                    $p,
                    $duration,
                    $distanceX,
                    $distanceY
                );
                
                Timer::after($duration + 10, function () use ($p) {
                    Animation::fadeOut($p, 60, function () use ($p) {
                        $p->free();
                    });
                });
            }
        );
    }
        
    public function spawnShell($actorModel, float $offsetX, float $offsetY, float $groundY): void
    {
        if (!$actorModel) return;
    
        $this->spawnParticle(
    
            function () use ($actorModel, $offsetX, $offsetY) {
    
                $p = new UXImageView();
                $p->enabled = false;
                $p->opacity = 1;
                $p->image   = new UXImage('res://.data/ui/particles/bullet_.png');
    
                $p->width  = 32;
                $p->height = 32;
    
                $p->x = $actorModel->x + $offsetX;
                $p->y = $actorModel->y + $offsetY;
    
                return $p;
            },
    
            function ($p) use ($groundY) {
            
                $groundY += 75; //для гильзы плюсуем
                
                $vx = -rand(6, 10);
                $vy = -rand(12, 16);
    
                $gravity  = 0.6;
                $friction = 0.98;
    
                $rotationSpeed = rand(8, 16);
    
                $timer = new UXAnimationTimer(function() use ($p, &$vx, &$vy, $gravity, $friction, $rotationSpeed, $groundY, &$timer) {
    
                    if (!$p)
                    {
                        $timer->stop();
                        return;
                    }
    
                    $p->x += $vx;
                    $p->y += $vy;
    
                    $vy += $gravity;
                    $vx *= $friction;
    
                    $p->rotate += $rotationSpeed;
    
                    if ($p->y >= $groundY - $p->height)
                    {
                        $p->y = $groundY - $p->height;
                    
                        $vy = -$vy * 0.45;
                        $vx *= 0.7;
                    
                        if (abs($vy) < 1.5)
                        {
                            $vy = 0;
                            $vx *= 0.3;
                    
                            if (abs($vx) < 0.3)
                            {
                                $timer->stop();
                    
                                Timer::after(2500, function () use ($p) {
                    
                                    Animation::fadeOut(
                                        $p,
                                        600,
                                        function () use ($p) {
                                            $p->free();
                                        }
                                    );
                                });
                            }
                        }
                    }
                });
                
                $timer->start();
            }
        );
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
                        
            $this->spawnParticle($this->makeBloodFactory($originX, $originY, $scatterX, $scatterY), $this->makeBurstAnimator($impulseX, $impulseY, $floorY));
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

            $this->spawnParticle($this->makeBloodFactory($originX, $originY, $scatterX, $scatterY), $this->makeConeAnimator($impulseX, $impulseY, $floorY));
        }
    }

    protected function spawnParticle(callable $factory, callable $animator): void
    {
        (new Thread(function () use ($factory, $animator) {
    
            $particle = $factory();
    
            UXApplication::runLater(function () use ($particle, $animator) {
    
                $this->form->form('Client')->MainGame->content->add($particle);
    
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
    
            $dx = $impulseX;
            $dy = $impulseY;
    
            Animation::displace($p, 200, $dx, $dy);
    
            Timer::after(210, function () use ($p, $floorY) {
    
                $targetY = $floorY;
    
                if ($p->y < $targetY)
                {
                    Animation::moveTo(
                        $p,
                        360,
                        $p->x,
                        $targetY,
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