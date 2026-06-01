<?php
namespace app\forms\classes;

use php\gui\UXImageView;
use php\gui\UXImage;

use app\forms\classes\Weapons\CWeapon;
use app\forms\classes\Weapons\CWeaponFactory;
use app\forms\classes\Debug;

class CActor extends CEntity
{
    protected $game;
    
    public const MODEL_OUTFIT_ON  = 'res://.data/ui/maingame/sprite/actor.png';
    public const MODEL_OUTFIT_OFF = 'res://.data/ui/maingame/sprite/noout/actor.png';    

    protected $currentWeapon = null;
    protected $currentWeaponIndex = 0;    
    protected $weaponState = [];
    protected $weapons = ['Pm', 'AK74'];
    
    protected $lastWeaponSwitch = 0;
    protected $weaponSwitchDelay = 0.48;

    protected $wearingOutfit = true;

    public function __construct($game, int $maxHP = 100)
    {
        parent::__construct($game, $maxHP);
        
        $this->game = $game;
        
        $this->setWeight(50.0);
        Log::info('Base actor weight: ' . $this->getWeight());
    }

    public function getGame()
    {
        return $this->game;
    }

    public function form(string $name)
    {
        return $this->game->form($name);
    }

    public function add($node): void
    {
        $this->game->add($node);
    }

    public function remove($node): void
    {
        $this->game->remove($node);
    }

    public function getEnemy()
    {
        return $this->game->GameEnemy;
    }

    public function getParticles()
    {
        return $this->game->Particles;
    }

    public function DamageEnemy($e = null, bool $spawnParticles = true, bool $damageByMouse = true): void
    {
        $this->game->DamageEnemy($e, $spawnParticles, $damageByMouse);
    }

    public function showJamHintUI(string $textKey): void
    {
        $this->game->showJamHintUI($textKey);
    }

    public function UpdateMagazine(): void
    {
        $this->game->UpdateMagazine();
    }

    public function getWeapon(): ?CWeapon
    {
        return $this->currentWeapon;
    }
    
    public function isWearingOutfit(): bool
    {
        return $this->wearingOutfit;
    }    
    
    public function putOnOutfit(): void
    {
        if ($this->wearingOutfit) return;
    
        $this->wearingOutfit = true;
    
        $inv = $this->form('Client')->Inventory->content;
    
        $inv->inv_maket_visual->image = new UXImage(self::MODEL_OUTFIT_ON);
        $this->form('Client')->MainGame->content->actor->image = new UXImage(self::MODEL_OUTFIT_ON);
    }
    
    public function takeOffOutfit(): void
    {
        if (!$this->wearingOutfit) return;
    
        $this->wearingOutfit = false;
    
        $model = 'res://.data/ui/maingame/sprite/noout/actor.png';
    
        $inv = $this->form('Client')->Inventory->content;
    
        $inv->inv_maket_visual->image = new UXImage(self::MODEL_OUTFIT_OFF);
        $this->form('Client')->MainGame->content->actor->image = new UXImage(self::MODEL_OUTFIT_OFF);
    }   

    public function UnequipCurrentWeapon(): void
    {
        if ($this->currentWeapon)
        {
            $this->weaponState[$this->currentWeapon->getType()] = $this->currentWeapon->exportState();
            $this->currentWeapon->detach();
            $this->currentWeapon = null;
            $this->UpdateMagazine();
        }
    }

    public function SwitchWeapon(?string $weaponType): void
    {
        if (!$this->GetModel() || !$this->GetModel()->visible) return;

        if ($weaponType === null)
        {
            $this->UnequipCurrentWeapon();
            return;
        }

        if ($this->currentWeapon && $this->currentWeapon->getType() === $weaponType) return;

        $inv = $this->form('Client')->Inventory->content;
    
        $slot = $inv->getWeaponSlot($weaponType);
    
        if (!$slot || !$slot['equipped']) return;

        if ($this->currentWeapon) $this->UnequipCurrentWeapon();

        $weapon = CWeaponFactory::create($weaponType, $this);
        if (!$weapon)
        {
            Debug::fail("Weapon '$weaponType' not created", __FILE__, __LINE__);
            return;
        }

        if (isset($this->weaponState[$weaponType]))
        {
            $weapon->importState($this->weaponState[$weaponType]);
        }

        $weapon->attach();
        $this->currentWeapon = $weapon;
        
        $weapon->setUnlimitedAmmo($GLOBALS['UnlimitedAmmoFlag']);

        if (isset($GLOBALS['ShadowsSwitcher_IsOn']) && !$GLOBALS['ShadowsSwitcher_IsOn'])
        {
            $this->currentWeapon->disableShadow();
        }
        else
        {
            $this->currentWeapon->enableShadow();
        }

        $this->UpdateMagazine();
    }
    
    protected function hasWeapon(string $weaponType): bool
    {
        $inv = $this->form('Client')->Inventory->content;
    
        $slot = $inv->getWeaponSlot($weaponType);
    
        return $slot ? !empty($slot['equipped']) : false;
    }  
    
    public function setWeaponIndex(int $index): void
    {
        if (!isset($this->weapons[$index])) return;
    
        $this->currentWeaponIndex = $index;
        $this->applyWeaponByIndex();
    }    
    
    public function applyWeaponByIndex(): void
    {
        $weaponType = $this->weapons[$this->currentWeaponIndex] ?? null;
        $this->SwitchWeapon($weaponType);
    }    
    
    public function canSwitchWeapon(): bool
    {
        $now = microtime(true);
        
        if (($now - $this->lastWeaponSwitch) < $this->weaponSwitchDelay)
            return false;
        
        $this->lastWeaponSwitch = $now;
        return true;
    }    
    
    public function switchNextWeapon(): void
    {
        if ($this->currentWeapon && $this->currentWeapon->isReloading()) return;
        
        if (!$this->canSwitchWeapon()) return;
    
        $startIndex = $this->currentWeaponIndex;
    
        do {
            $this->currentWeaponIndex++;
    
            if ($this->currentWeaponIndex >= count($this->weapons))
            {
                $this->currentWeaponIndex = 0;
            }
    
            $weaponType = $this->weapons[$this->currentWeaponIndex];
    
            if ($this->hasWeapon($weaponType))
            {
                $this->applyWeaponByIndex();
                return;
            }
    
        } while ($this->currentWeaponIndex !== $startIndex);
    }
    
    public function switchPrevWeapon(): void
    {
        if ($this->currentWeapon && $this->currentWeapon->isReloading()) return;
        
        if (!$this->canSwitchWeapon()) return;
    
        $startIndex = $this->currentWeaponIndex;
    
        do {
            $this->currentWeaponIndex--;
    
            if ($this->currentWeaponIndex < 0)
            {
                $this->currentWeaponIndex = count($this->weapons) - 1;
            }
    
            $weaponType = $this->weapons[$this->currentWeaponIndex];
    
            if ($this->hasWeapon($weaponType))
            {
                $this->applyWeaponByIndex();
                return;
            }
    
        } while ($this->currentWeaponIndex !== $startIndex);
    }  

    public function Shoot(): void
    {
        if (empty($GLOBALS['QuestStep1'])) return;
        if (!$this->currentWeapon) return;

        $this->currentWeapon->shoot();
    }

    public function ReloadWeapon(): void
    {
        if (!$this->currentWeapon) return;
        $this->currentWeapon->reload();
    }
}
