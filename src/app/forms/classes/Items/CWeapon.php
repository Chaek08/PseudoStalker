<?php
namespace app\forms\classes\Items;

use app\forms\classes\Log;
use app\forms\classes\Debug;
use action\Media;
use php\lang\Thread;
use php\gui\animation\UXAnimationTimer;
use behaviour\custom\DropShadowEffectBehaviour;
use php\gui\UXApplication;
use php\time\Timer;
use action\Animation;
use script\MediaPlayerScript;
use php\gui\UXImageView;
use php\gui\UXImage;
use behaviour\custom\ColorAdjustEffectBehaviour;
use app\forms\classes\Environment\EnvironmentBase;
use app\forms\classes\Environment\EnvironmentBrightness;
use app\forms\classes\DimaAsyncHackEbatNaxyi;

abstract class CWeapon extends CItem
{
    protected $owner;
    protected $type; 

    protected $ammoItemId = '';
    protected $magSize = 0;
    protected $reloadDelay = 1000;
    protected $particleOffset = [0, 0];
    protected $recoilPower;

    protected $ammo = 0;
    protected $reloading = false;

    protected $jammed = false;
    protected $jamHandled = false;

    protected $unlimitedAmmo = false;

    protected $recoilOffsetX = 0;
    protected $recoilOffsetY = 0;

    protected $reloadAnimOffsetX = 0;
    protected $reloadAnimOffsetY = 0;
    protected $reloadAnimRotate = 0;

    protected $view = null;
    protected $attachTimer = null;
    protected $fxTimers = [];

    protected $offsetX = 0;
    protected $offsetY = 0;

    public $dropShadowEffect;

    protected $soundShot;
    protected $soundEmpty;
    protected $soundDraw;
    protected $soundReload;
    protected $soundClose = 'res://.data/audio/weapon/generic_close.mp3';

    protected $shotPlayers = [];
    protected $shotPoolSize = 6;
    protected $shotSeq = 0;
      
    public function __construct($owner, string $id, string $name, string $desc, float $weight, int $price, string $icon, int $condition = 100)
    {
        parent::__construct($id, $name, $desc, $weight, $price, $icon, $condition);
    
        $this->owner = $owner;
    }
        
    abstract public function getType(): string;
    abstract protected function spritePath(): string;
    abstract protected function spriteOffsets(): array;
    abstract protected function hudMagImagePath(): string;

    public function attach(): void
    {
        $offsets = $this->spriteOffsets();
        $this->offsetX = $offsets[0];
        $this->offsetY = $offsets[1];
        
        $path = $this->spritePath();

        $this->fxLater(function () use ($path) {
            $this->view = new UXImageView(new UXImage($path));
            $model = $this->owner->GetModel();
            $this->view->x = $model->x + $this->offsetX;
            $this->view->y = $model->y + $this->offsetY;
            
            $this->owner->form('Client')->MainGame->content->EnvironmentBrightness->register($this->view);
                        
            $this->dropShadowEffect = new DropShadowEffectBehaviour();
            $this->dropShadowEffect->color   = '#1a1a1a';
            $this->dropShadowEffect->offsetX = 0;
            $this->dropShadowEffect->offsetY = 0;
            $this->dropShadowEffect->radius  = 10;
            $this->dropShadowEffect->spread  = 0;
            $this->dropShadowEffect->when    = 'ALWAYS';
            $this->dropShadowEffect->apply($this->view);     
            if (isset($GLOBALS['ShadowsSwitcher_IsOn']) && !$GLOBALS['ShadowsSwitcher_IsOn'])
            {
                $this->dropShadowEffect->disable();
            }
                   
            $this->owner->add($this->view);

            DimaAsyncHackEbatNaxyi::playSfxSound($this->soundDraw, 'weapon_draw');

            $this->startFollowTimer();

            $this->view->on('mouseDown', function () {
                $this->owner->Shoot();
            });

            $this->owner->UpdateMagazine();
        });
    }

    public function detach(): void
    {
        if ($this->attachTimer)
        {
            $this->attachTimer->stop();
            $this->attachTimer = null;
        }
        
        if ($this->dropTimer)
        {
            $this->dropTimer->stop();
            $this->dropTimer = null;
        }        
    
        $this->stopAllFxTimers();
    
        $this->fxLater(function () {
            if ($this->view)
            {
                $view = $this->view;
    
                $this->owner->form('Client')->MainGame->content->EnvironmentBrightness->unregister($view);
                
                $this->owner->remove($view);
    
                $this->view = null;
    
                DimaAsyncHackEbatNaxyi::playSfxSound($this->soundClose, 'weapon_close');
            }
        });
    }
    
