<?php
namespace app\forms\classes\Items;

class CAmmo5x45 extends CAmmo
{
    protected $gridWidth = 2;
    protected $gridHeight = 1;

    public function __construct()
    {
        parent::__construct(
            'ammo_5x45',
            'Ammo5x45_Name',
            'Ammo5x45_Desc',
            0.6,
            200,
            'res://.data/ui/weapons/mag_5_45.png'
        );

        $this->caliber = '5x45';
    }
}