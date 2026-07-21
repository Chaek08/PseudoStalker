<?php
namespace app\forms\classes\Items;

use app\forms\classes\Items\CWeapon;

class CWeapon_AK74 extends CWeapon
{
    protected $gridWidth = 5;
    protected $gridHeight = 2;

    public function __construct($owner)
    {
        parent::__construct(
            $owner,

            'wpn_ak74',
            'AK74_Name',
            'AK74_Desc',

            5.2,
            1800,

            'res://.data/ui/weapons/wpn_ak74.png'
        );
        
        $this->type = 'wpn_ak74';
        $this->magSize = 30;
        $this->ammo = 30;
        $this->recoilPower = 10;
        $this->ammoItemId = 'ammo_5x45';
        $this->soundShot = 'res://.data/audio/weapon/ak74_shot_0.mp3';
        $this->soundEmpty = 'res://.data/audio/weapon/gen_empty.mp3';
        $this->soundDraw  = 'res://.data/audio/weapon/ak74_draw.mp3';
        $this->soundReload = 'res://.data/audio/weapon/ak74_reload.mp3';
        $this->reloadDelay = 1000;
        $this->particleOffset = [256, 96];
    }

    public function getType(): string { return 'wpn_ak74'; }
    protected function spritePath(): string { return 'res://.data/ui/weapons/wpn_ak74.png'; }
    protected function spriteOffsets(): array { return [24, 144]; }
    protected function hudMagImagePath(): string { return 'res://.data/ui/weapons/mag_5_45_hud.png'; }
}