<?php
namespace app\forms\classes\UI;

use php\gui\UXImage;

class InventoryActions
{
    private $inventory;

    public function __construct($inventory)
    {
        $this->inventory = $inventory;
    }
    
    public function useItem()
    {
        $inv = $this->inventory;
    
        if (!$inv->selectedItem)
        {
            return;
        }
    
        $inv->UseSlotSound();
        $inv->HideCombobox();
    
        if ($inv->selectedItem === $inv->Inv_Medkit)
        {
            $this->applyMedkitEffect();
    
            $inv->medkitCount--;
    
            if ($inv->medkitCount < 1)
            {
                $inv->getGrid()->remove($inv->selectedItem);
    
                $inv->selectedItem->visible = false;
    
                $inv->repackInventory();
            }
        }
        else
        {
            return;
        }
    
        $inv->updateMedkitCount();
    
        $inv->UpdateInventoryStatus();
        $inv->UpdateSelectedItems();
        $inv->HideUIText();
    
        $inv->selectedItem = null;
    }
    
    public function dropItem()
    {
        $inv = $this->inventory;
    
        if (!$inv->selectedItem)
        {
            return;
        }
    
        $inv->DropSound();
    
        $inv->HideCombobox();
    
        $inv->getGrid()->remove($inv->selectedItem);
    
        $inv->selectedItem->visible = false;
    
        $inv->repackInventory();
    
        $inv->UpdateInventoryStatus();
        $inv->UpdateSelectedItems();
        $inv->HideUIText();
    
        $inv->form('Client')->MainGame->content->SpawnItem();
    
        $inv->selectedItem = null;
    }        
    
    public function takeOffItem(): void
    {
        $inv = $this->inventory;
        if (!$inv->selectedItem) return;
    
        $actor = $inv->form('Client')->MainGame->content->GameActor;
    
        $actor->takeOffOutfit();
        
        $inv->addOutfitToInventory();
    
        $inv->repackInventory();        
    
        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->DropSound();
    
        $inv->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;
    }

    public function putOnItem(): void
    {
        $inv = $this->inventory;
        if (!$inv->selectedItem) return;
    
        $actor = $inv->form('Client')->MainGame->content->GameActor;
    
        $inv->getGrid()->remove($inv->selectedItem);
        $inv->selectedItem->visible = false;
    
        $actor->putOnOutfit();
        
        $inv->repackInventory();        
    
        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->UseSlotSound();
    
        $inv->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;
    }
    
    public function applyMedkitEffect() //TODO: перенос в класс предметов
    {
        $actor = $this->inventory->form('Client')->MainGame->content->GameActor;
    
        if ($actor->isDead())
        {
            return;
        }
    
        $healPct = 20;
    
        $healAmount = (int)(($actor->getMaxHP() * $healPct) / 100);
    
        $actor->heal($healAmount);
    }
    
    
}