    public function destroy(): void
    {
        if ($this->attachTimer)
        {
            $this->attachTimer->stop();
            $this->attachTimer = null;
        }
        
        $this->stopAllFxTimers();
    
        if ($this->view)
        {
            $this->owner->form('Client')->MainGame->content->EnvironmentBrightness->unregister($this->view);
            $this->owner->remove($this->view);
            $this->view = null;
        }
    }        
    
    public function drop(): void
    {
        if (!$this->view)
        {
            return;
        }
    
        if ($this->attachTimer)
        {
            $this->attachTimer->stop();
            $this->attachTimer = null;
        }
    
        $this->stopAllFxTimers();
    
        $vx = rand(-1, 1);
        $vy = -3.5;
        $gravity = 0.38;
    
        $model = $this->owner->GetModel();
        $groundY = $model->y + $model->height - 70;
    
        $rotation = $this->view->rotate;
        $targetRotation = rand(-12, 12);
    
        $timer = null;
    
        $timer = new UXAnimationTimer(function () use (&$timer, &$vx, &$vy, $gravity, $groundY, &$rotation, $targetRotation) {
    
            if (!$this->view)
            {
                $timer->stop();
                return;
            }
    
            $this->view->x += $vx;
            $this->view->y += $vy;
    
            $rotation += ($targetRotation - $rotation) * 0.12;
            $this->view->rotate = $rotation;
    
            $vy += $gravity;
    
            if ($this->view->y >= $groundY)
            {
                $this->view->y = $groundY;
    
                if (abs($vy) > 0.8)
                {
                    $vy *= -0.2;
                    $vx *= 0.5;
                }
                else
                {
                    $this->view->y = $groundY;
                    $this->view->rotate = $targetRotation;
    
                    $timer->stop();
                }
            }
        });
    
        $this->registerTimer($timer);
        $timer->start();
    }
    
    public function shoot(): void
    {
        if ($this->reloading) { return; }
        
        if ($this->ammo <= 0)
        {
            $this->reload();
            $this->playEmpty();
            return;
        }
    
        if ($this->ammo > 1 && $this->ammo < $this->magSize && rand(1, 600) === 1) { $this->jammed = true; }
    
        if ($this->jammed && !$this->jamHandled)
        {
            $this->jamHandled = true;
            $this->playEmpty();
            $this->showJamHint();
            return;
        }
        if ($this->jammed) { $this->playEmpty(); return; }
        
        if ($this->owner->form('Client')->MainGame->content->isMP)
        {
            $this->owner->form('Client')->MainGame->content->NET_UpdateShotOnServer();
        }        
    
        $this->ammo--;
        $this->owner->UpdateMagazine();
        
        $this->playShotFX();
    }
    
    public function playShotFX()
    {
        $this->playShotOverlapped();
        $this->spawnMuzzleAndBlood();
        $this->playRecoil();
    }    
    
    public function reload(): void
    {
        if ($this->reloading) return;
        if ($this->ammo >= $this->magSize && !$this->jammed) return;
    
        $inv = $this->getInventoryContent();
        $ammoItem = $inv->getItem($this->ammoItemId);
    
        $totalAmmo = $this->unlimitedAmmo ? $this->magSize : ($ammoItem ? $ammoItem->getCount() : 0);
    
        if ($totalAmmo <= 0 && !$this->jammed) return;
    
        DimaAsyncHackEbatNaxyi::playSfxSound($this->soundReload, 'weapon_reload');
    
        $needed = max(0, $this->magSize - $this->ammo);
        $this->reloading = true;
        
        if ($this->owner->form('Client')->MainGame->content->isMP)
        {
            $this->owner->form('Client')->MainGame->content->NET_UpdateReloadOnServer();
        }        
    
        $this->playReloadAnimation();
    
        Timer::after($this->reloadDelay, function () use ($inv, $needed) {
    
            $this->fxLater(function () use ($inv, $needed) {
    
                if ($this->unlimitedAmmo)
                {
                    $this->ammo = $this->magSize;
                }
                else
                {
                    $ammoItem = $inv->getItem($this->ammoItemId);
    
                    if ($ammoItem)
                    {
                        $totalAmmo = $ammoItem->getCount();
    
                        if ($totalAmmo > 0)
                        {
                            if ($totalAmmo < $needed)
                            {
                                $this->ammo += $totalAmmo;
                                $ammoItem->setCount(0);
                            }
                            else
                            {
                                $this->ammo += $needed;
                                $ammoItem->removeCount($needed);
                            }
    
                            $inv->updateItemCount($ammoItem);
                        }
                    }
                }
    
                $this->owner->UpdateMagazine();
                $this->jammed = false;
                $this->jamHandled = false;
                $this->reloading = false;
            });
    
        });
    }

