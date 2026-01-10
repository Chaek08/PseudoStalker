<?php
namespace app\forms\classes;

use app\forms\classes\CSoundIndicator;
use behaviour\custom\DraggingBehaviour;

class CEnemy 
{
    protected $form;
    private $dragging 
    //private $Inventory;
    //private $activeWeapon;
    private $model;
    
    private $canInteractive;
    
    protected $soundIndicator;    
    
    public function __construct($form)
    {
        $this->form = $form;
        $this->dragging = new DraggingBehaviour();
        
        $this->dragging->setProperties([
            "direction"=> "LEFT_RIGHT",
            "limitedByParent" => true,
        ]);
    }

    public function CanInteractive() {return $this->canInteractive;}
    
    public function SetInteractive($b)
    { 
        $this->canInteractive = $b; 
        $this->dragging->enabled = $b;      
    }
    
    public function SetModel($mdl) 
    { 
        $this->model = $mdl; 
        $this->dragging->apply($this->model);
        $this->soundIndicator = new CSoundIndicator($this->model);
    }
    public function GetModel() { return $this->model; }
    
    public function playSound(string $path, int $durationMs): void
    {
        if (!$this->model || !$this->model->visible) return;
    
        if ($this->soundIndicator)
        {
            $this->soundIndicator->playFor($durationMs);
        }
    
        uiLater(function () use ($path) {
            if ($this->model && $this->model->visible)
            {
                $this->form->form('Client')->playSoundAsync($path, true, 'entity_voice');
            }
        });
    }
    
    //public function SetActiveWeapon($wpn) {  $this->activeWeapon = $wpn; }
    //public function HasActiveWeapon() : bool { return $this->activeWeapon !== null; }
    //public function GetActiveWeapon() {  return $this->activeWeapon; }
}