<?php
namespace app\forms\classes\Items;

use app\forms\classes\Localization;

class COutfit extends CItem
{
    protected $gridWidth = 2;
    protected $gridHeight = 2;

    public function __construct()
    {
        parent::__construct(
            'outfit',
            'Outfit_Inv_Name',
            'Outfit_Inv_Desc',
            2.0,
            2599,
            'res://.data/ui/inventory/bandit_outfit.png'
        );
    }
}