    public function isReloading(): bool { return $this->reloading; }

    protected function startFollowTimer(): void
    {
        if ($this->attachTimer)
        {
            $this->attachTimer->stop();
            $this->attachTimer = null;
        }
    
        $this->attachTimer = new UXAnimationTimer(function () {
    
            if (!$this->view) return;
    
            $m = $this->owner ? $this->owner->GetModel() : null;
            if (!$m) return;
    
            $this->view->x = $m->x + $this->offsetX + $this->recoilOffsetX + $this->reloadAnimOffsetX;
            $this->view->y = $m->y + $this->offsetY + $this->recoilOffsetY + $this->reloadAnimOffsetY;
            
            $this->view->rotate = $this->reloadAnimRotate;
        });
    
        $this->attachTimer->start();
    }

    protected function playShotOverlapped(): void
    {
        $base = strtolower($this->type) . '_shot';
        $tag = $base . '_' . $this->shotSeq;
        $this->shotSeq = ($this->shotSeq + 1) % $this->shotPoolSize;
        
        DimaAsyncHackEbatNaxyi::playSfxSound($this->soundShot, $tag);
    }

    protected function playEmpty(): void
    {
        DimaAsyncHackEbatNaxyi::playSfxSound($this->soundEmpty, 'weapon_empty');
    }

    protected function spawnMuzzleAndBlood(): void
    {
        $actor = $this->owner->GetModel();
        $enemyEntity = $this->owner->getEnemy();
        $enemy = $enemyEntity ? $enemyEntity->GetModel() : null;
        
        if (!$actor) return;
        
        $this->owner->getParticles()->weaponShot($actor, $enemy, $this->particleOffset[0], $this->particleOffset[1]);
        
        if ($enemy && $enemy->visible && $actor->x < $enemy->x)
        {
            $this->owner->DamageEnemy(null, false);
        }
    }
    
    protected function playRecoil(): void
    {
        $power = $this->recoilPower;
    
        $this->recoilOffsetX = $power;
    
        Timer::after(40, function () {
    
            if (!$this->view) return;
    
            $steps = 6;
    
            $stepY = $this->recoilOffsetY / $steps;
            $stepX = $this->recoilOffsetX / $steps;
    
            $i = 0;
    
            $timer = new UXAnimationTimer(function () use (&$i, $steps, $stepX, $stepY, &$timer) {
    
                if (!$this->view)
                {
                    $timer->stop();
                    return;
                }
    
                $this->recoilOffsetY -= $stepY;
                $this->recoilOffsetX -= $stepX;
    
                $i++;
    
                if ($i >= $steps)
                {
                    $this->recoilOffsetY = 0;
                    $this->recoilOffsetX = 0;
                    $timer->stop();
                }
            });
    
            $this->registerTimer($timer);
            $timer->start();
        });
    }
    
