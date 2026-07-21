<?php
namespace app\forms\classes\Items;

class CAmmo extends CItem
{
    protected $caliber;

    public function getCaliber()
    {
        return $this->caliber;
    }
}