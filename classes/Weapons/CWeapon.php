<?php
namespace app\forms\classes\Weapons;

use php\gui\animation\UXAnimationTimer;
use behaviour\custom\DropShadowEffectBehaviour;
use php\gui\UXApplication;
use php\time\Timer;
use action\Animation;
use script\MediaPlayerScript;
use php\gui\UXImageView;
use php\gui\UXImage;
use behaviour\custom\ColorAdjustEffectBehaviour;

abstract class CWeapon
{
    protected $owner;
    protected $type; 
    protected $magSize = 0; 
    protected $ammo = 0; 
    protected $inventoryField = '';   
    protected $inventoryUpdateFn = '';
    protected $soundShot;
    protected $soundEmpty;
    protected $soundDraw;
    protected $soundReload;
    protected $reloadDelay = 1000;
    protected $particleOffset = [0, 0];
    protected $jammed = false;
    protected $jamHandled = false;
    
    protected $reloading = false;     

    protected $view = null;
    protected $attachTimer = null;
    protected $offsetX = 0;
    protected $offsetY = 0;

    protected $shotPlayers = [];
    protected $shotPoolSize = 6;
    protected $shotIdx = 0;

    protected $prevTaskLabelText = null;
       
    public $dropShadowEffect;

    public function __construct($owner) { $this->owner = $owner; }

    abstract public function getType(): string;
    abstract protected function spritePath(): string;
    abstract protected function spriteOffsets(): array;
    abstract protected function hudMagImagePath(): string;

    public function attach(): void
    {
        [$this->offsetX, $this->offsetY] = $this->spriteOffsets();
        $path = $this->spritePath();

        $this->fxLater(function () use ($path) {
            $this->view = new UXImageView(new UXImage($path));
            $model = $this->owner->GetModel();
            $this->view->x = $model->x + $this->offsetX;
            $this->view->y = $model->y + $this->offsetY;
            (new ColorAdjustEffectBehaviour())->apply($this->view);
            
            $bm = 0.0;
            if ($model && $model->colorAdjustEffect)
            {
                $bm = $model->colorAdjustEffect->brightness;
            }
            if ($this->view->colorAdjustEffect)
            {
                $this->view->colorAdjustEffect->brightness = $bm;
            }
                        
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

            if (!empty($this->soundDraw) && !empty($GLOBALS['AllSounds']))
            {
                $this->owner->form('Client')->playSoundAsync($this->soundDraw, true, strtolower($this->type) . '_draw');
            }

            $this->startFollowTimer();

            $this->view->on('mouseDown', function () {
                $this->owner->Shoot();
            });

            $this->owner->UpdateMagazine();
        });
    }

    public function detach(): void
    {
        if ($this->attachTimer) { $this->attachTimer->stop(); $this->attachTimer = null; }
        $this->fxLater(function () {
            if ($this->view) {
                $this->owner->remove($this->view);
                $this->view = null;
                
                if (!empty($GLOBALS['AllSounds']))
                {
                    $this->owner->form('Client')->playSoundAsync('res://.data/audio/weapon/generic_close.mp3', true, 'generic_close');
                }                
            }
        });
    }
    
    public function shoot(): void
    {
        if ($this->reloading) { return; }
        if ($this->ammo <= 0) { $this->playEmpty(); return; }
    
        if ($this->ammo > 1 && $this->ammo < $this->magSize && rand(1, 600) === 1) { $this->jammed = true; }
    
        if ($this->jammed && !$this->jamHandled)
        {
            $this->jamHandled = true;
            if (!empty($GLOBALS['AllSounds'])) 
            {
                $this->owner->form('Client')->playSoundAsync($this->soundEmpty, true, strtolower($this->type) . '_jam');
            }
            $this->showJamHint();
            return;
        }
        if ($this->jammed) { $this->playEmpty(); return; }
    
        $this->ammo--;
        $this->owner->UpdateMagazine();
        $this->playShotOverlapped();
        $this->spawnMuzzleAndBlood();
    }
    
    public function reload(): void
    {
        if ($this->reloading) return;
        if ($this->ammo >= $this->magSize && !$this->jammed) return;
    
        $inv = $this->getInventoryContent();
        $totalAmmo = $inv->{$this->inventoryField};
        if ($totalAmmo <= 0 && !$this->jammed) return;
    
        if (!empty($this->soundReload) && !empty($GLOBALS['AllSounds']))
        {
            $this->owner->form('Client')->playSoundAsync($this->soundReload, true, strtolower($this->type) . '_reload');
        }
    
        $needed = max(0, $this->magSize - $this->ammo);
        $this->reloading = true;
    
        Timer::after($this->reloadDelay, function () use ($inv, $needed) {
            $this->fxLater(function () use ($inv, $needed) {
                $totalAmmo = $inv->{$this->inventoryField};
                if ($totalAmmo > 0)
                {
                    if ($totalAmmo < $needed) { $this->ammo += $totalAmmo; $totalAmmo = 0; }
                    else { $this->ammo += $needed; $totalAmmo -= $needed; }
                    $inv->{$this->inventoryField} = $totalAmmo;
                }
                if ($this->inventoryUpdateFn && method_exists($inv, $this->inventoryUpdateFn))
                {
                    $fn = $this->inventoryUpdateFn; $inv->$fn();
                }
                $this->owner->UpdateMagazine();
                $this->jammed = false; $this->jamHandled = false;
                $this->reloading = false;
            });
        });
    }

    public function isReloading(): bool { return $this->reloading; }

    protected function startFollowTimer(): void
    {
        $this->attachTimer = new UXAnimationTimer(function () {
    
            if (!$this->view) return;
    
            $m = $this->owner ? $this->owner->GetModel() : null;
            if (!$m) return;
    
            $this->view->x = $m->x + $this->offsetX;
            $this->view->y = $m->y + $this->offsetY;
        });
    
        $this->attachTimer->start();
    }

    protected $shotSeq = 0;
    protected $shotPoolSize = 6;
    
    protected function playShotOverlapped(): void
    {
        if (empty($GLOBALS['AllSounds'])) return;
    
        $base = strtolower($this->type) . '_shot';
        $tag = $base . '_' . $this->shotSeq;
        $this->shotSeq = ($this->shotSeq + 1) % $this->shotPoolSize;
    
        $this->owner->form('Client')->playSoundAsync($this->soundShot, true, $tag);
    }

    protected function playEmpty(): void
    {
        if (!empty($GLOBALS['AllSounds']))
        {
            $this->owner->form('Client')->playSoundAsync($this->soundEmpty, true, strtolower($this->type) . '_empty');
        }
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

    protected function getInventoryContent()
    {
        return $this->owner->form('Client')->Inventory->content->InventoryGrid->content;
    }

    public function getAmmo(): int { return $this->ammo; }
    public function getMagSize(): int { return $this->magSize; } 
    public function setAmmo(int $n): void { $this->ammo = max(0, min($this->magSize, $n)); }
    public function getTotalAmmoFromInventory(): int
    {
        $inv = $this->getInventoryContent();
        return $inv->{$this->inventoryField};
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
    public function importState(array $s): void {
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
    
    public function setBrightness(float $brightness): void
    {
        $this->fxLater(function () use ($brightness) {
            if ($this->view && $this->view->colorAdjustEffect)
            {
                $this->view->colorAdjustEffect->brightness = $brightness;
            }
        });
    }
    
    protected function fxLater(callable $fn): void { UXApplication::runLater($fn); }
}
