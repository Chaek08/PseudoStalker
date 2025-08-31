<?php
namespace app\forms\classes;


class CActor 
{
    //private $Inventory;
    private $activeWeapon;
    private $model;
    
    public function __construct()
    {
            $pos= [0,0];
            //$this->activeWeapon = 0;
    }
    
    public function SetModel($mdl)
    {
        $this->model = $mdl;
    }
    
    public function GetModel()
    {
        return $this->model;
    }
    
    
    public function GetPosX() { return $this->pos[0]; }
    public function GetPosY() { return $this->pos[1]; }
    
    public function SetPos($x,$y) { $this->pos = [$x,$y]; }
    public function SetPosX($x) { $this->pos[0] = [$x]; }
    public function SetPosY($y) { $this->pos[0] = [$y]; }
    
    public function SetActiveWeapon($wpn) {  $this->activeWeapon = $wpn; }
    
    public function HasActiveWeapon() : bool { return $this->activeWeapon !== null; }
    
    public function GetActiveWeapon() {  return $this->activeWeapon; }
}