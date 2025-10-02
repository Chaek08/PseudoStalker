<?php
namespace app\forms;

use php\gui\UXApplication;
use php\time\Timer;
use php\gui\UXImage;
use behaviour\custom\LightingEffectBehaviour;
use php\gui\animation\UXAnimationTimer;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 

class InventoryGrid extends AbstractForm
{
    var $grid;
    
    private $inventoryItems = [];    

    var $draggedItem = null;
    var $draggedItemOriginalPos = null;
    
    public $selectedItem = null;
    
    public $medkitCount = 0;
    public $pmAmmoCount = 25; //default
    public $akAmmoCount = 60; //default
    
    public function __construct()
    {
        parent::__construct();
    
        $this->grid = [];

        for ($x = 0; $x < 11; $x++)
        {
            for ($y = 0; $y < 16; $y++)
            {
                $this->grid[$x][$y] = null;
            }
        }
        
        $this->inventoryItems = [
            $this->Inv_Vodka,
            $this->Inv_Medkit,
            $this->Inv_Outfit,
            $this->Inv_Wpn_Pm,
            $this->Inv_Ammo_9x18,
            $this->Inv_Wpn_AK74,
            $this->Inv_Ammo_5x45
        ];        

        $this->addVodkaToInventory();
        $this->addMedkitToInventory();
        $this->addAmmo9x18ToInventory();
        $this->addAmmo5x45ToInventory();
    }
    /**
     * @event mouseMove
     */
    function GridMouseMove(UXMouseEvent $e = null)
    {
        if ($this->draggedItem == null || $this->inventoryLocked) return;

        $offsetX = $this->draggedItem->width / 2;
        $offsetY = $this->draggedItem->height / 2;

        $mouseX = $e->sceneX;
        $mouseY = $e->sceneY;

        $newX = $mouseX - $offsetX;
        $newY = $mouseY - $offsetY;

        $gridLeft = 0;
        $gridTop = 120;
        $gridRight = 552;
        $gridBottom = 784 + 120;

        $maxX = $gridRight - $this->draggedItem->width;
        $maxY = $gridBottom - $this->draggedItem->height;

        $clampedX = max($gridLeft, min($newX, $maxX));
        $clampedY = max($gridTop, min($newY, $maxY));

        $this->draggedItem->position = [$clampedX, $clampedY];
    }
    /**
     * @event mouseUp-Left
     */
    function GridMouseUp(UXMouseEvent $e = null)
    {  
        if ($this->draggedItem == null || $this->inventoryLocked) return;

        $cellSize = 49;
        $gridLeft = 0;
        $gridTop = 120;

        $mouseX = $e->x;
        $mouseY = $e->y;

        if ($mouseX < 0 || $mouseY < $gridTop || $mouseX >= 552 || $mouseY >= $gridTop + 784)
        {
            $this->draggedItem->position = $this->draggedItemOriginalPos;
            $this->draggedItem = null;
            return;
        }

        $cellX = floor(($mouseX - $gridLeft) / $cellSize);
        $cellY = floor(($mouseY - $gridTop) / $cellSize);

        $itemWidthCells = ceil($this->draggedItem->width / $cellSize);
        $itemHeightCells = ceil($this->draggedItem->height / $cellSize);

        $this->removeItemFromGrid($this->draggedItem);

        if ($this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells))
        {
            $this->placeItem($this->draggedItem, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
            $this->form('Client')->Inventory->content->UpdateComboboxPosition();
        }
        else
        {
            $this->draggedItem->position = $this->draggedItemOriginalPos;

            $originalX = floor(($this->draggedItemOriginalPos[0] - $gridLeft) / $cellSize);
            $originalY = floor(($this->draggedItemOriginalPos[1] - $gridTop) / $cellSize);

            $this->placeItem($this->draggedItem, $originalX, $originalY, $itemWidthCells, $itemHeightCells);
        }

        $this->draggedItem = null;
    }
    function addVodkaToInventory()
    {
        $item = $this->Inv_Vodka;
        $itemWidthCells = 1;
        $itemHeightCells = 2;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }    
    function addMedkitToInventory()
    {
        $item = $this->Inv_Medkit;
        $itemWidthCells = 2;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;
        
        $this->medkitCount += 2;
        $this->updateMedkitCount();

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }
    function addAmmo9x18ToInventory()
    {
        $item = $this->Inv_Ammo_9x18;
        $itemWidthCells = 2;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;
        
        $this->updateAmmo9x18Count();

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }
    function addAmmo5x45ToInventory()
    {
        $item = $this->Inv_Ammo_5x45;
        $itemWidthCells = 1;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;
        
        $this->updateAmmo5x45Count();

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }
    function addOutfitToInventory()
    {
        $item = $this->Inv_Outfit;
        $itemWidthCells = 2;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }
    function addPmToInventory()
    {
        $item = $this->Inv_Wpn_Pm;
        $itemWidthCells = 1;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }
    function addAk74ToInventory()
    {
        $item = $this->Inv_Wpn_AK74;
        $itemWidthCells = 1;
        $itemHeightCells = 1;
        
        $slot = $this->findFreeSlot($itemWidthCells, $itemHeightCells);
        list($cellX, $cellY) = $slot;

        if (!$this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells)) return;

