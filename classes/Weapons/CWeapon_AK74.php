<?php
namespace app\forms\classes\Weapons;
use app\forms\classes\Weapons\CWeapon;

class CWeapon_AK74 extends CWeapon
{
    public function __construct($owner)
    {
        parent::__construct($owner);
        $this->type = 'AK74';
        $this->magSize = 30;
        $this->ammo = 30;
        $this->inventoryField = 'akAmmoCount';
        $this->inventoryUpdateFn = 'updateAmmo5x45Count';
        $this->soundShot = 'res://.data/audio/weapon/ak74_shot_0.mp3';
        $this->soundEmpty = 'res://.data/audio/weapon/gen_empty.mp3';
        $this->soundDraw  = 'res://.data/audio/weapon/ak74_draw.mp3';
        $this->soundReload = 'res://.data/audio/weapon/ak74_reload.mp3';
        $this->reloadDelay = 1000;
        $this->particleOffset = [256, 96];
    }

    public function getType(): string { return 'AK74'; }
    protected function spritePath(): string { return 'res://.data/ui/weapons/wpn_ak74.png'; }
    protected function spriteOffsets(): array { return [24, 144]; }
    protected function hudMagImagePath(): string { return 'res://.data/ui/weapons/mag_5_45_hud.png'; }
}