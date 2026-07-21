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

    private $gridLeft = 32;
    private $gridTop = 200;
    private $gridRight = 552;
    private $gridBottom = 904;
    
    private $weaponSlots = [
        'wpn_pm' => [
            'item' => 'Inv_Wpn_Pm',
            'rect' => ['x'=>32, 'y'=>80, 'w'=>152, 'h'=>96],
            'pos' => [32, 80],
            'size' => [1, 1],
            'equipped' => false
        ],
    
        'wpn_ak74' => [
            'item' => 'Inv_Wpn_AK74',
            'rect' => ['x'=>208, 'y'=>80, 'w'=>245, 'h'=>96],
            'pos' => [208, 80],
            'size' => [5, 2],
            'equipped' => false
        ]
    ];      
    
    private $outfitSlotRect = ['x'=>1128, 'y'=>128, 'w'=>448, 'h'=>672];     
       
    //CITEM FOR MP UPDATE
    private $items = [];       
    private $selectedItemData = null;     
    
    public function __construct() 
    {
        parent::__construct();    
        
        $this->items = [
            'vodka'      => new CVodka(),
            'medkit'     => new CMedkit(),
            'outfit'     => new COutfit(),
            'wpn_pm'     => CWeaponFactory::create('wpn_pm', $actor),
            'wpn_ak74'   => CWeaponFactory::create('wpn_ak74', $actor),
            'ammo_9x18'  => new CAmmo9x18(),
            'ammo_5x45'  => new CAmmo5x45(),
        ];
        
        $itemMap = [
            'vodka'      => $this->Inv_Vodka,
            'medkit'     => $this->Inv_Medkit,
            'outfit'     => $this->Inv_Outfit,
            'wpn_pm'     => $this->Inv_Wpn_Pm,
            'wpn_ak74'   => $this->Inv_Wpn_AK74,
            'ammo_9x18'  => $this->Inv_Ammo_9x18,
            'ammo_5x45'  => $this->Inv_Ammo_5x45,
        ];
        
        foreach ($itemMap as $id => $ui)
        {
            $this->items[$id]->setUIItem($ui);
        }         
            
        $this->grid = new InventoryGrid(11, 13); 
        $this->gridLayout = new InventoryGridLayout(
            $this->gridLeft,
            $this->gridTop,
            $this->gridRight,
            $this->gridBottom
        );
        
        $this->actions = new InventoryActions($this);
          
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
            
            $this->addItem('vodka');
            $this->addItem('medkit', 2);
            $this->addItem('ammo_9x18', 25);
            $this->addItem('ammo_5x45', 60);   
            
            $this->items['medkit']->setUICountLabel($this->Inv_Medkit_Count);
            $this->items['ammo_9x18']->setUICountLabel($this->Inv_PmAmmo_Count);
            $this->items['ammo_5x45']->setUICountLabel($this->Inv_AkAmmo_Count);                        
        });        
    }
    
    function InitItems()
    {
           
    }    
    
    private function pointInRect($x, $y, $r): bool
    {
        return $x >= $r['x'] && $x < ($r['x'] + $r['w']) && $y >= $r['y'] && $y < ($r['y'] + $r['h']);
    }
    
    public function getItem(string $id): ?CItem
    {
        return $this->items[$id] ?? null;
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
        if ($this->dragManager->getDraggedUIItem() == null) return;
        
        if (!$this->dragManager->isActivated()) return;
    
        $item = $this->findItemByUI($this->dragManager->getDraggedUIItem());
        
        $itemW = $item->getGridWidth();
        $itemH = $item->getGridHeight();
    
        list($cellX, $cellY) = $this->gridLayout->screenToCell($e->x, $e->y);
        list($cellX, $cellY) = $this->gridLayout->clampCell($cellX, $cellY, $itemW, $itemH, 11, 13);
    
        $item = $this->dragManager->getDraggedUIItem();
        
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

        if ($this->dragManager->getDraggedUIItem() === $this->Inv_Outfit && $this->gridLayout->isInsideGrid($mouseX, $mouseY))
        {
            $this->selectedItemData = $this->items['outfit'];
    
            $this->TakeOffItem();
    
            $this->repackInventory();
    
            $this->UpdateInventoryStatus();
            $this->HideCombobox();
    
            $this->dragManager->endDrag();
            return;
        }
    
        if ($this->dragManager->getDraggedUIItem() == null) 
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
    
            if ($this->dragManager->getDraggedUIItem() === $item && $this->pointInRect($mouseX, $mouseY, $slot['rect']))
            {
                $this->moveWeaponToSlot($weapon);
    
                $this->dragManager->endDrag();
                return;
            }
        }
    
        if ($this->dragManager->getDraggedUIItem() === $this->Inv_Outfit && $this->pointInRect($mouseX, $mouseY, $this->outfitSlotRect))
        {
            $this->selectedItemData = $this->items['outfit'];
    
            $this->PutOnItem();
    
            $this->dragManager->endDrag();
            return;
        }
    
        if ($mouseX < 0 || $mouseY < $this->gridTop ||  $mouseX >= 552 || $mouseY >= $this->gridBottom)
        {
            $this->dragManager->getDraggedUIItem()->position = $this->dragManager->getOriginalPosition();
    
            $this->dragManager->endDrag();
            return;
        }
    
        $itemData = $this->findItemByUI($this->dragManager->getDraggedUIItem());
        
        $itemWidthCells = $itemData->getGridWidth();
        $itemHeightCells = $itemData->getGridHeight();
        
        list($cellX, $cellY) = $this->gridLayout->screenToCell($mouseX, $mouseY);
        list($cellX, $cellY) = $this->gridLayout->clampCell($cellX, $cellY, $itemWidthCells, $itemHeightCells, 11, 13);
    
        $oldCell = $this->grid->findItem($this->dragManager->getDraggedUIItem());    
        $this->grid->remove($this->dragManager->getDraggedUIItem());
    
        if ($this->dragManager->getDraggedUIItem() === $this->Inv_Wpn_Pm && $this->weaponSlots['wpn_pm']['equipped'])
        {
            $actor = $this->form('Client')->MainGame->content->GameActor;
    
            $w = $actor->getWeapon();
    
            if ($w && $w->getType() === 'wpn_pm')
            {
                $actor->UnequipCurrentWeapon();
            }
    
            $this->weaponSlots['wpn_pm']['equipped'] = false;
        }
    
        if ($this->dragManager->getDraggedUIItem() === $this->Inv_Wpn_AK74 && $this->weaponSlots['wpn_ak74']['equipped'])
        {
            $actor = $this->form('Client')->MainGame->content->GameActor;
    
            $w = $actor->getWeapon();
    
            if ($w && $w->getType() === 'wpn_ak74')
            {
                $actor->UnequipCurrentWeapon();
            }
    
            $this->weaponSlots['wpn_ak74']['equipped'] = false;
        }
    
        if ($this->grid->canPlace($cellX, $cellY, $itemWidthCells, $itemHeightCells))
        {
            if ($this->dragManager->getDraggedUIItem() === $this->Inv_Wpn_Pm)
                $this->weaponSlots['wpn_pm']['equipped'] = false;
    
            if ($this->dragManager->getDraggedUIItem() === $this->Inv_Wpn_AK74)
                $this->weaponSlots['wpn_ak74']['equipped'] = false;
    
            $this->placeGridItem($this->dragManager->getDraggedUIItem(), $cellX, $cellY, $itemWidthCells, $itemHeightCells);
    
            $this->UpdateComboboxPosition();
        }
        else
        {
            if ($oldCell !== null)
            {
                list($originalX, $originalY) = $oldCell;
            
                $this->placeGridItem($this->dragManager->getDraggedUIItem(), $originalX, $originalY, $itemWidthCells, $itemHeightCells);
            }
        }
    
        $this->dragManager->endDrag();
    }  
 
    /** @event Inv_Vodka.mouseDrag */
    function VodkaMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Medkit.mouseDrag */
    function MedkitMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Outfit.mouseDrag */
    function OutfitMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Wpn_Pm.mouseDrag */
    function PmMouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Wpn_AK74.mouseDrag */
    function Ak74MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Ammo_9x18.mouseDrag */
    function Ammo9x18MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }
    
    /** @event Inv_Ammo_5x45.mouseDrag */
    function Ammo5x45MouseDown(UXMouseEvent $e = null) { $this->dragManager->beginDrag($this->findItemByUI($e->sender)); }    
    
    public function addItem(string $id, int $count = 1): void
    {
        if (!isset($this->items[$id]))
        {
            return;
        }
    
        $item = $this->items[$id];
    
        $wasEmpty = $item->isEmpty();
    
        $item->setCount($count);
    
        if ($wasEmpty)
        {
            $this->addItemToInventory($item);
        }
    
        $this->updateItemCount($item);
        $this->UpdateInventoryStatus();
    }
    
    private function addItemToInventory(CItem $item)
    {
        $ui = $item->getUIItem();
    
        $w = $item->getGridWidth();
        $h = $item->getGridHeight();
    
        $slot = $this->grid->findFreeSlot($w, $h);
        if (!$slot) return;
    
        list($cellX, $cellY) = $slot;
    
        if (!$this->grid->canPlace($cellX, $cellY, $w, $h))
        {
            return;
        }
    
        $this->placeGridItem($ui, $cellX, $cellY, $w, $h);
    }
   
    public function updateItemCount(CItem $item)
    {
        $label = $item->getUICountLabel();
    
        if (!$label) return;
           
        $count = $item->getCount();
    
        $label->position = [$item->getUIItem()->x, $item->getUIItem()->y];
    
        $label->visible = $count > 1;
    
        if ($count > 1)
        {
            $label->text = "x{$count}";
        }
    
        if ($item->isEmpty())
        {
            $this->grid->remove($item->getUIItem());
            $item->getUIItem()->visible = false;
        }
    } 
       
    public function updateAllItemCounts()
    {
        foreach ($this->items as $item)
        {
            $this->updateItemCount($item);
        }
    }    
    
    private function updateCountLabel(CItem $item, $label)
    {
        $ui = $item->getUIItem();
        $count = $item->getCount();
    
        $label->position = [$ui->x, $ui->y];
    
        if ($count > 1)
        {
            $label->text = 'x' . $count;
            $label->visible = true;
        }
        else
        {
            Logger::error('пидорас');
            $label->visible = false;
        }
    
        if ($count <= 0)
        {
            Logger::error('пидорас');
            $this->grid->remove($ui);
            $ui->visible = false;
        }
    }
      
    
    function repackInventory()
    {
        $visibleItems = [];
    
        foreach ($this->items as $id => $item)
        {
            $ui = $item->getUIItem();
    
            if (!$ui || !$ui->visible)
            {
                continue;
            }
    
            if (($id === 'wpn_pm'   && $this->weaponSlots['wpn_pm']['equipped']) || ($id === 'wpn_ak74' && $this->weaponSlots['wpn_ak74']['equipped']))
            {
                continue;
            }
    
            $visibleItems[] = $item;
        }
    
        foreach ($visibleItems as $item)
        {
            $this->grid->remove($item->getUIItem());
        }
    
        foreach ($visibleItems as $item)
        {
            $w = $item->getGridWidth();
            $h = $item->getGridHeight();
    
            $slot = $this->grid->findFreeSlot($w, $h);
    
            if ($slot)
            {
                list($x, $y) = $slot;
    
                $this->placeGridItem($item->getUIItem(), $x, $y, $w, $h);
            }
            else
            {
                $item->getUIItem()->visible = false;
            }
        }
    }  
    
    /** @event Inv_Vodka.click-Left */
    function SelectVodka(UXMouseEvent $e = null) { $this->selectItem('vodka'); }
    
    /** @event Inv_Medkit.click-Left */
    function SelectMedkit(UXMouseEvent $e = null) { $this->selectItem('medkit', $e->clickCount <= 2); }
    
    /** @event Inv_Outfit.click-Left */
    function SelectOutfit(UXMouseEvent $e = null)
    {
        if ($e && $e->clickCount >= 2) return;
        $this->selectItem('outfit');
    }
    
    /** @event Inv_Wpn_Pm.click-Left */
    function SelectPm(UXMouseEvent $e = null) { $this->selectItem('wpn_pm', $e->clickCount <= 2); }
    
    /** @event Inv_Wpn_AK74.click-Left */
    function SelectAk74(UXMouseEvent $e = null) { $this->selectItem('wpn_ak74', $e->clickCount <= 2); }
    
    /** @event Inv_Ammo_9x18.click-Left */
    function SelectAmmo9x18(UXMouseEvent $e = null) { $this->selectItem('ammo_9x18'); }
    
    /** @event Inv_Ammo_5x45.click-Left */
    function SelectAmmo5x45(UXMouseEvent $e = null) { $this->selectItem('ammo_5x45'); }

    public function selectItem(string $itemId, bool $playSound = true)
    {
        $this->HideCombobox();
    
        if (!isset($this->items[$itemId]))
        {
            return;
        }
    
        $this->selectedItemData = $this->items[$itemId];
    
        $this->ShowUIText();
        $this->SetItemInfo();
        $this->SetItemCondition();
    
        if ($playSound)
        {
            $this->UseSlotSound();
        }
    }
    
    public function getSelectedItem(): ?CItem
    {
        return $this->selectedItemData;
    }
    
    public function setSelectedItem(?CItem $item): void
    {
        $this->selectedItemData = $item;
    }    
    
    public function clearSelectedItem(): void
    {
        $this->selectedItemData = null;
    }
    
    private function findItemByUI($uiItem): ?CItem
    {
        foreach ($this->items as $item)
        {
            if ($item->getUIItem() === $uiItem)
            {
                return $item;
            }
        }
    
        return null;
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
        $this->selectedItemData = $this->findItemByUI($item);
        
        if (!$this->selectedItemData)
        {
            return;
        }        
        
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
    
    function UpdateSelectedItems() {}
    
    private function updateGridUI()
    {
        $this->clearSelectedItem();
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
        if (!$this->selectedItemData)
        {
            return;
        }
    
        if ($this->selectedItemData->getId() === 'wpn_ak74') $this->MoveAK74ToSlot();
        if ($this->selectedItemData->getId() === 'wpn_pm') $this->MovePmToSlot();
    }
     
    /** @event Inv_Wpn_Pm.click-2x */
    function MovePmToSlot(UXMouseEvent $e = null) { $this->moveWeaponToSlot('wpn_pm'); }
    
    /** @event Inv_Wpn_AK74.click-2x */
    function MoveAK74ToSlot(UXMouseEvent $e = null) { $this->moveWeaponToSlot('wpn_ak74'); }
        
    function moveWeaponToSlot(string $weaponName)
    {
        $slot = $this->getWeaponSlot($weaponName);
        if (!$slot) return;
    
        $item = $this->{$slot['item']};
    
        //$this->selectedItem = $item;
        //$this->selectedItemData = $this->findItemByUI($item);
    
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
    
        $this->form('Client')->MainGame->content->GameActor->SwitchWeapon('wpn_pm');
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
    
    function UpdateInventoryStatus()
    {
        $maxWeight = 90.0;
        $baseWeight = $this->form('Client')->MainGame->content->GameActor->getWeight();
        $totalWeight = $baseWeight;
    
        foreach ($this->items as $item)
        {
            $ui = $item->getUIItem();
    
            if ($ui && $ui->visible)
            {
                $totalWeight += $item->getWeight();
            }
        }
    
        $weightLabel = Localization::get('Weight_Label');
    
        $this->weight_desc->text = sprintf('%s %.1f / %.1f', $weightLabel, $totalWeight, $maxWeight);
    
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
        if (!$this->selectedItemData)
        {
            return;
        }
    
        $item = $this->selectedItemData;
    
        $this->maket_label->text = $item->getName();
        $this->maket_desc->text = $item->getDescription();
        $this->maket_weight->text = $item->getWeight() . 'kg';
        $this->maket_count->text = $item->getPrice() . ' ' . $this->moneyCurrency;
        $this->inv_maket->image = new UXImage($item->getIcon());
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
    
        $this->selectedItemData = $this->items['outfit'];
        
        $this->ShowCombobox();
    }  
    
    /**
     * @event inv_maket_visual.mouseDrag 
     */
    function OutfitDragStart(UXMouseEvent $e = null)
    {
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;
    
        $this->dragManager->beginDrag($this->findItemByUI($this->Inv_Outfit));
    }

    /**
     * @event inv_maket_visual.click-2x 
     */
    function QuickUseMaket(UXMouseEvent $e = null)
    {    
        if (!$this->form('Client')->MainGame->content->GameActor->isWearingOutfit()) return;    
    
        $this->selectedItemData = $this->findItemByUI($this->Inv_Outfit);
        
        $this->TakeOffItem();
    }

    /**
     * @event Inv_Outfit.click-2x 
     */
    function QuickUseOutfit(UXMouseEvent $e = null)
    {
        $this->selectedItemData = $this->findItemByUI($e->sender);
        $this->PutOnItem();
    }

    /**
     * @event Inv_Medkit.click-2x 
     */
    function QuickUseMedkit(UXMouseEvent $e = null)
    {
        $this->selectedItemData = $this->findItemByUI($e->sender);
        
        $this->ApplyMedkitEffect();
        $this->form('Client')->Inventory->content->UseSlotSound();
        
        $item = $this->items['medkit'];
        
        $item->removeCount();
        
        if ($item->isEmpty())
        {
            $ui = $item->getUIItem();
        
            $this->grid->remove($ui);
            $ui->visible = false;
        
            $this->repackInventory();
        
            $this->UpdateInventoryStatus();
            $this->HideUIText();
            $this->HideCombobox();
        }
        
        $this->updateItemCount($item);
        
        $this->clearSelectedItem();
    }
    
    function DespawnItems()
    {
        $this->items['medkit']->setCount(0);
        $this->updateItemCount($this->items['medkit']);
        
        $this->items['ammo_9x18']->setCount(0);
        $this->updateItemCount($this->items['ammo_9x18']);
        
        $this->items['ammo_5x45']->setCount(0);
        $this->updateItemCount($this->items['ammo_5x45']);
        
        $this->setSelectedItem($this->items['outfit']);
        $this->PutOnItem();
        
        $this->addItem('vodka');
        $this->addItem('medkit', 2);
        $this->addItem('ammo_5x45', 60);
        $this->addItem('ammo_9x18', 25);
        
        $this->form('Client')->MainGame->content->ItemVodka->despawn();
    }
    
    function SetItemCondition()
    {
        if (!$this->selectedItemData)
        {
            return;
        }
        
        $this->maket_cond->width = 0;
    
        $condition = $this->selectedItemData->getCondition();
    
        if ($condition >= 80)
        {
            $color = '#4d804d';
        }
        elseif ($condition >= 30)
        {
            $color = '#b3801a';
        }
        else
        {
            $color = '#990000';
        }
    
        $this->maket_cond->text = $condition . ' %';
        $this->maket_cond->color = $color;
    
        $maxWidth = 208;
        $minWidth = 40;
    
        $target = round($minWidth + (($condition / 100) * ($maxWidth - $minWidth)));
    
        UIProgressBarAnimator::resizeWidth($this->maket_cond, $target, 700);
    }
     
    function ShowCombobox()
    {
        if (!$this->selectedItemData) return;
        
        $this->cancelDrag();

        $this->PropertiesSound();

        $cursorPos = $this->form('Client')->CustomCursor->position;
        
        $this->contextMenu->refreshCaptions();
        
        $this->contextMenu->showForItem($this->selectedItemData, $cursorPos, $this->form('Client')->MainGame->content->GameActor->isWearingOutfit());
    }

    function UpdateComboboxPosition()
    {
        if (!$this->selectedItemData) return;

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
    
        $this->updateAllItemCounts();
    }
}
