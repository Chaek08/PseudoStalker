<?php
namespace app\forms\classes\Items;

use app\forms\classes\Localization;

class CMedkit extends CConsumable
{
    protected $gridWidth = 2;
    protected $gridHeight = 1;

    public function __construct()
    {
        parent::__construct(
            'medkit',
            'Medkit_Inv_Name',
            'Medkit_Inv_Desc',
            0.1,
            100,
            'res://.data/ui/inventory/item_medkit.png'
        );
    }
}