<?php
namespace app\forms\classes\Weapons;

class CWeapon 
{


    protected $ammpProp;
    protected $maxAmmo;
    protected $soundShot;
    protected $soundEmpty;
    protected $particleOffset;
    protected $jammed;
    protected $jamHandled;

    protected $isReloading;
    protected $image;

    public function __construct()
    {
            $this->jamHandled = false;
            $this->jammed     = false;
    }

    public function IsJammed() : bool  { return $this->jammed; }
    
    public function IsJamHandled() : bool  { return $this->jamHandled;}


}