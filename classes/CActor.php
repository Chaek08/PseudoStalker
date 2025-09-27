<?php
namespace app\forms\classes;

use behaviour\custom\DraggingBehaviour;


class CActor 
{
    private $dragging 
    //private $Inventory;
    private $activeWeapon;
    private $model;
    
    private $canInteractive;
    
    public function __construct()
    {
        $this->dragging = new DraggingBehaviour();
        
        $this->dragging->setProperties([
            "direction"=> "LEFT_RIGHT",
            "limitedByParent" => true,
        ]);
    }

    public function CanInteractive() {return $this->canInteractive;}
    
    public function SetInteractive($b) { 
            $this->canInteractive = $b; 
            $this->dragging->enabled = $b;      
    }
    
    public function SetModel($mdl) 
    { 
        $this->model = $mdl; 
        $this->dragging->apply($this->model);
    }
    public function GetModel() { return $this->model; }
    
    public function SetActiveWeapon($wpn) {  $this->activeWeapon = $wpn; }
    public function HasActiveWeapon() : bool { return $this->activeWeapon !== null; }
    public function GetActiveWeapon() {  return $this->activeWeapon; }
}