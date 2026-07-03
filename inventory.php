<?php
namespace app\forms;

use app\forms\classes\DimaAsyncHackEbatNaxyi;
use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent;

use app\forms\classes\Localization;
use app\forms\classes\UIProgressBarAnimator;
use app\forms\classes\UI\InventoryDragManager;
use app\forms\classes\UI\InventoryGrid;
use app\forms\classes\UI\InventoryActions;

class inventory extends AbstractForm
{
    public $contextMenu;    
    
    private $vodkaWeight = 0.5;
    private $medkitWeight = 0.1;
    private $outfitWeight = 2.0;
    private $pmWeight = 0.7;
    private $pmAmmoWeight = 0.7;
    private $ak74Weight = 5.2;
    private $ak74AmmoWeight = 0.6;
    
    private $playerMonero = 40;
    private $moneyCurrency = 'RU';
    
    public $SDK_OutfitName;
    public $SDK_OutfitIcon;
    public $SDK_OutfitPrice;
    public $SDK_OutfitWeight;
    public $SDK_OutfitDesc;
    public $SDK_VodkaName;
    public $SDK_VodkaIcon;
    public $SDK_VodkaPrice;
    public $SDK_VodkaWeight;
    public $SDK_VodkaDesc;
    
    private $dragManager;
    
    private $grid;
    private $gridLayout;
    private $actions;
    
    private $inventoryItems = [];

    public $selectedItem = null;
    public $medkitCount = 0;
    public $pmAmmoCount = 25;
    public $akAmmoCount = 69;
        
    private $gridLeft = 32;
    private $gridTop = 200;
    private $gridRight = 552;
    private $gridBottom = 904;
    
    private $weaponSlots = [
        'Pm' => [
            'item' => 'Inv_Wpn_Pm',
            'rect' => ['x'=>32, 'y'=>80, 'w'=>152, 'h'=>96],
            'pos' => [32, 80],
            'size' => [1, 1],
            'equipped' => false
        ],
    
        'AK74' => [
            'item' => 'Inv_Wpn_AK74',
            'rect' => ['x'=>208, 'y'=>80, 'w'=>245, 'h'=>96],
            'pos' => [208, 80],
            'size' => [5, 2],
            'equipped' => false
        ]
    ];      
    
    private $outfitSlotRect = ['x'=>1128, 'y'=>128, 'w'=>448, 'h'=>672];     
       
