<?php
namespace app\forms\classes\Weapons;

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
            $model = $this->owner->GameActor->GetModel();
            $this->view->x = $model->x + $this->offsetX;
            $this->view->y = $model->y + $this->offsetY;
            (new ColorAdjustEffectBehaviour())->apply($this->view);
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
        if ($this->attachTimer) { $this->attachTimer->cancel(); $this->attachTimer = null; }
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
    
        if ($this->ammo < $this->magSize && rand(1, 60) === 1) { $this->jammed = true; }
    
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
        $this->attachTimer = Timer::every(1, function () {
            $this->fxLater(function () {
                if ($this->view && $this->owner->GameActor && $this->owner->GameActor->GetModel())
                {
                    $m = $this->owner->GameActor->GetModel();
                    $this->view->x = $m->x + $this->offsetX;
                    $this->view->y = $m->y + $this->offsetY;
                    if ($this->view->colorAdjustEffect)
                    {
                        $this->view->colorAdjustEffect->brightness = $m->colorAdjustEffect->brightness;
                    }
                }
            });
        });
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
        [$ox, $oy] = $this->particleOffset;

        $this->owner->spawnParticleAsync(function () use ($ox, $oy) {
            $p = new UXImageView(new UXImage('res://.data/ui/particles/shoot.png'));
            $p->width = 128; $p->height = 128; $p->opacity = 1;
            $p->x = $this->owner->GameActor->GetModel()->x + $ox;
            $p->y = $this->owner->GameActor->GetModel()->y + $oy;
            (new \behaviour\custom\BloomEffectBehaviour())->apply($p);
            return $p;
        }, function ($p) {
            Animation::fadeOut($p, 150, function () use ($p) {
                if ($p->parent) { $p->parent->remove($p); }
                $p->free();
            });
        });

        $actor = $this->owner->GameActor->GetModel();
        $enemy = $this->owner->GameEnemy->GetModel();

        if (!$enemy || !$actor || !$enemy->visible)
        {
            return;
        }

        if ($actor->x > $enemy->x)
        {
            return;
        }
        
        if ($enemy->visible)
        {
            $this->owner->DamageEnemy(null, false);
            $bloodCount = rand(4, 7);
            foreach (range(1, $bloodCount) as $_) {
                $scatterX = rand(-35, 35);
                $scatterY = rand(-35, 35);
                $this->owner->spawnParticleAsync(function () use ($enemy, $scatterX, $scatterY, $oy) {
                    $b = new UXImageView(new UXImage('res://.data/ui/particles/blood.png'));
                    $b->scale = $this->owner->form('Client')->MainGame->scale;
                    $b->width = 86; $b->height = 86; $b->opacity = 1.0;
                    $hitX = $enemy->x + ($enemy->width / 2) - ($b->width / 2);
                    $hitY = $this->owner->GameActor->GetModel()->y + $oy;
                    $b->x = $hitX + $scatterX;
                    $b->y = $hitY + $scatterY;
                    return $b;
                }, function ($b) {
                    Animation::fadeOut($b, 400, function () use ($b) { $b->free(); });
                });
            }
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
    
    protected function fxLater(callable $fn): void { UXApplication::runLater($fn); }
}
