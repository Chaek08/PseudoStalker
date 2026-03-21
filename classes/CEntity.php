<?php
namespace app\forms\classes;

use php\time\Timer;
use app\forms\classes\CSoundIndicator;
use behaviour\custom\DraggingBehaviour;

abstract class CEntity
{
    protected $form;
    protected $model;
    protected $dragging;
    protected $soundIndicator;

    protected $canInteractive = false;
    protected $GodMode = false;    
    protected $isDead = false;

    protected $maxHP = 100;
    protected $hp = 100;

    protected $onHpChanged = [];
    protected $onDeath = [];
    
    protected $regenTimer = null;
    protected $regenAmount = 5;
    protected $regenIntervalMs = 15000;
    
    public function __construct($form, int $maxHP = 100)
    {
        $this->form  = $form;
        $this->maxHP = $maxHP;
        $this->hp    = $maxHP;

        $this->dragging = new DraggingBehaviour();
        $this->dragging->setProperties([
            'direction' => 'LEFT_RIGHT',
            'limitedByParent' => true,
        ]);
    }
    
    public function SetGodMode(bool $state): void
    {
        $this->GodMode = $state;
    }
    
    public function isGodMode(): bool
    {
        return $this->GodMode;
    }    

    public function isDead(): bool
    {
        return $this->isDead;
    }

    public function CanInteractive(): bool
    {
        return !$this->isDead && $this->canInteractive;
    }

    public function SetInteractive(bool $b): void
    {
        if ($this->isDead) return;

        $this->canInteractive = $b;
        //$this->dragging->enabled = $b;
    }

    public function SetModel($mdl): void
    {
        $this->model = $mdl;
        $this->dragging->apply($this->model);
        $this->initSoundIndicator();
    }

    public function GetModel()
    {
        return $this->model;
    }

    protected function initSoundIndicator(): void
    {
        if ($this->model)
        {
            $this->soundIndicator = new CSoundIndicator($this->model);
        }
    }

    public function getHP(): int
    {
        return $this->hp;
    }

    public function getMaxHP(): int
    {
        return $this->maxHP;
    }

    public function getHpPercent(): int
    {
        if ($this->maxHP <= 0) return 0;
        return (int) round(($this->hp / $this->maxHP) * 100);
    }

    public function applyDamage(int $amount): void
    {
        if ($this->isDead || $amount <= 0) return;
        
        if ($this->GodMode) return;

        $this->hp -= $amount;

        if ($this->hp <= 0)
        {
            $this->hp = 0;
            $this->fireHpChanged();
            $this->death();
            return;
        }
        
        $this->startRegen();        

        $this->fireHpChanged();
    }

    public function heal(int $amount): void
    {
        if ($this->isDead || $amount <= 0) return;

        $this->hp = min($this->maxHP, $this->hp + $amount);
        $this->fireHpChanged();
    }

    public function death(): void
    {
        if ($this->isDead) return;

        $this->isDead = true;
        $this->canInteractive = false;
        //$this->dragging->enabled = false;

        if ($this->soundIndicator)
        {
            $this->soundIndicator->destroy();
            $this->soundIndicator = null;
        }

        if ($this->model)
        {
            $this->model->hide();
        }

        $this->fireDeath();
    }

    public function revive(): void
    {
        if (!$this->isDead) return;

        $this->isDead = false;
        $this->hp = $this->maxHP;

        if ($this->model)
        {
            $this->model->show();
        }

        $this->canInteractive = true;
        //$this->dragging->enabled = true;

        $this->initSoundIndicator();
        $this->fireHpChanged();
    }

    public function respawn(float $x, float $y, bool $interactive = false): void
    {
        if ($this->isDead)
        {
            $this->revive();
        }
        else
        {
            $this->hp = $this->maxHP;
            $this->fireHpChanged();
    
            if ($this->model)
            {
                $this->model->show();
            }
        }
    
        if (!$this->model) return;
    
        $this->model->x = $x;
        $this->model->y = $y;
        $this->SetInteractive($interactive);
    }

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

    public function onHpChanged(callable $cb): void
    {
        $this->onHpChanged[] = $cb;
    }

    public function onDeath(callable $cb): void
    {
        $this->onDeath[] = $cb;
    }

    protected function fireHpChanged(): void
    {
        foreach ($this->onHpChanged as $cb)
        {
            $cb($this);
        }
    }

    protected function fireDeath(): void
    {
        foreach ($this->onDeath as $cb)
        {
            $cb($this);
        }
    }
    
    public function setHp(int $value): void
    {
        $value = max(0, min($this->maxHP, $value));
        
        if ($this->GodMode) return;
    
        if ($this->isDead)
        {
            $this->hp = $value;
            return;
        }
    
        $this->hp = $value;
        $this->fireHpChanged();
    
        if ($this->hp <= 0)
        {
            $this->death();
        }
    }

    protected function startRegen(): void
    {
        if ($this->isDead) return;
    
        if ($this->regenTimer)
        {
            $this->regenTimer->cancel();
            $this->regenTimer = null;
        }
    
        $this->regenTimer = Timer::every($this->regenIntervalMs, function () {
            if ($this->isDead)
            {
                $this->stopRegen();
                return;
            }
    
            if ($this->hp >= $this->maxHP)
            {
                $this->stopRegen();
                return;
            }
    
            $this->heal($this->regenAmount);
        });
    }
    
    protected function stopRegen(): void
    {
        if ($this->regenTimer)
        {
            $this->regenTimer->cancel();
            $this->regenTimer = null;
        }
    }
    
}
