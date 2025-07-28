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
            $this->Inv_Outfit
        ];        

        $this->addVodkaToInventory();
        $this->addMedkitToInventory();
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
        if ($this->draggedItem == null || $this->inventoryLocked) return;

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
            if ($item->visible)
            {
                $visibleItems[] = $item;
            }
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
    }
    /**
     * @event Inv_Outfit.mouseDown-Left 
     */
    function OutfittMouseDown(UXMouseEvent $e = null)
    {
        if ($this->inventoryLocked) return;    
    
        $this->draggedItem = $e->sender;
        $this->draggedItemOriginalPos = $this->draggedItem->position;
        
        $this->draggedItem->toFront();
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
        if ($e->clickCount >= 2)
        {
            $this->form('Client')->Inventory->content->HideCombobox();
            return;
        }
        
        $this->selectedItem = $e->sender;
        
        $this->form('Client')->Inventory->content->ShowCombobox();
    }    
    /**
     * @event inv_grid.click-Left 
     */
    function UpdateInventoryGrid(UXMouseEvent $e = null)
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
    
        $nakedModel = 'res://.data/ui/actor_noout.png';
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
        $bar = $this->form('Client')->MainGame->content->health_bar_gg;
        $inv_bar = $this->form('Client')->Inventory->content->health_bar_gg;

        $width = $bar->width;

        switch ($width)
        {
            case 54:
                $bar->width += 30;
                $bar->text = "15%";

                $target = $inv_bar->width + 50;
                $this->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "15%";
                });
                break;

            case 84:
                $bar->width += 60;
                $bar->text = "33%";

                $target = $inv_bar->width + 100;
                $this->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "33%";
                });
                break;

            case 114:
            case 144:
                $bar->width += 30;
                $bar->text = "50%";

                $target = $inv_bar->width + 40;
                $this->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "50%";
                });
                break;

            case 174:
                $bar->width += 30;
                $bar->text = "55%";

                $target = $inv_bar->width + 40;
                $this->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "55%";
                });
                break;

            case 204:
                $bar->width += 30;
                $bar->text = "75%";

                $target = $inv_bar->width + 100;
                $this->animateResizeWidth($inv_bar, $target, 5, function() use ($inv_bar) {
                    $inv_bar->text = "75%";
                });
                break;

            case 234:
                $bar->width += 30;
                if ($bar->width > 264) $bar->width = 264;
                $bar->text = "100%";

                $this->animateResizeWidth($inv_bar, 416, 5, function() use ($inv_bar) {
                    $inv_bar->text = "100%";
                });
                break;
        }
    }
    function ApplyVodkaEffect()
    {
        
    } 
}
