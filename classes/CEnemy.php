<?php
namespace app\forms\classes;

use behaviour\custom\DraggingBehaviour;
use app\forms\classes\CSoundIndicator;

class CEnemy 
{
    protected $form;
    private $dragging;
    //private $activeWeapon;
    private $model;

    private $canInteractive;
    private $isDead = false;

    protected $soundIndicator;

    public function __construct($form)
    {
        $this->form = $form;
        $this->dragging = new DraggingBehaviour();

        $this->dragging->setProperties([
            "direction" => "LEFT_RIGHT",
            "limitedByParent" => true,
        ]);
    }

    public function isDead(): bool
    {
        return $this->isDead;
    }

    public function CanInteractive()
    {
        return !$this->isDead && $this->canInteractive;
    }

    public function SetInteractive($b)
    {
        if ($this->isDead) return;

        $this->canInteractive = $b;
        $this->dragging->enabled = $b;
    }

    public function SetModel($mdl)
    {
        if ($this->isDead) return;

        $this->model = $mdl;
        $this->dragging->apply($this->model);

        $this->soundIndicator = new CSoundIndicator($this->model);
    }

    public function GetModel()
    {
        return $this->model;
    }
    
//СВЯЗАЬБ С КЛАССОМ CWEAPOMN

//    public function SetActiveWeapon($wpn)
//    {
 //       if ($this->isDead) return;
//
 //       $this->activeWeapon = $wpn;
//    }

  // function HasActiveWeapon(): bool
  //  {
 //       return !$this->isDead && $this->activeWeapon !== null;
  //  }

  //  public function GetActiveWeapon()
  //  {
  //      return $this->activeWeapon;
  //  }

    public function playSound(string $path, int $durationMs): void
    {
        if ($this->isDead || !$this->model) return;

        if ($this->soundIndicator)
        {
            $this->soundIndicator->playFor($durationMs);
        }

        uiLater(function () use ($path) {
            if ($this->isDead || !$this->model) return;

            $this->form->form('Client')->playSoundAsync($path, true, 'entity_voice');
        });
    }

    public function death(): void
    {
        if ($this->isDead) return;

        $this->isDead = true;

        $this->canInteractive = false;
        $this->dragging->enabled = false;

        if ($this->soundIndicator)
        {
            $this->soundIndicator->destroy();
            $this->soundIndicator = null;
        }

       // $this->activeWeapon = null;

        if ($this->model)
        {
            $this->model->hide();
        }
    }
    
    public function revive(): void
    {
        if (!$this->isDead) return;
    
        $this->isDead = false;
    
        if ($this->model)
        {
            $this->model->show();
        }
    
        $this->canInteractive = true;
        $this->dragging->enabled = true;
    
        if ($this->model)
        {
            $this->soundIndicator = new CSoundIndicator($this->model);
        }
    }    
    
    public function respawn(float $x, float $y, bool $interactive = false): void
    {
        if ($this->isDead)
        {
            $this->revive();
        }
    
        if (!$this->model) return;
    
        $this->model->show();
    
        $this->model->x = $x;
        $this->model->y = $y;
    
        $this->SetInteractive($interactive);
    }    
}
