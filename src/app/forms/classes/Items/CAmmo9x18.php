<?php
namespace app\forms\classes\Items;

class CAmmo9x18 extends CAmmo
{
    protected $gridWidth = 1;
    protected $gridHeight = 1;

    public function __construct()
    {
        parent::__construct(
            'ammo_9x18',
            'Ammo9x18_Name',
            'Ammo9x18_Desc',
            0.7,
            90,
            'res://.data/ui/weapons/mag_9_18.png'
        );

        $this->caliber = '9x18';
    }
}