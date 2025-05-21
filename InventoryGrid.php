<?php
namespace app\forms;

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
            $this->Inv_Medkit
        ];        

        $this->addVodkaToInventory();
        $this->addMedkitToInventory();
    }
    /**
     * @event mouseMove
     */
    function GridMouseMove(UXMouseEvent $e = null)
    {
        if ($this->draggedItem == null) return;

        $offsetX = $this->draggedItem->width / 2;
        $offsetY = $this->draggedItem->height / 2;

        $mouseX = $e->sceneX;
        $mouseY = $e->sceneY;

        $newX = $mouseX - $offsetX;
        $newY = $mouseY - $offsetY;

        $gridLeft = 0;
        $gridTop = 0;
        $gridRight = 552;
        $gridBottom = 784;

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
        if ($this->draggedItem == null) return;

        $this->grid;

        $cellSize = 49;
        $gridLeft = 0;
        $gridTop = 0;

        $mouseX = $e->x;
        $mouseY = $e->y;

        if ($mouseX < 0 || $mouseY < 0 || $mouseX >= 552 || $mouseY >= 784)
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
        $this->grid;

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
        $this->grid;

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
    }    
    function canPlace($cellX, $cellY, $w, $h): bool
    {
        $this->grid;

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
        $this->grid;

        for ($x = 0; $x < $w; $x++)
        {
            for ($y = 0; $y < $h; $y++)
            {
                $this->grid[$cellX + $x][$cellY + $y] = $item;
            }
        }

        $cellSize = 49;
        $gridX = $cellX * $cellSize;
        $gridY = $cellY * $cellSize;

        $itemW = $item->width;
        $itemH = $item->height;

        $posX = $gridX + ($cellSize * $w - $itemW) / 2;
        $posY = $gridY + ($cellSize * $h - $itemH) / 2;

        $item->position = [$posX, $posY];
        $item->visible = true;
        
        $this->updateMedkitCount();
    }
    function removeItemFromGrid($item)
    {
        $this->grid;

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
        foreach ($this->inventoryItems as $item)
        {
            if ($item->visible)
            {
                $this->removeItemFromGrid($item);
            }
        }

        foreach ($this->inventoryItems as $item)
        {
            if (!$item->visible) continue;

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
    /**
     * @event Inv_Vodka.mouseDown-Left 
     */
    function VodkaMouseDown(UXMouseEvent $e = null)
    {
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
    }
    /**
     * @event Inv_Medkit.mouseDown-Left 
     */
    function MedkitMouseDown(UXMouseEvent $e = null)
    {
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
        $this->Inv_Medkit_Count->toFront();
    }    
    /**
     * @event Inv_Vodka.click-Left 
     */
    function SelectVodka(UXMouseEvent $e = null)
    {
        $this->HideCombobox();
        
        if ($GLOBALS['item_vodka_selected']) return;
        
        $this->form('maingame')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_vodka_selected'] = true;
        
        $this->form('maingame')->Inventory->content->ShowUIText();
        $this->form('maingame')->Inventory->content->SetItemInfo();
        $this->form('maingame')->Inventory->content->SetItemCondition();
        
        $this->form('maingame')->Inventory->content->UseSlotSound();
    }
    /**
     * @event Inv_Medkit.click-Left 
     */
    function SelectMedkit(UXMouseEvent $e = null)
    {
        $this->HideCombobox();
        
        if ($GLOBALS['item_medkit_selected']) return;
        
        $this->form('maingame')->Inventory->content->UpdateSelectedItems();
        $GLOBALS['item_medkit_selected'] = true;
        
        $this->form('maingame')->Inventory->content->ShowUIText();
        $this->form('maingame')->Inventory->content->SetItemInfo();
        $this->form('maingame')->Inventory->content->SetItemCondition();
        
        if ($e->clickCount <= 2) $this->form('maingame')->Inventory->content->UseSlotSound();
    }
    /**
     * @event Inv_Vodka.click-Right 
     */
    function VodkaActions(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        $this->ShowCombobox();
    }
    /**
     * @event Inv_Medkit.click-Right 
     */
    function MedkitActions(UXMouseEvent $e = null)
    {
        if ($e->clickCount >= 2)
        {
            $this->HideCombobox();
            return;
        }
        
        $this->selectedItem = $e->sender;
        $this->ShowCombobox();
    }    
    /**
     * @event inv_grid.click-Left 
     */
    function UpdateInventoryGrid(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Inventory->content->UpdateSelectedItems();
        $this->form('maingame')->Inventory->content->HideUIText();   
        $this->HideCombobox();
    }
    /**
     * @event Combobox_Drop.click-Left 
     */
    function DropItem(UXMouseEvent $e = null)
    {
        if (!$this->selectedItem) return;
        
        $this->form('maingame')->Inventory->content->DropSound();
        $this->HideCombobox();
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;
        $this->repackInventory();
        
        $this->form('maingame')->Inventory->content->UpdateInventoryStatus();
        $this->form('maingame')->Inventory->content->HideUIText();
        
        $this->form('maingame')->SpawnItem(); //в нашем случае водка
        
        $this->selectedItem = null; 
    }
    /**
     * @event Combobox_Use.click-Left 
     */
    function UseItem(UXMouseEvent $e = null)
    {
        if (!$this->selectedItem) return;
        
        $this->form('maingame')->Inventory->content->UseSlotSound();
        $this->HideCombobox();
        
        if ($this->selectedItem == $this->Inv_Vodka)
        {
            //$this->form('maingame')->Inventory->content->ApplyVodkaEffect();
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
        
        $this->form('maingame')->Inventory->content->UpdateInventoryStatus();
        $this->form('maingame')->Inventory->content->HideUIText();
        
        $this->selectedItem = null;
    }
    /**
     * @event Inv_Medkit.click-2x 
     */
    function QuickUseMedkit(UXMouseEvent $e = null)
    {    
        $this->selectedItem = $e->sender;

        $this->ApplyMedkitEffect();
        $this->form('maingame')->Inventory->content->UseSlotSound();

        $this->medkitCount--;
                
        if ($this->medkitCount < 1)
        {
            $this->removeItemFromGrid($this->selectedItem);
            $this->selectedItem->visible = false;
            $this->form('maingame')->Inventory->content->UpdateInventoryStatus();
            $this->form('maingame')->Inventory->content->HideUIText();
            $this->HideCombobox();
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

        $this->form('maingame')->Inventory->content->UpdateInventoryStatus();
        $this->form('maingame')->Inventory->content->HideUIText();

        $this->selectedItem = null;
    */
    }
    function ApplyMedkitEffect()
    {
        $bar = $this->form('maingame')->health_bar_gg;
        $inv_bar = $this->form('maingame')->Inventory->content->health_bar_gg;

        switch ($bar->width)
        {
            case 54:
                $bar->width += 30;
                $bar->text = "15%";

                $target = $inv_bar->width + 50;
                $this->form('maingame')->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "15%";
                });
                break;

            case 84:
                $bar->width += 60;
                $bar->text = "33%";

                $target = $inv_bar->width + 100;
                $this->form('maingame')->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "33%";
                });
                break;
            case 114:
            case 144:
                $bar->width += 30;
                $bar->text = "50%";

                $target = $inv_bar->width + 40;
                $this->form('maingame')->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "50%";
                });
                break;

            case 174:
                $bar->width += 30;
                $bar->text = "55%";

                $target = $inv_bar->width + 40;
                $this->form('maingame')->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "55%";
                });
                break;

            case 204:
                $bar->width += 30;
                $bar->text = "75%";

                $target = $inv_bar->width + 100;
                $this->form('maingame')->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "75%";
                });
                break;

            case 234:
                $bar->width += 30;
                $bar->text = "100%";

                $this->form('maingame')->animateResizeWidth($inv_bar, 416, 5, function() use ($inv_bar) {
                    $inv_bar->text = "100%";
                });
                break;
        }      
    }
    function ApplyVodkaEffect()
    {
        
    } 
    function ShowCombobox()
    {     
        if (!$this->selectedItem) return;

        $this->form('maingame')->Inventory->content->PropertiesSound();

        list($itemX, $itemY) = $this->selectedItem->position;

        $comboWidth = $this->main->width;
        $comboHeight = $this->main->height;

        $gridLeft = 0;
        $gridTop = 0;
        $gridRight = 552;
        $gridBottom = 784;

        $comboX = $itemX;
        $comboY = $itemY - $comboHeight - 10;

        if ($comboX + $comboWidth > $gridRight)
        {
            $comboX = $gridRight - $comboWidth;
        }

        if ($comboX < $gridLeft)
        {
            $comboX = $gridLeft;
        }

        if ($comboY < $gridTop)
        {
            $comboY = $itemY + $this->selectedItem->height + 10;
        }

        $this->main->position = [$comboX, $comboY];
        $offsetY = 8;

        $this->Combobox_Use->hide();
        $this->Combobox_Drop->hide();
        
        $this->main->show();
        $this->main->toFront();

        if ($this->selectedItem == $this->Inv_Vodka)
        {
            $this->Combobox_Drop->position = [$comboX + 8, $comboY + $offsetY];
            $this->Combobox_Drop->toFront();
            $this->Combobox_Drop->show();
        }

        if ($this->selectedItem == $this->Inv_Medkit)
        {
            $this->Combobox_Use->position = [$comboX + 8, $comboY + $offsetY];
            $this->Combobox_Use->toFront();
            $this->Combobox_Use->show();
        }      
    }    
    function HideCombobox()
    {  
        $this->main->hide();
        if ($this->selectedItem == $this->Inv_Medkit)
        {
            $this->Combobox_Use->hide();
        }
        if ($this->selectedItem == $this->Inv_Vodka)
        {
            $this->Combobox_Drop->hide();
        }
    }    
}
