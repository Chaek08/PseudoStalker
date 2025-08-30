<?php
namespace app\forms\classes;

class CActor 
{
    //private $Inventory;
    private $activeWeapon;
    
    public function __construct()
    {
            //$this->activeWeapon = 0;
    }
    
    public function SetActiveWeapon($wpn)
    {
        $this->activeWeapon = $wpn;
    }
    
    public function HasActiveWeapon() : bool
    {
        return $this->activeWeapon !== null;
    }
    
    public function GetActiveWeapon()
    {
        return $this->activeWeapon;
    }
}