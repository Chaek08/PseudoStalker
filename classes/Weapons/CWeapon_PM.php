<?php 
namespace app\forms\classes\Weapons;
use app\forms\classes\Weapons\CWeapon;

class CWeapon_PM extends CWeapon
{
    public function __construct($owner)
    {
        parent::__construct($owner);
        $this->type = 'Pm';
        $this->magSize = 8;
        $this->ammo = 8;
        $this->inventoryField = 'pmAmmoCount';
        $this->inventoryUpdateFn = 'updateAmmo9x18Count';
        $this->soundShot = 'res://.data/audio/weapon/t_pm_shot.mp3';
        $this->soundEmpty = 'res://.data/audio/weapon/pistol_empty.mp3';
        $this->soundDraw  = 'res://.data/audio/weapon/pm_draw.mp3';
        $this->soundReload = 'res://.data/audio/weapon/pm_reload.mp3';
        $this->reloadDelay = 2000;
        $this->particleOffset = [158, 93];
    }

    public function getType(): string { return 'Pm'; }
    protected function spritePath(): string { return 'res://.data/ui/weapons/wpn_pm.png'; }
    protected function spriteOffsets(): array { return [112, 152]; }
    protected function hudMagImagePath(): string { return 'res://.data/ui/weapons/mag_9_18.png'; }
}