    protected function playReloadAnimation(): void
    {
        $downSteps = 18;
        $upSteps = 20;
    
        $maxLift = -30;
        $maxDropY = 6;
        $sideSwing = 12;
        $backShift = -20;
    
        $i = 0;
    
        $timerDown = new UXAnimationTimer(function () use (&$i, $downSteps, $maxLift, $maxDropY, $sideSwing, $backShift, &$timerDown) {
    
            if (!$this->view)
            {
                $timerDown->stop();
                return;
            }
    
            $t = $i / $downSteps;
    
            $ease = 1 - pow(1 - $t, 3);
    
            $jerk = 0;
            if ($t < 0.15)
            {
                $jt = $t / 0.15;
                $jerk = 9 * (1 - pow(1 - $jt, 3));
            }
    
            $this->reloadAnimRotate = $maxLift * sin($t * M_PI_2);
            $this->reloadAnimOffsetY = $maxDropY * $ease;
            $this->reloadAnimOffsetX = $jerk + ($backShift * sin($t * M_PI_2)) + (-sin($t * M_PI) * $sideSwing * 0.4);
    
            $i++;
    
            if ($i > $downSteps)
            {
                $timerDown->stop();
            }
        });
    
        $this->registerTimer($timerDown);
        $timerDown->start();
    
        Timer::after($this->reloadDelay, function () use ($upSteps) {
    
            if (!$this->view) return;
    
            $i = 0;
    
            $startRot = $this->reloadAnimRotate;
            $startY   = $this->reloadAnimOffsetY;
            $startX   = $this->reloadAnimOffsetX ?? 0;
    
            $timerUp = new UXAnimationTimer(function () use (&$i, $upSteps, $startRot, $startY, $startX, &$timerUp) {
    
                if (!$this->view)
                {
                    $timerUp->stop();
                    return;
                }
    
                $t = $i / $upSteps;
    
                $c1 = 1.70158;
                $c3 = $c1 + 1;
                $ease = 1 + $c3 * pow($t - 1, 3) + $c1 * pow($t - 1, 2);
    
                $this->reloadAnimRotate = $startRot * (1 - $ease);
                $this->reloadAnimOffsetY = $startY * (1 - $ease);
                $this->reloadAnimOffsetX = $startX * (1 - $ease);
    
                $this->reloadAnimOffsetX += sin($t * M_PI) * 2;
    
                $shake = sin($t * M_PI * 8) * (1 - $t) * 3;
                $this->reloadAnimRotate += $shake;
    
                $i++;
    
                if ($i > $upSteps)
                {
                    $this->reloadAnimRotate = 0;
                    $this->reloadAnimOffsetY = 0;
                    $this->reloadAnimOffsetX = 0;
                    $timerUp->stop();
                }
            });
    
            $this->registerTimer($timerUp);
            $timerUp->start();
        });
    }

    protected function getInventoryContent()
    {
        return $this->owner->form('Client')->Inventory->content;
    }

    public function getAmmo(): int { return $this->ammo; }
    public function getMagSize(): int { return $this->magSize; } 
    public function setAmmo(int $n): void { $this->ammo = max(0, min($this->magSize, $n)); }
    
    public function getTotalAmmoFromInventory(): int
    {
        return $this->getInventoryContent()->getItem($this->ammoItemId)->getCount();
    }
    
    public function setUnlimitedAmmo(bool $state): void
    {
        $this->unlimitedAmmo = $state;
        if ($state)
        {
            $this->owner->UpdateMagazine();
        }
    }
    
    public function hasUnlimitedAmmo(): bool
    {
        return $this->unlimitedAmmo;
    }  

    public function hudMagImage(): string
    {
        return $this->hudMagImagePath();
    }
    
    public function softHide(): void
    {
        if (!$this->view) return;
        UXApplication::runLater(function () {
            if ($this->view) $this->view->hide();
        });
    }
    
    public function softShow(): void
    {
        if (!$this->view) return;
        UXApplication::runLater(function () {
            if ($this->view) $this->view->show();
        });
    }

    public function exportState(): array { return ['ammo' => $this->ammo, 'jammed' => $this->jammed, 'jamHandled' => $this->jamHandled]; }
    
    public function importState(array $s): void
    {
        if (isset($s['ammo'])) $this->setAmmo((int)$s['ammo']);
        if (isset($s['jammed'])) $this->jammed = (bool)$s['jammed'];
        if (isset($s['jamHandled'])) $this->jamHandled = (bool)$s['jamHandled'];
    }
        
    
    protected function showJamHint(): void
    {
        if (method_exists($this->owner, 'showJamHintUI'))
        {
            $this->owner->showJamHintUI('GunJmammed');
        }
    }
    
    public function enableShadow(): void
    {
        if ($this->dropShadowEffect)
        {
            $this->dropShadowEffect->enable();
        }
    }
    
    public function disableShadow(): void
    {
        if ($this->dropShadowEffect)
        {
            $this->dropShadowEffect->disable();
        }
    }
    
    protected function fxLater(callable $fn): void { UXApplication::runLater($fn); }
    
    protected function registerTimer($timer): void
    {
        $this->fxTimers[] = $timer;
    }    
    
    protected function stopAllFxTimers(): void
    {
        foreach ($this->fxTimers as $t)
        {
            if ($t)
            {
                $t->stop();
            }
        }
    
        $this->fxTimers = [];
    } 
    
    public function resetToDefaultState(): void
    {
        $this->ammo = $this->magSize;
    
        $this->jammed = false;
        $this->jamHandled = false;
        $this->reloading = false;
    
        $this->recoilOffsetX = 0;
        $this->recoilOffsetY = 0;
    
        $this->reloadAnimOffsetX = 0;
        $this->reloadAnimOffsetY = 0;
        $this->reloadAnimRotate = 0;
    
        $this->stopAllFxTimers();
    }    
}
