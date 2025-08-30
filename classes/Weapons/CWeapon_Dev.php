<?php
namespace app\forms\classes\Weapons;
use app\forms\classes\Weapons\CWeapon;
use app\forms\classes\CWeapon;

class CWeapon_Dev extends CWeapon
{

    public function __construct()
    {
        parent::__construct();
        
        $this->ammpProp = 'pmAmmo';
        $this->maxAmmo = 8;
        $this->soundShot = 'res://.data/audio/weapon/t_pm_shot.mp3';
        $this->soundEmpty = 'res://.data/audio/weapon/pistol_empty.mp3';
        $this->particleOffset = [158,93];
    }
}