        $this->placeItem($item, $cellX, $cellY, $itemWidthCells, $itemHeightCells);
        
        $this->repackInventory();
    }    
    function updateMedkitCount()
    {    
        $posX = $this->Inv_Medkit->x;
        $posY = $this->Inv_Medkit->y;

        $this->Inv_Medkit_Count->x = $posX;
        $this->Inv_Medkit_Count->y = $posY;

        if ($this->medkitCount >= 2)
        {
            $this->Inv_Medkit_Count->text = 'x' . (string)$this->medkitCount;
            $this->Inv_Medkit_Count->visible = true;
        }
        else
        {
            $this->Inv_Medkit_Count->visible = false;
        }
        
        if ($this->medkitCount < 1)
        {
            $this->removeItemFromGrid($this->Inv_Medkit);
            $this->Inv_Medkit->visible = false;
        }        
    }
    function updateAmmo9x18Count()
    {    
        $posX = $this->Inv_Ammo_9x18->x;
        $posY = $this->Inv_Ammo_9x18->y;

        $this->Inv_PmAmmo_Count->x = $posX;
        $this->Inv_PmAmmo_Count->y = $posY;

        if ($this->pmAmmoCount >= 2)
        {
            $this->Inv_PmAmmo_Count->text = 'x' . (string)$this->pmAmmoCount;
            $this->Inv_PmAmmo_Count->visible = true;
        }
        else
        {
            $this->Inv_PmAmmo_Count->visible = false;
        }
        
        if ($this->pmAmmoCount < 1)
        {
            $this->removeItemFromGrid($this->Inv_Ammo_9x18);
            $this->Inv_Ammo_9x18->visible = false;
        }        
    }
    function updateAmmo5x45Count()
    {    
        $posX = $this->Inv_Ammo_5x45->x;
        $posY = $this->Inv_Ammo_5x45->y;

        $this->Inv_AkAmmo_Count->x = $posX;
        $this->Inv_AkAmmo_Count->y = $posY;

        if ($this->akAmmoCount >= 2)
        {
            $this->Inv_AkAmmo_Count->text = 'x' . (string)$this->akAmmoCount;
            $this->Inv_AkAmmo_Count->visible = true;
        }
        else
        {
            $this->Inv_AkAmmo_Count->visible = false;
        }
        
        if ($this->akAmmoCount < 1)
        {
            $this->removeItemFromGrid($this->Inv_Ammo_5x45);
            $this->Inv_Ammo_5x45->visible = false;
        }        
    }
    function canPlace($cellX, $cellY, $w, $h): bool
    {
        if ($cellX + $w > 11 || $cellY + $h > 16) return false;

        for ($x = 0; $x < $w; $x++)
        {
            for ($y = 0; $y < $h; $y++)
            {
                if ($this->grid[$cellX + $x][$cellY + $y] != null) return false;
            }
        }

        return true;
    }
    function placeItem($item, $cellX, $cellY, $w, $h)
    {
        for ($x = 0; $x < $w; $x++)
        {
            for ($y = 0; $y < $h; $y++)
            {
                $this->grid[$cellX + $x][$cellY + $y] = $item;
            }
        }

        $cellSize = 49;
        $gridX = $cellX * $cellSize;
        $gridY = $cellY * $cellSize + 120;

        $itemW = $item->width;
        $itemH = $item->height;

        $posX = $gridX + ($cellSize * $w - $itemW) / 2;
        $posY = $gridY + ($cellSize * $h - $itemH) / 2;

        $item->position = [$posX, $posY];
        $item->visible = true;
        
        $this->updateMedkitCount();
        $this->updateAmmo9x18Count();
        $this->updateAmmo5x45Count();
    }
    function removeItemFromGrid($item)
    {
        for ($x = 0; $x < 11; $x++)
        {
            for ($y = 0; $y < 16; $y++)
            {
                if ($this->grid[$x][$y] === $item)
                {
                    $this->grid[$x][$y] = null;
                }
            }
        }
    }
    function findFreeSlot($w, $h)
    {
        for ($y = 0; $y < 16; $y++)
        {
            for ($x = 0; $x < 11; $x++)
            {
                if ($this->canPlace($x, $y, $w, $h))
                {
                    return [$x, $y];
                }
            }
        }
        return null;
    }   
    function repackInventory()
    {
        $visibleItems = [];
        foreach ($this->inventoryItems as $item)
        {
            if (!$item->visible) continue;

            if ($item == $this->Inv_Wpn_Pm && $this->pmInWeaponSlot) continue;
            
            if ($item == $this->Inv_Wpn_AK74 && $this->AK74InWeaponSlot) continue;

            $visibleItems[] = $item;
        }

        foreach ($visibleItems as $item)
        {
            $this->removeItemFromGrid($item);
        }

        foreach ($visibleItems as $item)
        {
            $w = ceil($item->width / 49);
            $h = ceil($item->height / 49);

            $slot = $this->findFreeSlot($w, $h);

            if ($slot != null)
            {
                list($x, $y) = $slot;
                $this->placeItem($item, $x, $y, $w, $h);
            }
            else
            {
                $item->visible = false;
            }    
        }    
    }
    private $inventoryLocked = false;

    function lockInventory(bool $locked)
    {
        $this->inventoryLocked = $locked;

        $item = [
            'medkit' => $this->Inv_Medkit,
            'medkitCount' => $this->Inv_Medkit_Count,
            'vodka' => $this->Inv_Vodka,
            'outfit' => $this->Inv_Outfit,
            'pm' => $this->Inv_Wpn_Pm,
            'ammo_9x18' => $this->Inv_Ammo_9x18,
            'ammo_9x18Count' => $this->Inv_PmAmmo_Count,
            'ak74' => $this->Inv_Wpn_AK74,
            'ammo_5x45' => $this->Inv_Ammo_5x45,
            'ammo_5x45Count' => $this->Inv_AkAmmo_Count,
        ];

        foreach ($item as $key => $obj)
        {
            if ($obj && $obj->visible)
            {
                $obj->enabled = !$locked;

                if ($locked)
                {
                    $obj->colorAdjustEffect->brightness = -0.4;
                }
                else
                {
                    $obj->colorAdjustEffect->brightness = 0.0;
                }
            }
        }
    }     
    /**
     * @event Inv_Vodka.mouseDown-Left 
     */
    function VodkaMouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
    
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
    }
    /**
     * @event Inv_Medkit.mouseDown-Left 
     */
    function MedkitMouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
    
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
        
        $this->Inv_Medkit_Count->toFront();
    }
    /**
     * @event Inv_Outfit.mouseDown-Left 
     */
    function OutfitMouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
    
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
    }
    /**
     * @event Inv_Wpn_Pm.mouseDown-Left 
     */
    function PmMouseDown(UXMouseEvent $e = null)
    {    
        if ($this->inventoryLocked || $this->pmInWeaponSlot) return; //искусственное ограничение, пм можно перетащить из wpn слота в обычный, но наоборот такая схема не работает
    
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();        
    }
    /**
     * @event Inv_Wpn_AK74.mouseDown-Left 
     */
    function Ak74MouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked || $this->AK74InWeaponSlot) return;
        
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();        
    }    
    /**
     * @event Inv_Ammo_9x18.mouseDown-Left 
     */
    function Ammo9x18MouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
        
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
        
        $this->Inv_PmAmmo_Count->toFront();
    }
    /**
     * @event Inv_Ammo_5x45.mouseDown-Left 
     */
    function Ammo5x45MouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
        
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
        
        $this->Inv_AkAmmo_Count->toFront();
    }
    /**
     * @event Inv_Vodka.click-Left 
     */
    function SelectVodka(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_vodka_selected']) return;
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_vodka_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        $this->form('Client')->Inventory->content->UseSlotSound();
    }
    /**
     * @event Inv_Medkit.click-Left 
     */
    function SelectMedkit(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_medkit_selected']) return;   
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_medkit_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        if ($e->clickCount <= 2) $this->form('Client')->Inventory->content->UseSlotSound();
    }
    /**
     * @event Inv_Outfit.click-Left 
     */
    function SelectOutfit(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_outfit_selected']) return;
        
        if ($e && $e->clickCount >= 2) return;
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_outfit_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        $this->form('Client')->Inventory->content->UseSlotSound();        
    }    
    /**
     * @event Inv_Wpn_Pm.click-Left 
     */
    function SelectPm(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_pm_selected']) return;   
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_pm_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        if ($e->clickCount <= 2) $this->form('Client')->Inventory->content->UseSlotSound();
    }
    /**
     * @event Inv_Wpn_AK74.click-Left 
     */
    function SelectAk74(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_ak74_selected']) return;   
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_ak74_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        if ($e->clickCount <= 2) $this->form('Client')->Inventory->content->UseSlotSound();
    }    
    /**
     * @event Inv_Ammo_9x18.click-Left 
     */
    function SelectAmmo9x18(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_ammo_9x18_selected']) return;
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_ammo_9x18_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        $this->form('Client')->Inventory->content->UseSlotSound();        
    }
    /**
     * @event Inv_Ammo_5x45.click-Left 
     */
    function SelectAmmo5x45(UXMouseEvent $e = null)
    {
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($GLOBALS['item_ammo_5x45_selected']) return;
        
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_ammo_5x45_selected'] = true;
        
        $this->form('Client')->Inventory->content->ShowUIText();
        $this->form('Client')->Inventory->content->SetItemInfo();
        $this->form('Client')->Inventory->content->SetItemCondition();
        
        $this->form('Client')->Inventory->content->UseSlotSound();        
    }
  
    /**
     * @event Inv_Vodka.click-Right 
     */
    function VodkaActions(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }
    /**
     * @event Inv_Outfit.click-Right 
     */
    function OutfitActions(UXMouseEvent $e = null)
    {    
        $this->selectedItem = $e->sender;
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }    
    /**
     * @event Inv_Medkit.click-Right 
     */
    function MedkitActions(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
    
        if ($e->clickCount >= 2)
        {
            $this->form('Client')->Inventory->content->HideCombobox();
            return;
        }
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }
    /**
     * @event Inv_Wpn_Pm.click-Right 
     */
    function PmActions(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        
        if ($e->clickCount >= 2)
        {
            $this->form('Client')->Inventory->content->HideCombobox();
            return;
        }
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }
    /**
     * @event Inv_Wpn_AK74.click-Right 
     */
    function Ak74Actions(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        
        if ($e->clickCount >= 2)
        {
            $this->form('Client')->Inventory->content->HideCombobox();
            return;
        }
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }       
    /**
     * @event Inv_Ammo_9x18.click-Right 
     */
    function Ammo9x18Actions(UXMouseEvent $e = null)
    {
    /*
        $this->selectedItem = $e->sender;
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    */
    }       

    /**
     * @event Inv_Ammo_5x45.click-Right 
     */
    function Ammo5x45Actions(UXMouseEvent $e = null)
    {
        /*
            $this->selectedItem = $e->sender;
            
            $this->form('Client')->Inventory->content->ShowCombobox();
        */
    }

    /**
     * @event inv_grid.click-Left 
     */
    function UpdateInvGrid(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $this->form('Client')->Inventory->content->HideUIText();   
        $this->form('Client')->Inventory->content->HideCombobox();
    }
    /**
     * @event inv_grid_wpn_1.click-Left 
     */
    function UpdateInvWpn1Grid(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $this->form('Client')->Inventory->content->HideUIText();   
        $this->form('Client')->Inventory->content->HideCombobox();
    }
    /**
     * @event inv_grid_wpn_2.click-Left 
     */
    function UpdateInvWpn2Grid(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $this->form('Client')->Inventory->content->HideUIText();   
        $this->form('Client')->Inventory->content->HideCombobox();
    }    
    function DropItem()
    {
        if (!$this->selectedItem) return;
        
        $this->form('Client')->Inventory->content->DropSound();
        $this->form('Client')->Inventory->content->HideCombobox();
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;
        $this->repackInventory();
        
        $this->form('Client')->Inventory->content->UpdateInventoryStatus();
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $this->form('Client')->Inventory->content->HideUIText();
        
        $this->form('Client')->MainGame->content->SpawnItem(); //в нашем случае водка
        
        $this->selectedItem = null; 
    }
    function UseItem()
    {
        if (!$this->selectedItem) return;
        
        $this->form('Client')->Inventory->content->UseSlotSound();
        $this->form('Client')->Inventory->content->HideCombobox();
        
        if ($this->selectedItem == $this->Inv_Vodka)
        {
            //$this->form('Client')->Inventory->content->ApplyVodkaEffect();
            //задел на будущее
        }
        elseif ($this->selectedItem == $this->Inv_Medkit)
        {
            $this->ApplyMedkitEffect();
            
            $this->medkitCount--;
            
            if ($this->medkitCount < 1)
            {
                $this->removeItemFromGrid($this->selectedItem);
                $this->selectedItem->visible = false;
            }            
        }
        else
        {
            return;
        }        
        
        $this->repackInventory();
        $this->updateMedkitCount();
        
        $this->form('Client')->Inventory->content->UpdateInventoryStatus();
        $this->form('Client')->Inventory->content->UpdateSelectedItems();
        $this->form('Client')->Inventory->content->HideUIText();
        
        $this->selectedItem = null;
    }
    
    public $isWearing = false;
    
    function TakeOffItem()
    {    
        if (!$this->selectedItem) return;    
    
        $nakedModel = 'res://.data/ui/maingame/sprite/noout/actor.png';
        $this->isWearing = true;
        
        $this->form('Client')->Inventory->content->inv_maket_visual->image = new UXImage($nakedModel);
        $this->form('Client')->MainGame->content->actor->image = new UXImage($nakedModel);
        
        $this->addOutfitToInventory();
        
        $this->form('Client')->Inventory->content->HideCombobox();
        $this->form('Client')->Inventory->content->HideUIText();
        
        $this->form('Client')->Inventory->content->DropSound();
        
        $this->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;        
    }
    function PutOnItem()
    {
        if (!$this->selectedItem) return;
    
        $wearingModel = 'res://.data/ui/maingame/sprite/actor.png';
        $this->isWearing = false;
    
        $this->form('Client')->Inventory->content->inv_maket_visual->image = new UXImage($wearingModel);
        $this->form('Client')->MainGame->content->actor->image = new UXImage($wearingModel);
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;
        $this->repackInventory();
        
        $this->form('Client')->Inventory->content->HideCombobox();
        
        $this->form('Client')->Inventory->content->HideUIText();
        $this->form('Client')->Inventory->content->UseSlotSound();   
        
        $this->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;
    }
    /**
     * @event Inv_Medkit.click-2x 
     */
    function QuickUseMedkit(UXMouseEvent $e = null)
    {    
        $this->selectedItem = $e->sender;

        $this->ApplyMedkitEffect();
        $this->form('Client')->Inventory->content->UseSlotSound();

        $this->medkitCount--;
                
        if ($this->medkitCount < 1)
        {
            $this->removeItemFromGrid($this->selectedItem);
            $this->selectedItem->visible = false;
            $this->form('Client')->Inventory->content->UpdateInventoryStatus();
            $this->form('Client')->Inventory->content->HideUIText();
            $this->form('Client')->Inventory->content->HideCombobox();
        }
        $this->repackInventory();
        $this->updateMedkitCount();

        $this->selectedItem = null;
        $GLOBALS['item_medkit_selected'] = false;
    }
    /**
     * @event Inv_Vodka.click-2x 
     */
    function QuickUseVodka(UXMouseEvent $e = null)
    {
    /*
        $this->selectedItem = $e->sender;
        
        $this->ApplyVodkaEffect();
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;

        $this->form('Client')->Inventory->content->UpdateInventoryStatus();
        $this->form('Client')->Inventory->content->HideUIText();

        $this->selectedItem = null;
    */
    }
    /**
     * @event Inv_Outfit.click-2x 
     */
    function QuickUseOutfit(UXMouseEvent $e = null)
    {    
        $this->selectedItem = $e->sender;
        
        $this->PutOnItem();
    }
    
    public $pmInWeaponSlot = false;
    public $AK74InWeaponSlot = false;
    
    private $PmSlotPos = [0, 0];
    private $Ak74SlotPos = [176, 0];
    
    function MoveWeaponsToInvSlot()
    {
        $pm = $this->Inv_Wpn_Pm;
        if ($pm)
        {
            $this->removeItemFromGrid($pm);
            
            $pm->position = [$this->PmSlotPos[0], $this->PmSlotPos[1]];
            $pm->visible = true;
            $pm->enabled = true;
            
            $this->pmInWeaponSlot = true;
        }
    
        $ak = $this->Inv_Wpn_AK74;
        if ($ak)
        {
            $this->removeItemFromGrid($ak);
            
            $ak->position = [$this->Ak74SlotPos[0], $this->Ak74SlotPos[1]];
            $ak->visible = true;
            $ak->enabled = true;
            
            $this->AK74InWeaponSlot = true;
        }
    
        $this->repackInventory();
    
        $mg = $this->form('Client')->MainGame->content;
        $mg->SwitchWeapon('Pm');
    
        $this->form('Client')->Inventory->content->UseSlotSound();
        $this->form('Client')->Inventory->content->HideCombobox();
    }

    /**
     * @event Inv_Wpn_Pm.click-2x 
     */
    function MovePmToSlot(UXMouseEvent $e = null)
    {
        $this->MoveWeaponToSlot('Pm');
    }   
    /**
     * @event Inv_Wpn_AK74.click-2x 
     */
    function MoveAK74ToSlot(UXMouseEvent $e = null)
    {
        $this->MoveWeaponToSlot('AK74');
    }
   
    function MoveWeaponToSlot(string $weaponName)
    {
        $weaponMap = [
            'Pm' => [
                'item' => $this->Inv_Wpn_Pm,
                'slotPos' => $this->PmSlotPos,
                'flag' => 'pmInWeaponSlot',
                'gridSize' => [1, 1],
                'weaponType' => 'Pm',
            ],
            'AK74' => [
                'item' => $this->Inv_Wpn_AK74,
                'slotPos' => $this->Ak74SlotPos,
                'flag' => 'AK74InWeaponSlot',
                'gridSize' => [6, 2],
                'weaponType' => 'AK74',
            ],
        ];
        if (!isset($weaponMap[$weaponName])) return;
    
        $weapon      = $weaponMap[$weaponName]['item'];
        $slotX       = $weaponMap[$weaponName]['slotPos'][0];
        $slotY       = $weaponMap[$weaponName]['slotPos'][1];
        $flagName    = $weaponMap[$weaponName]['flag'];
        $size        = $weaponMap[$weaponName]['gridSize'];
        $weaponType  = $weaponMap[$weaponName]['weaponType'];
    
        $this->selectedItem = $weapon;
    
        if ($this->$flagName)
        {
            $slot = $this->findFreeSlot($size[0], $size[1]);
            if ($slot != null)
            {
                list($cellX, $cellY) = $slot;
                $this->placeItem($weapon, $cellX, $cellY, $size[0], $size[1]);
                $this->$flagName = false; 
                $weapon->enabled = true;
                $weapon->visible = true;
    
                $mg = $this->form('Client')->MainGame->content;
                $mg->UnequipCurrentWeapon();
    
                $this->form('Client')->Inventory->content->UseSlotSound();
                $this->form('Client')->Inventory->content->HideCombobox();
            }
            return;
        }
    
        $foundInGrid = false;
        for ($x = 0; $x < 11; $x++)
        {
            for ($y = 0; $y < 16; $y++) {
                if ($this->grid[$x][$y] === $weapon)
                {
                    $this->grid[$x][$y] = null;
                    $foundInGrid = true;
                }
            }
        }
    
        if ($foundInGrid)
        {
            $weapon->position = [$slotX, $slotY];
            $weapon->visible = true;
            $weapon->enabled = true;
            $this->$flagName = true;
    
            $mg = $this->form('Client')->MainGame->content;
            $mg->SwitchWeapon($weaponType);
    
            $this->form('Client')->Inventory->content->UseSlotSound();
            $this->form('Client')->Inventory->content->HideCombobox();
        }
    
        $this->repackInventory();    
        $this->selectedItem = null;
    }

    public $isAnimatingBars = [];
    private $isAnimatingBarsTimers = [];

    function animateResizeWidth($node, $targetWidth, $speed = 1, $callback = null)
    {
        $id = spl_object_hash($node);
    
        if (isset($this->isAnimatingBarsTimers[$id]))
        {
            $this->isAnimatingBarsTimers[$id]->stop();
        }

        $this->isAnimatingBars[$id] = true;

        $timer = new UXAnimationTimer(function () use ($node, $targetWidth, $speed, &$timer, $callback, $id) {
            if ($node->width < $targetWidth)
            {
                $node->width += $speed;
                if ($node->width >= $targetWidth)
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimatingBars[$id] = false;
                    if ($callback) $callback();
                }
            }
            elseif ($node->width > $targetWidth)
            {
                $node->width -= $speed;
                if ($node->width <= $targetWidth) 
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimatingBars[$id] = false;
                    if ($callback) $callback();
                }
            }
            else
            {
                $timer->stop();
                $this->isAnimatingBars[$id] = false;
                if ($callback) $callback();
            }
        });

        $this->isAnimatingBarsTimers[$id] = $timer;

        $timer->start();
    }
    
    function ApplyMedkitEffect()
    {
        $minWidth     = 54;
        $maxWidthMain = 264;
        $maxWidthInv  = 416;
    
        $bar     = $this->form('Client')->MainGame->content->health_bar_gg;
        $inv_bar = $this->form('Client')->Inventory->content->health_bar_gg;
    
        $currentW   = $bar->width;
        $currentPct = round((($currentW - $minWidth) / ($maxWidthMain - $minWidth)) * 99) + 1;
        $currentPct = max(1, min(100, $currentPct));
    
        $healPct = 20;
        $newPct  = min(100, $currentPct + $healPct);
    
        $targetMain = (int) round($minWidth + (($maxWidthMain - $minWidth) * ($newPct - 1) / 99));
        $targetInv  = (int) round($minWidth + (($maxWidthInv  - $minWidth) * ($newPct - 1) / 99));
    
        $this->animateResizeWidth($bar, $targetMain, 5, function() use ($bar, $newPct) {
            $bar->text = $newPct . "%";
        });
    
        $this->animateResizeWidth($inv_bar, $targetInv, 5, function() use ($inv_bar, $newPct) {
            $inv_bar->text = $newPct . "%";
        });
    }
    function ApplyVodkaEffect()
    {
        
    } 
}
