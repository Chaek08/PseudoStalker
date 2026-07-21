<?php 
namespace app\forms\classes\Items;

use app\forms\classes\Items\CWeapon;

class CWeapon_PM extends CWeapon
{
    protected $gridWidth = 1;
    protected $gridHeight = 1;

    public function __construct($owner)
    {
        parent::__construct(
            $owner,

            'wpn_pm',
            'PM_Name',
            'PM_Desc',

            0.7,
            280,

            'res://.data/ui/weapons/wpn_pm.png'
        );
        
        $this->type = 'wpn_pm';
        $this->magSize = 8;
        $this->ammo = 8;
        $this->recoilPower = 4;        
        $this->ammoItemId = 'ammo_9x18';
        $this->soundShot = 'res://.data/audio/weapon/t_pm_shot.mp3';
        $this->soundEmpty = 'res://.data/audio/weapon/pistol_empty.mp3';
        $this->soundDraw  = 'res://.data/audio/weapon/pm_draw.mp3';
        $this->soundReload = 'res://.data/audio/weapon/pm_reload.mp3';
        $this->reloadDelay = 2000;
        $this->particleOffset = [158, 93];
    }

    public function getType(): string { return 'wpn_pm'; }
    protected function spritePath(): string { return 'res://.data/ui/weapons/wpn_pm.png'; }
    protected function spriteOffsets(): array { return [112, 152]; }
    protected function hudMagImagePath(): string { return 'res://.data/ui/weapons/mag_9_18.png'; }
}