    public function __construct() 
    {
        parent::__construct();
        
        $this->grid = new InventoryGrid(11, 13); 
        $this->gridLayout = new InventoryGridLayout(
            $this->gridLeft,
            $this->gridTop,
            $this->gridRight,
            $this->gridBottom
        );
        
        $this->actions = new InventoryActions($this);
        
        $this->inventoryItems = [
            $this->Inv_Vodka,
            $this->Inv_Medkit,
            $this->Inv_Outfit,
            $this->Inv_Wpn_Pm,
            $this->Inv_Ammo_9x18,
            $this->Inv_Wpn_AK74,
            $this->Inv_Ammo_5x45
        ];   
 
        uiLater(function () {
            
            $this->dragManager = new InventoryDragManager($this);
          
            $this->contextMenu = new InventoryContextMenu($this->form('Client'), $this);
            
            if ($btn = $this->contextMenu->getButton('drop'))
            {
                $btn->on('click', function () use ($this) { $this->DropItem(); $this->HideCombobox(); });
            }
            if ($btn = $this->contextMenu->getButton('use'))
            {
                $btn->on('click', function () use ($this) { $this->UseItem(); $this->HideCombobox(); });
            }
            if ($btn = $this->contextMenu->getButton('takeOff'))
            {
                $btn->on('click', function () use ($this) { $this->TakeOffItem(); $this->HideCombobox(); });
            }
            if ($btn = $this->contextMenu->getButton('putOn'))
            {
                $btn->on('click', function () use ($this) { $this->PutOnItem(); $this->HideCombobox(); });
            }
            if ($btn = $this->contextMenu->getButton('moveToSlot'))
            {
                $btn->on('click', function () use ($this) { $this->MoveToSlot(); $this->HideCombobox(); });
            }    
        });
        
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
    
    public function getGrid()
    {
        return $this->grid;
    }
    
    public function getDragManager()
    {
        return $this->dragManager;
    }
    
    public function getGridLayout()
    {
        return $this->gridLayout;
    }
    
    public function getWeaponSlot(string $weapon)
    {
        return $this->weaponSlots[$weapon] ?? null;
    }
    
    public function getActions()
    {
        return $this->actions;
    }
    
    /**
     * @event mouseMove
     */
    function GridMouseMove(UXMouseEvent $e = null)
    {
        if ($this->dragManager->getDraggedItem() == null) return;
        
        if (!$this->dragManager->isActivated()) return;
    
        list($itemW, $itemH) = $this->itemGridSize($this->dragManager->getDraggedItem());
    
        list($cellX, $cellY) = $this->gridLayout->screenToCell($e->x, $e->y);
        list($cellX, $cellY) = $this->gridLayout->clampCell($cellX, $cellY, $itemW, $itemH, 11, 13);
    
        $item = $this->dragManager->getDraggedItem();
        
        $item->position = $this->gridLayout->getItemPosition($item, $cellX, $cellY, $itemW, $itemH);
    }
    
    /**
     * @event mouseUp-Left
     */
    function GridMouseUp(UXMouseEvent $e = null)
    {
        if (!$this->dragManager->isDragging())
        {
            return;
        }
    
        $cursor = $this->form('Client')->CustomCursor;
    
        $mouseX = $cursor->x;
        $mouseY = $cursor->y;

        if ($this->dragManager->getDraggedItem() === $this->Inv_Outfit && $this->gridLayout->isInsideGrid($mouseX, $mouseY))
        {
            $this->selectedItem = $this->Inv_Outfit;
    
            $this->TakeOffItem();
    
            $this->repackInventory();
    
            $this->UpdateInventoryStatus();
            $this->HideCombobox();
    
            $this->dragManager->endDrag();
            return;
        }
    
        if ($this->dragManager->getDraggedItem() == null) 
        {
            return;
        }
    
        if (!$this->dragManager->isActivated())
        {
            $this->dragManager->endDrag();
            return;
        }
    
        foreach ($this->weaponSlots as $weapon => $slot)
        {
            $item = $this->{$slot['item']};
    
            if ($this->dragManager->getDraggedItem() === $item && $this->pointInRect($mouseX, $mouseY, $slot['rect']))
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
    
        if ($mouseX < 0 || $mouseY < $this->gridTop ||  $mouseX >= 552 || $mouseY >= $this->gridBottom)
        {
            $this->dragManager->getDraggedItem()->position = $this->dragManager->getOriginalPosition();
    
            $this->dragManager->endDrag();
            return;
        }
    
        list($itemWidthCells, $itemHeightCells) = $this->itemGridSize($this->dragManager->getDraggedItem());
        
        list($cellX, $cellY) = $this->gridLayout->screenToCell($mouseX, $mouseY);
        list($cellX, $cellY) = $this->gridLayout->clampCell($cellX, $cellY, $itemWidthCells, $itemHeightCells, 11, 13);
    
        $oldCell = $this->grid->findItem($this->dragManager->getDraggedItem());    
        $this->grid->remove($this->dragManager->getDraggedItem());
    
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
    
        if ($this->grid->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells))
        {
            if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_Pm)
                $this->weaponSlots['Pm']['equipped'] = false;
    
            if ($this->dragManager->getDraggedItem() === $this->Inv_Wpn_AK74)
                $this->weaponSlots['AK74']['equipped'] = false;
    
            $this->placeGridItem($this->dragManager->getDraggedItem(), $cellX, $cellY, $itemWidthCells, $itemHeightCells);
    
            $this->UpdateComboboxPosition();
        }
        else
        {
            if ($oldCell !== null)
            {
                list($originalX, $originalY) = $oldCell;
            
                $this->placeGridItem($this->dragManager->getDraggedItem(), $originalX, $originalY, $itemWidthCells, $itemHeightCells);
            }
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
        $slot = $this->grid->findFreeSlot($w, $h);
        if (!$slot) return;
        
        list($cellX, $cellY) = $slot;
        if (!$this->grid->canPlace($cellX, $cellY, $w, $h)) return;
        
        $this->placeGridItem($item, $cellX, $cellY, $w, $h);
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
            $this->grid->remove($item);
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
            $this->grid->remove($item);
        }
        
        foreach ($visibleItems as $item)
        {
            list($w, $h) = $this->itemGridSize($item);
            $slot = $this->grid->findFreeSlot($w, $h);
            
            if ($slot != null)
            {
                list($x, $y) = $slot;
                $this->placeGridItem($item, $x, $y, $w, $h);
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
        $this->HideCombobox();
        
        if ($GLOBALS[$globalKey]) return;
        
        $this->UpdateSelectedItems();
        $GLOBALS[$globalKey] = true;
        
        $this->ShowUIText();
        $this->SetItemInfo();
        $this->SetItemCondition();
        
        if ($playSound) $this->UseSlotSound();
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
        
        if ($hide)
        {
            $this->HideCombobox();
        }
        else
        {
            $this->ShowCombobox();
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
        $this->UpdateSelectedItems();
        $this->HideUIText();
        $this->HideCombobox();
    }
    
    function DropItem()
    {
        $this->actions->dropItem();
    }
    
    function UseItem()
    {
        $this->actions->useItem();
    }
    
    function ApplyMedkitEffect()
    {
        $this->actions->applyMedkitEffect();
    }    
    
    function TakeOffItem()
    {
        $this->actions->takeOffItem();
    }
    
    function PutOnItem()
    {
        $this->actions->putOnItem();
    }
    
    function MoveToSlot()
    {
        if ($this->selectedItem == $this->Inv_Wpn_AK74) $this->MoveAK74ToSlot();
        if ($this->selectedItem == $this->Inv_Wpn_Pm) $this->MovePmToSlot();
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
            $free = $this->grid->findFreeSlot($slot['size'][0], $slot['size'][1]);
    
            if ($free)
            {
                list($x, $y) = $free;
    
                $this->placeGridItem($item, $x, $y, $slot['size'][0], $slot['size'][1]);
    
                $this->weaponSlots[$weaponName]['equipped'] = false;
    
                $this->form('Client')->MainGame->content->GameActor->UnequipCurrentWeapon();
            }
    
            return;
        }
    
        $this->grid->remove($item);
    
        $item->position = $slot['pos'];
        $item->visible = true;
        $item->enabled = true;
    
        $this->weaponSlots[$weaponName]['equipped'] = true;
    
        $this->form('Client')->MainGame->content->GameActor->SwitchWeapon($weaponName);
    
        $this->repackInventory();
    }
    
    function MoveWeaponsToWeaponSlot()
    {
        foreach ($this->weaponSlots as $weapon => &$slot)
        {
            $item = $this->{$slot['item']};
    
            if (!$item) continue;
    
            $this->grid->remove($item);
    
            $item->position = $slot['pos'];
            $item->visible = true;
            $item->enabled = true;
    
            $slot['equipped'] = true;
        }
    
        $this->repackInventory();
    
        $this->form('Client')->MainGame->content->GameActor->SwitchWeapon('Pm');
        $this->UseSlotSound();
        $this->HideCombobox();
    }       
        
    function UpdateInventoryHealthBar()
    {
        $actor = $this->form('Client')->MainGame->content->GameActor;
        if (!$actor) return;
    
        $pct = max(0, min(100, $actor->getHpPercent()));
    
        $min = 54;
        $maxInv = 416;
    
        $targetWidth = (int)($min + ($maxInv - $min) * ($pct / 100));
    
        $invBar = $this->health_bar_gg;
        $invBar->width = 0;
        
        UIProgressBarAnimator::resizeWidth(
            $invBar,
            $targetWidth,
            700,
            function () use ($invBar, $pct) {
                $invBar->text = $pct . '%';
            }
        );
    }
    
    function UpdateSelectedItems()
    {
        $GLOBALS['item_outfit_selected'] = false;    
        $GLOBALS['item_vodka_selected'] = false;
        $GLOBALS['item_medkit_selected'] = false;
        $GLOBALS['item_pm_selected'] = false;
        $GLOBALS['item_ammo_9x18_selected'] = false;
        $GLOBALS['item_ak74_selected'] = false;
        $GLOBALS['item_ammo_5x45_selected'] = false;
    }
    
    function UpdateInventoryStatus()
    {
        $maxWeight = 90.0;
        $baseWeight = $this->form('Client')->MainGame->content->GameActor->getWeight();
        $totalWeight = $baseWeight;

        if ($this->Inv_Vodka->visible)
        {
           $totalWeight += $this->vodkaWeight; 
        }        
        if ($this->Inv_Medkit->visible)
        {
            $totalWeight += $this->medkitWeight;
        }
        if ($this->inv_maket_visual->visible) //??
        {
            $totalWeight += $this->outfitWeight;
        }
        if ($this->Inv_Wpn_Pm->visible)
        {
           $totalWeight += $this->pmWeight; 
        }        
        if ($this->Inv_Ammo_9x18->visible)
        {
           $totalWeight += $this->pmAmmoWeight; 
        }
        if ($this->Inv_Wpn_AK74->visible)
        {
           $totalWeight += $this->ak74Weight; 
        }  
         if ($this->Inv_Ammo_5x45->visible)
        {
           $totalWeight += $this->ak74AmmoWeight; 
        }        
                       
        $WeightLabel = Localization::get('Weight_Label');
        
        $text = $WeightLabel . "  " . round($totalWeight, 1) . " / " . round($maxWeight, 1);
        $this->weight_desc->text = $text;
        
        $this->money->text = $this->playerMonero . ' ' . $this->moneyCurrency;
    }
    
    function ShowUIText()
    {
        $this->maket_label->show();
        $this->maket_count->show();
        $this->maket_desc->show();
        $this->maket_cond->show();
        $this->maket_cond_label->show();
        $this->maket_weight->show();
        $this->inv_maket->show();
    }
    function HideUIText()
    {
        $this->maket_label->hide();
        $this->maket_count->hide();
        $this->maket_desc->hide();               
        $this->maket_cond->hide();
        $this->maket_cond_label->hide();
        $this->maket_weight->hide();
        $this->inv_maket->hide();  
    }
    function SetItemInfo()
    {
        $this->inv_maket->image = null;
        $this->maket_count->text = null;
        $this->maket_weight->text = null;
        $this->maket_label->text = null;
        $this->maket_desc->text = null;        
        
        if ($GLOBALS['item_vodka_selected'])
        {
            $vodka_name = trim($this->SDK_VodkaName);
            $vodka_icon = trim($this->SDK_VodkaIcon);
            $vodka_weight = trim($this->SDK_VodkaWeight);
            $vodka_desc = trim($this->SDK_VodkaDesc);
            $vodka_price = trim($this->SDK_VodkaPrice);      
            
            $this->maket_label->text = $vodka_name != '' ? $vodka_name : Localization::get('Vodka_Inv_Name');
            $this->inv_maket->image = new UXImage($vodka_icon != '' ? $vodka_icon : 'res://.data/ui/inventory/item_vodka.png');
            $this->maket_weight->text = $vodka_weight != '' ? $vodka_weight . 'kg' : sprintf('%.1fkg', $this->vodkaWeight);
            $this->maket_desc->text = $vodka_desc != '' ? $vodka_desc : Localization::get('Vodka_Inv_Desc');
            $this->maket_count->text = ($vodka_price != '' ? $vodka_price : '250') . ' ' . $this->moneyCurrency;
        }
        if ($GLOBALS['item_outfit_selected'])
        {
            $outfit_name = trim($this->SDK_OutfitName);
            $outfit_icon = trim($this->SDK_OutfitIcon);
            $outfit_weight = trim($this->SDK_OutfitWeight);
            $outfit_desc = trim($this->SDK_OutfitDesc);
            $outfit_price = trim($this->SDK_OutfitPrice);        
            
            $this->maket_label->text = $outfit_name != '' ? $outfit_name : Localization::get('Outfit_Inv_Name');
            $this->inv_maket->image = new UXImage($outfit_icon != '' ? $outfit_icon : 'res://.data/ui/inventory/bandit_outfit.png');
            $this->maket_weight->text = $outfit_weight != '' ? $outfit_weight . 'kg' : sprintf('%.1fkg', $this->outfitWeight);
            $this->maket_desc->text = $outfit_desc != '' ? $outfit_desc : Localization::get('Outfit_Inv_Desc');
            $this->maket_count->text = ($outfit_price != '' ? $outfit_price : '2599') . ' ' . $this->moneyCurrency;
        }
        if ($GLOBALS['item_medkit_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/inventory/item_medkit.png');
            
            $this->maket_label->text = Localization::get('Medkit_Inv_Name');
            $this->maket_desc->text = Localization::get('Medkit_Inv_Desc');
            
            Element::setText($this->maket_count, "100" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->medkitWeight));
        }
        if ($GLOBALS['item_pm_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/wpn_pm.png');
            
            $this->maket_label->text = Localization::get('PM_Name');
            $this->maket_desc->text = Localization::get('PM_Desc');
            
            Element::setText($this->maket_count, "280" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->pmWeight));
        }
        if ($GLOBALS['item_ammo_9x18_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/mag_9_18.png');
            
            $this->maket_label->text = Localization::get('Ammo9x18_Name');
            $this->maket_desc->text = Localization::get('Ammo9x18_Desc');
            
            Element::setText($this->maket_count, "70" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->pmAmmoWeight));            
        }
        if ($GLOBALS['item_ak74_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/wpn_ak74.png');
            
            $this->maket_label->text = Localization::get('AK74_Name');
            $this->maket_desc->text = Localization::get('AK74_Desc');
            
            Element::setText($this->maket_count, "2000" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->ak74Weight));
        }
        if ($GLOBALS['item_ammo_5x45_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/mag_5_45.png');
            
            $this->maket_label->text = Localization::get('Ammo5x45_Name');
            $this->maket_desc->text = Localization::get('Ammo5x45_Desc');
            
            Element::setText($this->maket_count, "200" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->ak74AmmoWeight));            
        }        
    }
    
    function UseSlotSound()
    {
        if ($this->form('Client')->Inventory->visible)
        {
            DimaAsyncHackEbatNaxyi::playSfxSound('res://.data/audio/inv_slot.mp3', 'inv_use_slot'); 
        }
    }
    
    function PropertiesSound()
    {
        if ($this->form('Client')->Inventory->visible)
        {
            DimaAsyncHackEbatNaxyi::playSfxSound('res://.data/audio/inv_properties.mp3', 'inv_properties'); 
        }
    }
    
    function DropSound()
    {
        if ($this->form('Client')->Inventory->visible)
        {
            DimaAsyncHackEbatNaxyi::playSfxSound('res://.data/audio/inv_drop.mp3', 'inv_drop');
        }
    }  
    
    function OpenSound()
    {
        if ($GLOBALS['AllSounds'])
        {    
            DimaAsyncHackEbatNaxyi::playSfxSound('res://.data/audio/inv_open.mp3', 'inv_open');
        }
    }
    
    function CloseSound()
    {
        if ($GLOBALS['AllSounds'])
        {      
            DimaAsyncHackEbatNaxyi::playSfxSound('res://.data/audio/inv_close.mp3', 'inv_close');
        }        
    }
    
    /**
     * @event inv_maket_visual.click-Left 
     */
    function SelectActorMaket(UXMouseEvent $e = null)
    {
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;
    
        $this->SelectOutfit();
    }
    /**
     * @event inv_maket_visual.click-Right 
     */
    function OutfitMaketActions(UXMouseEvent $e = null)
    {    
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;
    
        $this->selectedItem = $this->Inv_Outfit;
        
        $this->ShowCombobox();
    }  
    
    /**
     * @event inv_maket_visual.mouseDrag 
     */
    function OutfitDragStart(UXMouseEvent $e = null)
    {
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;
    
        $this->dragManager->beginDrag($this->Inv_Outfit);
    }

    /**
     * @event inv_maket_visual.click-2x 
     */
    function QuickUseMaket(UXMouseEvent $e = null)
    {    
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;    
    
        $this->selectedItem = $this->Inv_Outfit;
        
        $this->TakeOffItem();
    }

    /**
     * @event Inv_Outfit.click-2x 
     */
    function QuickUseOutfit(UXMouseEvent $e = null)
    {
        $this->selectedItem = $e->sender;
        $this->PutOnItem();
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
            $this->grid->remove($this->selectedItem);
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
    
    function DespawnItems()
    {
        $this->medkitCount = 0;
        
        $this->selectedItem = $this->Inv_Outfit;
        $this->PutOnItem();
            
        $this->akAmmoCount = 60;
        $this->pmAmmoCount = 25;
                   
        $this->addVodkaToInventory();
        $this->addMedkitToInventory();
        $this->addAmmo5x45ToInventory();
        $this->addAmmo9x18ToInventory();     
        
        $this->form('Client')->MainGame->content->ItemVodka->despawn();
    }
    
    function SetItemCondition()
    {
        $this->maket_cond->width = 0;
        
        if ($GLOBALS['item_outfit_selected'])
        {
            $actor = $this->form('Client')->MainGame->content->GameActor;
            $hpPercent = $actor->getHpPercent();
    
            if ($hpPercent >= 80)
            {
                $armorPercent = 100;
                $color = '#4d804d';
            }
            elseif ($hpPercent >= 50)
            {
                $armorPercent = 67;
                $color = '#b3801a';
            }
            elseif ($hpPercent >= 30)
            {
                $armorPercent = 45;
                $color = '#b3801a';
            }
            else
            {
                $armorPercent = 13;
                $color = '#990000';
            }
    
            $this->maket_cond->text = $armorPercent . " %";
            $this->maket_cond->color = $color;
    
            $maxArmorWidth = 208;
            $minArmorWidth = 40;
            
            $target = round($minArmorWidth + (($armorPercent / 100) * ($maxArmorWidth - $minArmorWidth)));
    
            UIProgressBarAnimator::resizeWidth($this->maket_cond, $target, 700);
        }
        if ($GLOBALS['item_vodka_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }
        if ($GLOBALS['item_medkit_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }
        if ($GLOBALS['item_pm_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }
        if ($GLOBALS['item_ammo_9x18_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }
        if ($GLOBALS['item_ak74_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }
         if ($GLOBALS['item_ammo_5x45_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            
            UIProgressBarAnimator::resizeWidth($this->maket_cond, 208, 700);
        }       
    } 
     
    function ShowCombobox()
    {
        if (!$this->selectedItem) return;
        
        $this->cancelDrag();

        $this->PropertiesSound();

        $cursorPos = $this->form('Client')->CustomCursor->position;
        
        $this->contextMenu->refreshCaptions();
        
        $this->contextMenu->showForItem($this->selectedItem, $cursorPos, $this->form('Client')->MainGame->content->GameActor->isWearingOutfit());
    }

    function UpdateComboboxPosition()
    {
        if (!$this->selectedItem) return;

        $cursorPos = $this->form('Client')->CustomCursor->position;
        $this->contextMenu->updatePosition($cursorPos);
    }

    function HideCombobox()
    {
        $this->contextMenu->hide();
    }
    
    private function placeGridItem($item, $x, $y, $w, $h)
    {
        $this->grid->place($item, $x, $y, $w, $h);
    
        $item->position = $this->gridLayout->getItemPosition($item, $x, $y, $w, $h);
        $item->visible = true;
    
        $this->updateMedkitCount();
        $this->updateAmmo9x18Count();
        $this->updateAmmo5x45Count();
    }
}
