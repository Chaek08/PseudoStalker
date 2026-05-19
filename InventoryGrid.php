<?php
namespace app\forms;

use app\forms\classes\UI\InventoryDragManager;
use php\gui\UXApplication;
use php\time\Timer;
use php\gui\UXImage;
use php\gui\UXImageView;
use behaviour\custom\LightingEffectBehaviour;
use php\gui\animation\UXAnimationTimer;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 

class InventoryGrid extends AbstractForm
{
    var $grid;
    
    private $inventoryItems = [];
   
    private $dragManager;
   
    public $selectedItem = null;
    public $medkitCount = 0;
    public $pmAmmoCount = 25;
    public $akAmmoCount = 69;
    public $isWearing = false;
    
    private $gridLeft = 0;
    private $gridTop = 120;
    private $gridRight = 552;
    private $gridBottom = 904;
    
    //temp
    private $weaponSlots = [
        'Pm' => [
            'item' => 'Inv_Wpn_Pm',
            'rect' => ['x'=>0, 'y'=>0, 'w'=>152, 'h'=>96],
            'pos' => [0, 0],
            'size' => [1, 1],
            'equipped' => false
        ],
    
        'AK74' => [
            'item' => 'Inv_Wpn_AK74',
            'rect' => ['x'=>176, 'y'=>0, 'w'=>245, 'h'=>96],
            'pos' => [176, 0],
            'size' => [5, 2],
            'equipped' => false
        ]
    ];    
  
    private $outfitSlotRect = ['x'=>1128, 'y'=>128, 'w'=>448, 'h'=>672];  

    public function __construct()
    {
        parent::__construct();
        
        $this->grid = [];
        
        for ($x = 0; $x < 11; $x++) 
        {
            for ($y = 0; $y < 13; $y++) //ВЫСОТА СЕТКИ 15 ЧТОБЫ НЕ ЗАЛАЗИТЬ ЗА ТОТ РЯД ЯЧЕЕК КОТОЫРЙ НАПОЛОВМИНУ УХОДИТ ЗА ПРЕДЕЛЫ ТО ЕСТЬ НЕПОЛНЫЙ
            {
                $this->grid[$x][$y] = null;
            }
        }
        
        $this->dragManager = new InventoryDragManager($this);
        
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
    
    private function pointInRect($x, $y, $r): bool
    {
        return $x >= $r['x'] && $x < ($r['x'] + $r['w']) && $y >= $r['y'] && $y < ($r['y'] + $r['h']);
    }
    
    public function cancelDrag(): void
    {
        $this->dragManager->endDrag();
    }   
    
    public function getWeaponSlot(string $weapon)
    {
        return $this->weaponSlots[$weapon] ?? null;
    }     
    
    /**
     * @event mouseMove
     */
    function GridMouseMove(UXMouseEvent $e = null)
    {
        if ($this->dragManager->getDraggedItem() == null) return;
        
        if (!$this->dragManager->isActivated()) return;
    
        $cellSize = 49;
    
        list($itemW, $itemH) = $this->itemGridSize($this->dragManager->getDraggedItem());
    
        $cellX = floor(($e->x - $this->gridLeft) / $cellSize);
        $cellY = floor(($e->y - $this->gridTop) / $cellSize);
    
        $cellX = max(0, min($cellX, 11 - $itemW));
        $cellY = max(0, min($cellY, 13 - $itemH));
    
        $gridX = $this->gridLeft + ($cellX * $cellSize);
        $gridY = $this->gridTop + ($cellY * $cellSize);
    
        $posX = $gridX + (($cellSize * $itemW) - $this->dragManager->getDraggedItem()->width) / 2;
        $posY = $gridY + (($cellSize * $itemH) - $this->dragManager->getDraggedItem()->height) / 2;
    
        $this->dragManager->getDraggedItem()->position = [$posX, $posY];
    }
    
    /**
     * @event mouseUp-Left
     */
    function GridMouseUp(UXMouseEvent $e = null)
    {
        if ($this->dragManager->getDraggedItem() == null) return;
        
        if (!$this->dragManager->isActivated())
        {
            $this->dragManager->endDrag();
            return;
        }
        
        $cellSize = 49;
        $mouseX = $e->x;
        $mouseY = $e->y;
        
        foreach ($this->weaponSlots as $weapon => $slot)
        {
            $item = $this->{$slot['item']};
        
            if (
                $this->dragManager->getDraggedItem() === $item &&
                $this->pointInRect($mouseX, $mouseY, $slot['rect'])
            )
            {
                $this->moveWeaponToSlot($weapon);
                $this->dragManager->endDrag();
                return;
            }
        }
        if ($this->dragManager->getDraggedItem() === $this->Inv_Outfit && $this->pointInRect($mouseX, $mouseY, $this->outfitSlotRect))
        {
            $this->selectedItem = $this->Inv_Outfit;
        
            $this->PutOnItem();
        
            $this->dragManager->endDrag();
            return;
        }
        
        if ($mouseX < 0 || $mouseY < $this->gridTop || $mouseX >= 552 || $mouseY >= $this->gridBottom)
        {
            $this->dragManager->getDraggedItem()->position = $this->dragManager->getOriginalPosition();
            $this->dragManager->endDrag();
            return;
        }
        
        $cellX = floor(($mouseX - $this->gridLeft) / $cellSize);
        $cellY = floor(($mouseY - $this->gridTop) / $cellSize);
        
        list($itemWidthCells, $itemHeightCells) = $this->itemGridSize($this->dragManager->getDraggedItem());

        $this->removeItemFromGrid($this->dragManager->getDraggedItem());
        
        if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_Pm && $this->weaponSlots['Pm']['equipped'])
        {
            $actor = $this->form('Client')->MainGame->content->GameActor;
            $w = $actor->getWeapon();
        
            if ($w && $w->getType() === 'Pm')
            {
                $actor->UnequipCurrentWeapon();
            }
        
            $this->weaponSlots['Pm']['equipped'] = false;
        }
        
        if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_AK74 && $this->weaponSlots['AK74']['equipped'])
        {
            $actor = $this->form('Client')->MainGame->content->GameActor;
            $w = $actor->getWeapon();
        
            if ($w && $w->getType() === 'AK74')
            {
                $actor->UnequipCurrentWeapon();
            }
        
            $this->weaponSlots['AK74']['equipped'] = false;
        }
        
        if ($this->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells))
        {
            if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_Pm)
                $this->weaponSlots['Pm']['equipped'] = false;
            
            if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_AK74)
                $this->weaponSlots['AK74']['equipped'] = false;
            
            $this->placeItem($this->dragManager->getDraggedItem(), $cellX, $cellY, $itemWidthCells, $itemHeightCells);
            
            $this->form('Client')->Inventory->content->UpdateComboboxPosition();
            //$this->form('Client')->Inventory->content->UseSlotSound();
        }
        else
        {
            $originalPosition = $this->dragManager->getOriginalPosition();
            $this->dragManager->getDraggedItem()->position = $originalPosition;
            $originalX = floor(($originalPosition[0] - $this->gridLeft) / $cellSize);
            $originalY = floor(($originalPosition[1] - $this->gridTop) / $cellSize);
            $this->placeItem($this->dragManager->getDraggedItem(), $originalX, $originalY, $itemWidthCells, $itemHeightCells);
        }
        
        $this->dragManager->endDrag();
    }
    
    private function itemGridSize($item): array
    {
        if ($item === $this->Inv_Vodka)      return [1, 2];
        if ($item === $this->Inv_Medkit)     return [2, 1];
        if ($item === $this->Inv_Outfit)     return [2, 2];
        if ($item === $this->Inv_Ammo_9x18)  return [1, 1];
        if ($item === $this->Inv_Ammo_5x45)  return [2, 1];
        if ($item === $this->Inv_Wpn_Pm)     return [1, 1];
        if ($item === $this->Inv_Wpn_AK74)   return [5, 2];
        
        return [ceil($item->width / 49), ceil($item->height / 49)];
    }
    
    /** @event Inv_Vodka.mouseDrag */
    function VodkaMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender); }
    
    /** @event Inv_Medkit.mouseDrag */
    function MedkitMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender, $this->Inv_Medkit_Count); }
    
    /** @event Inv_Outfit.mouseDrag */
    function OutfitMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender); }
    
    /** @event Inv_Wpn_Pm.mouseDrag */
    function PmMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender); }
    
    /** @event Inv_Wpn_AK74.mouseDrag */
    function Ak74MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender); }
    
    /** @event Inv_Ammo_9x18.mouseDrag */
    function Ammo9x18MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender, $this->Inv_PmAmmo_Count); }
    
    /** @event Inv_Ammo_5x45.mouseDrag */
    function Ammo5x45MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($e->sender, $this->Inv_AkAmmo_Count); }
    
    function addVodkaToInventory() { $this->addItemToInventory($this->Inv_Vodka, 1, 2); }
    function addOutfitToInventory() { $this->addItemToInventory($this->Inv_Outfit, 2, 1); }
    function addPmToInventory() { $this->addItemToInventory($this->Inv_Wpn_Pm, 1, 1); }
    function addAk74ToInventory() { $this->addItemToInventory($this->Inv_Wpn_AK74, 1, 1); }
    
    function addMedkitToInventory()
    {
        $this->medkitCount += 2;
        $this->updateMedkitCount();
        $this->addItemToInventory($this->Inv_Medkit, 2, 1);
    }
    
    function addAmmo9x18ToInventory()
    {
        $this->updateAmmo9x18Count();
        $this->addItemToInventory($this->Inv_Ammo_9x18, 2, 1);
    }
    
    function addAmmo5x45ToInventory()
    {
        $this->updateAmmo5x45Count();
        $this->addItemToInventory($this->Inv_Ammo_5x45, 1, 1);
    }
    
    private function addItemToInventory($item, $w, $h)
    {
        $slot = $this->findFreeSlot($w, $h);
        if (!$slot) return;
        
        list($cellX, $cellY) = $slot;
        if (!$this->canPlace($cellX, $cellY, $w, $h)) return;
        
        $this->placeItem($item, $cellX, $cellY, $w, $h);
    }
    
    function updateMedkitCount() { $this->updateCountLabel($this->Inv_Medkit, $this->Inv_Medkit_Count, $this->medkitCount); }
    function updateAmmo9x18Count() { $this->updateCountLabel($this->Inv_Ammo_9x18, $this->Inv_PmAmmo_Count, $this->pmAmmoCount); }
    function updateAmmo5x45Count() { $this->updateCountLabel($this->Inv_Ammo_5x45, $this->Inv_AkAmmo_Count, $this->akAmmoCount); }
    
    private function updateCountLabel($item, $label, $count)
    {
        $label->position = [$item->x, $item->y];
        
        if ($count >= 2)
        {
            $label->text = 'x' . $count;
            $label->visible = true;
        }
        else
        {
            $label->visible = false;
        }
        
        if ($count < 1)
        {
            $this->removeItemFromGrid($item);
            $item->visible = false;
        }
    }
    
    public function getItemCount($item): int
    {
        if ($item === $this->Inv_Medkit)
        {
            return $this->medkitCount;
        }
    
        if ($item === $this->Inv_Ammo_9x18)
        {
            return $this->pmAmmoCount;
        }
    
        if ($item === $this->Inv_Ammo_5x45)
        {
            return $this->akAmmoCount;
        }
    
        return 0;
    }
    
    public function getItemCountLabel($item)
    {
        if ($item === $this->Inv_Medkit)    return $this->Inv_Medkit_Count;
        if ($item === $this->Inv_Ammo_9x18) return $this->Inv_PmAmmo_Count;
        if ($item === $this->Inv_Ammo_5x45) return $this->Inv_AkAmmo_Count;
    
        return null;
    }    
    
    function canPlace($cellX, $cellY, $w, $h): bool
    {
        if ($cellX < 0 || $cellY < 0) return false;

        if ($cellX + $w > 11 || $cellY + $h > 13) return false;
        
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
        
        $posX = $gridX + ($cellSize * $w - $item->width) / 2;
        $posY = $gridY + ($cellSize * $h - $item->height) / 2;
        
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
            for ($y = 0; $y < 13; $y++)
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
        for ($y = 0; $y < 13; $y++)
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
            if ($item == $this->Inv_Wpn_Pm && $this->weaponSlots['Pm']['equipped']) continue;
            if ($item == $this->Inv_Wpn_AK74 && $this->weaponSlots['AK74']['equipped']) continue;
            
            $visibleItems[] = $item;
        }
        
        foreach ($visibleItems as $item)
        {
            $this->removeItemFromGrid($item);
        }
        
        foreach ($visibleItems as $item)
        {
            list($w, $h) = $this->itemGridSize($item);
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
  
    /** @event Inv_Vodka.click-Left */
    function SelectVodka(UXMouseEvent $e = null) { $this->selectItem('item_vodka_selected'); }
    
    /** @event Inv_Medkit.click-Left */
    function SelectMedkit(UXMouseEvent $e = null) { $this->selectItem('item_medkit_selected', $e->clickCount <= 2); }
    
    /** @event Inv_Outfit.click-Left */
    function SelectOutfit(UXMouseEvent $e = null)
    {
        if ($e && $e->clickCount >= 2) return;
        $this->selectItem('item_outfit_selected');
    }
    
    /** @event Inv_Wpn_Pm.click-Left */
    function SelectPm(UXMouseEvent $e = null) { $this->selectItem('item_pm_selected', $e->clickCount <= 2); }
    
    /** @event Inv_Wpn_AK74.click-Left */
    function SelectAk74(UXMouseEvent $e = null) { $this->selectItem('item_ak74_selected', $e->clickCount <= 2); }
    
    /** @event Inv_Ammo_9x18.click-Left */
    function SelectAmmo9x18(UXMouseEvent $e = null) { $this->selectItem('item_ammo_9x18_selected'); }
    
    /** @event Inv_Ammo_5x45.click-Left */
    function SelectAmmo5x45(UXMouseEvent $e = null) { $this->selectItem('item_ammo_5x45_selected'); }
    
    private function selectItem($globalKey, $playSound = true)
    {
        $inv = $this->form('Client')->Inventory->content;
        $inv->HideCombobox();
        
        if ($GLOBALS[$globalKey]) return;
        
        $inv->UpdateSelectedItems();
        $GLOBALS[$globalKey] = true;
        
        $inv->ShowUIText();
        $inv->SetItemInfo();
        $inv->SetItemCondition();
        
        if ($playSound) $inv->UseSlotSound();
    }
    
    /** @event Inv_Vodka.click-Right */
    function VodkaActions(UXMouseEvent $e = null) { $this->showItemActions($e->sender); }
    
    /** @event Inv_Outfit.click-Right */
    function OutfitActions(UXMouseEvent $e = null) { $this->showItemActions($e->sender); }
    
    /** @event Inv_Medkit.click-Right */
    function MedkitActions(UXMouseEvent $e = null) { $this->showItemActions($e->sender, $e->clickCount >= 2); }
    
    /** @event Inv_Wpn_Pm.click-Right */
    function PmActions(UXMouseEvent $e = null) { $this->showItemActions($e->sender, $e->clickCount >= 2); }
    
    /** @event Inv_Wpn_AK74.click-Right */
    function Ak74Actions(UXMouseEvent $e = null) { $this->showItemActions($e->sender, $e->clickCount >= 2); }
    
    private function showItemActions($item, $hide = false)
    {
        $this->selectedItem = $item;
        $inv = $this->form('Client')->Inventory->content;
        
        if ($hide)
        {
            $inv->HideCombobox();
        }
        else
        {
            $inv->ShowCombobox();
        }
    }
    
    /** @event inv_grid.click-Left */
    function UpdateInvGrid(UXMouseEvent $e = null) { $this->updateGridUI(); }
    
    /** @event inv_grid_wpn_1.click-Left */
    function UpdateInvWpn1Grid(UXMouseEvent $e = null) { $this->updateGridUI(); }
    
    /** @event inv_grid_wpn_2.click-Left */
    function UpdateInvWpn2Grid(UXMouseEvent $e = null) { $this->updateGridUI(); }
    
    private function updateGridUI()
    {
        $inv = $this->form('Client')->Inventory->content;
        $inv->UpdateSelectedItems();
        $inv->HideUIText();
        $inv->HideCombobox();
    }
    
    function DropItem()
    {
        if (!$this->selectedItem) return;
        
        $inv = $this->form('Client')->Inventory->content;
        $inv->DropSound();
        $inv->HideCombobox();
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;
        $this->repackInventory();
        
        $inv->UpdateInventoryStatus();
        $inv->UpdateSelectedItems();
        $inv->HideUIText();
        
        $this->form('Client')->MainGame->content->SpawnItem();
        
        $this->selectedItem = null;
    }
    
    function UseItem()
    {
        if (!$this->selectedItem) return;
        
        $inv = $this->form('Client')->Inventory->content;
        $inv->UseSlotSound();
        $inv->HideCombobox();
        
        if ($this->selectedItem == $this->Inv_Medkit)
        {
            $this->ApplyMedkitEffect();
            $this->medkitCount--;
            
            if ($this->medkitCount < 1)
            {
                $this->removeItemFromGrid($this->selectedItem);
                $this->selectedItem->visible = false;
                $this->repackInventory();
            }
        }
        else
        {
            return;
        }
        
        $this->updateMedkitCount();
        
        $inv->UpdateInventoryStatus();
        $inv->UpdateSelectedItems();
        $inv->HideUIText();
        
        $this->selectedItem = null;
    }
    
    function TakeOffItem()
    {
        if (!$this->selectedItem) return;
        
        $this->isWearing = true;
        $nakedModel = 'res://.data/ui/maingame/sprite/noout/actor.png';
        
        $inv = $this->form('Client')->Inventory->content;
        $inv->inv_maket_visual->image = new UXImage($nakedModel);
        $this->form('Client')->MainGame->content->actor->image = new UXImage($nakedModel);
        
        $this->addOutfitToInventory();
        
        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->DropSound();
        
        $this->repackInventory();
        
        $this->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;
    }
    
    function PutOnItem()
    {
        if (!$this->selectedItem) return;
        
        $this->isWearing = false;
        $wearingModel = 'res://.data/ui/maingame/sprite/actor.png';
        
        $inv = $this->form('Client')->Inventory->content;
        $inv->inv_maket_visual->image = new UXImage($wearingModel);
        $this->form('Client')->MainGame->content->actor->image = new UXImage($wearingModel);
        
        $this->removeItemFromGrid($this->selectedItem);
        $this->selectedItem->visible = false;
        $this->repackInventory();
        
        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->UseSlotSound();
        
        $this->selectedItem = null;
        $GLOBALS['item_outfit_selected'] = false;
    }
    
    function MoveToSlot()
    {
        if ($this->selectedItem == $this->Inv_Wpn_AK74) $this->MoveAK74ToSlot();
        if ($this->selectedItem == $this->Inv_Wpn_Pm) $this->MovePmToSlot();
    }
    
    /** @event Inv_Medkit.click-2x */
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
            $this->repackInventory();
            
            $inv = $this->form('Client')->Inventory->content;
            $inv->UpdateInventoryStatus();
            $inv->HideUIText();
            $inv->HideCombobox();
        }
        
        $this->updateMedkitCount();
        
        $this->selectedItem = null;
        $GLOBALS['item_medkit_selected'] = false;
    }
    
    /** @event Inv_Outfit.click-2x */
    function QuickUseOutfit(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        $this->PutOnItem();
    }
    
    function MoveWeaponsToWeaponSlot()
    {
        foreach ($this->weaponSlots as $weapon => &$slot)
        {
            $item = $this->{$slot['item']};
    
            if (!$item) continue;
    
            $this->removeItemFromGrid($item);
    
            $item->position = $slot['pos'];
            $item->visible = true;
            $item->enabled = true;
    
            $slot['equipped'] = true;
        }
    
        $this->repackInventory();
    
        $this->form('Client')->MainGame->content->GameActor->SwitchWeapon('Pm');
        $this->form('Client')->Inventory->content->UseSlotSound();
        $this->form('Client')->Inventory->content->HideCombobox();
    }
    
    /** @event Inv_Wpn_Pm.click-2x */
    function MovePmToSlot(UXMouseEvent $e = null) { $this->moveWeaponToSlot('Pm'); }
    
    /** @event Inv_Wpn_AK74.click-2x */
    function MoveAK74ToSlot(UXMouseEvent $e = null) { $this->moveWeaponToSlot('AK74'); }


    
    function moveWeaponToSlot(string $weaponName)
    {
        $slot = $this->getWeaponSlot($weaponName);
        if (!$slot) return;
    
        $item = $this->{$slot['item']};
    
        $this->selectedItem = $item;
    
        if ($this->weaponSlots[$weaponName]['equipped'])
        {
            $free = $this->findFreeSlot($slot['size'][0], $slot['size'][1]);
    
            if ($free)
            {
                list($x, $y) = $free;
    
                $this->placeItem($item, $x, $y, $slot['size'][0], $slot['size'][1]);
    
                $this->weaponSlots[$weaponName]['equipped'] = false;
    
                $this->form('Client')->MainGame->content->GameActor->UnequipCurrentWeapon();
            }
    
            return;
        }
    
        $this->removeItemFromGrid($item);
    
        $item->position = $slot['pos'];
        $item->visible = true;
        $item->enabled = true;
    
        $this->weaponSlots[$weaponName]['equipped'] = true;
    
        $this->form('Client')->MainGame->content->GameActor->SwitchWeapon($weaponName);
    
        $this->repackInventory();
    }
    
    function ApplyMedkitEffect()
    {
        $actor = $this->form('Client')->MainGame->content->GameActor;
    
        if ($actor->isDead())
        {
            return;
        }
    
        $healPct = 20;
        $healAmount = (int)(($actor->getMaxHP() * $healPct) / 100);
    
        $actor->heal($healAmount);
    }
}
