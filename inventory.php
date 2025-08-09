<?php
namespace app\forms;

use app\forms\InventoryGrid;
use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 
use app\forms\classes\Localization;

class inventory extends AbstractForm
{
    private $localization;
    
    private $vodkaWeight = 0.5;
    private $medkitWeight = 0.1;
    private $outfitWeight = 2.0;
    
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
       
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }
    
    function UpdateSelectedItems()
    {
        $GLOBALS['item_outfit_selected'] = false;    
        $GLOBALS['item_vodka_selected'] = false;
        $GLOBALS['item_medkit_selected'] = false;
    }
    function UpdateInventoryStatus()
    {
        $maxWeight = 90.0;
        $baseWeight = 50.0;
        $totalWeight = $baseWeight;

        if ($this->InventoryGrid->content->Inv_Vodka->visible)
        {
           $totalWeight += $this->vodkaWeight; 
        }
        if ($this->InventoryGrid->content->Inv_Medkit->visible)
        {
            $totalWeight += $this->medkitWeight;
        }
        if ($this->inv_maket_visual->visible)
        {
            $totalWeight += $this->outfitWeight;
        }
            
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
        $WeightLabel = $this->localization->get('Weight_Label');
        
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
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
          
        if ($GLOBALS['item_vodka_selected'])
        {
            $vodka_name = trim($this->SDK_VodkaName);
            $vodka_icon = trim($this->SDK_VodkaIcon);
            $vodka_weight = trim($this->SDK_VodkaWeight);
            $vodka_desc = trim($this->SDK_VodkaDesc);
            $vodka_price = trim($this->SDK_VodkaPrice);      
            
            $this->maket_label->text = $vodka_name != '' ? $vodka_name : $this->localization->get('Vodka_Inv_Name');
            $this->inv_maket->image = new UXImage($vodka_icon != '' ? $vodka_icon : 'res://.data/ui/inventory/item_vodka.png');
            $this->maket_weight->text = $vodka_weight != '' ? $vodka_weight . 'kg' : sprintf('%.1fkg', $this->vodkaWeight);
            $this->maket_desc->text = $vodka_desc != '' ? $vodka_desc : $this->localization->get('Vodka_Inv_Desc');
            $this->maket_count->text = ($vodka_price != '' ? $vodka_price : '250') . ' ' . $this->moneyCurrency;
        }
        if ($GLOBALS['item_outfit_selected'])
        {
            $outfit_name = trim($this->SDK_OutfitName);
            $outfit_icon = trim($this->SDK_OutfitIcon);
            $outfit_weight = trim($this->SDK_OutfitWeight);
            $outfit_desc = trim($this->SDK_OutfitDesc);
            $outfit_price = trim($this->SDK_OutfitPrice);        
            
            $this->maket_label->text = $outfit_name != '' ? $outfit_name : $this->localization->get('Outfit_Inv_Name');
            $this->inv_maket->image = new UXImage($outfit_icon != '' ? $outfit_icon : 'res://.data/ui/inventory/bandit_outfit.png');
            $this->maket_weight->text = $outfit_weight != '' ? $outfit_weight . 'kg' : sprintf('%.1fkg', $this->outfitWeight);
            $this->maket_desc->text = $outfit_desc != '' ? $outfit_desc : $this->localization->get('Outfit_Inv_Desc');
            $this->maket_count->text = ($outfit_price != '' ? $outfit_price : '2599') . ' ' . $this->moneyCurrency;
        }
        if ($GLOBALS['item_medkit_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/inventory/item_medkit.png');
            
            $this->maket_label->text = $this->localization->get('Medkit_Inv_Name');
            $this->maket_desc->text = $this->localization->get('Medkit_Inv_Desc');
            
            Element::setText($this->maket_count, "100" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->medkitWeight));
        }
    }
    function UseSlotSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_slot.mp3', true, 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_properties.mp3', true, 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_drop.mp3', true, 'inv_drop'); 
        }               
    }    
    /**
     * @event inv_maket_visual.click-Left 
     */
    function SelectActorMaket(UXMouseEvent $e = null)
    {
        if ($this->InventoryGrid->content->isWearing) return;
    
        $this->InventoryGrid->content->SelectOutfit();
    }
    /**
     * @event inv_maket_visual.click-Right 
     */
    function OutfitActions(UXMouseEvent $e = null)
    {    
        if ($this->InventoryGrid->content->isWearing) return;
    
        $this->InventoryGrid->content->selectedItem = $this->InventoryGrid->content->Inv_Outfit; // СИТУАЦИЯ
        
        $this->ShowCombobox();
    }    
    /**
     * @event Combobox_Drop.click-Left 
     */
    function ComboboxDrop(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->DropItem(); 
    }
    /**
     * @event Combobox_Use.click-Left 
     */
    function ComboboxUse(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->UseItem();
    }
    /**
     * @event Combobox_TakeOff.click-Left 
     */
    function ComboboxTakeOff(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->TakeOffItem();
    }
    /**
     * @event Combobox_PutOn.click-Left 
     */
    function Combobox_PutOn(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->PutOnItem();
    }
    /**
     * @event inv_maket_visual.click-2x 
     */
    function QuickUseMaket(UXMouseEvent $e = null)
    {    
        if ($this->InventoryGrid->content->isWearing) return;    
    
        $this->InventoryGrid->content->selectedItem = $this->InventoryGrid->content->Inv_Outfit;
        
        $this->InventoryGrid->content->TakeOffItem();
    }
    function DespawnItems()
    {
        $this->InventoryGrid->content->medkitCount = 0;
        
        $this->InventoryGrid->content->addVodkaToInventory();
        $this->InventoryGrid->content->addMedkitToInventory();
        
        $this->form('Client')->MainGame->content->item_vodka_0000->hide();
        $this->form('Client')->MainGame->content->item_vodka_0000->enabled = true;
        $this->form('Client')->MainGame->content->item_vodka_0000->opacity = 100;
        $this->form('Client')->MainGame->content->item_vodka_0000->position = [256,696];
    }
    function SetItemCondition()
    {
        $this->maket_cond->width = 0;
        
        if ($GLOBALS['item_outfit_selected'])
        {
            if ($this->form('Client')->MainGame->content->health_bar_gg->width == 264) // Дефолтный размер health bar, без наподобности в функции ResetOutfitCondition
            {
                $this->maket_cond->text = "100 %";
                $this->maket_cond->color = '#4d804d';
                $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
            }
            if ($this->form('Client')->MainGame->content->health_bar_gg->width == 204 || $this->form('Client')->MainGame->content->health_bar_gg->width == 234)
            {
                $this->maket_cond->text = "82 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('Client')->animateResizeWidth($this->maket_cond, 168, 10);
            }
            if ($this->form('Client')->MainGame->content->health_bar_gg->width == 174 || $this->form('Client')->MainGame->content->health_bar_gg->width == 144)
            {
                $this->maket_cond->text = "67 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('Client')->animateResizeWidth($this->maket_cond, 138, 10);
            }
            if ($this->form('Client')->MainGame->content->health_bar_gg->width == 114)
            {
                $this->maket_cond->text = "45 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('Client')->animateResizeWidth($this->maket_cond, 118, 10);
            }
            if ($this->form('Client')->MainGame->content->health_bar_gg->width == 84 || $this->form('Client')->MainGame->content->health_bar_gg->width == 54)
            {
                $this->maket_cond->text = "13 %";
                $this->maket_cond->color = '#990000';
                $this->form('Client')->animateResizeWidth($this->maket_cond, 74, 10);
            }     
        }
        if ($GLOBALS['item_vodka_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
        }
        if ($GLOBALS['item_medkit_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
        }        
    }  
    function ShowCombobox()
    {
        if (!$this->InventoryGrid->content->selectedItem) return;

        $this->PropertiesSound();

        list($comboX, $comboY) = $this->form('Client')->CustomCursor->position;
        $offsetY = 8;

        $clientForm = $this->form('Client');

        foreach (['main', 'Combobox_Use', 'Combobox_Drop', 'Combobox_TakeOff', 'Combobox_PutOn'] as $name)
        {
            $el = $this->{$name} ?? null;
            if (is_object($el) && $el->parent != $clientForm)
            {
                $clientForm->add($el);
            }
        }

        $clientForm->main->position = [$comboX, $comboY];

        foreach (['Combobox_Use', 'Combobox_Drop', 'Combobox_TakeOff', 'Combobox_PutOn'] as $name)
        {
            $clientForm->{$name}->hide();
        }

        $clientForm->main->show();
        $clientForm->main->toFront();

        $selected = $this->InventoryGrid->content->selectedItem;

        if ($selected == $this->InventoryGrid->content->Inv_Vodka)
        {
            $clientForm->Combobox_Drop->position = [$comboX + 8, $comboY + $offsetY];
            $clientForm->Combobox_Drop->toFront();
            $clientForm->Combobox_Drop->show();
        }
        elseif ($selected == $this->InventoryGrid->content->Inv_Medkit)
        {
            $clientForm->Combobox_Use->position = [$comboX + 8, $comboY + $offsetY];
            $clientForm->Combobox_Use->toFront();
            $clientForm->Combobox_Use->show();
        }
        elseif ($selected == $this->InventoryGrid->content->Inv_Outfit)
        {    
            $targetBtn = $this->InventoryGrid->content->isWearing ? 'Combobox_PutOn' : 'Combobox_TakeOff';
            $clientForm->{$targetBtn}->position = [$comboX + 8, $comboY + $offsetY];
            $clientForm->{$targetBtn}->toFront();
            $clientForm->{$targetBtn}->show();
        }
    }
    function UpdateComboboxPosition()
    {
        if (!$this->InventoryGrid->content->selectedItem) return;

        list($comboX, $comboY) = $this->form('Client')->CustomCursor->position;

        $clientForm = $this->form('Client');
    
        $clientForm->main->position = [$comboX, $comboY];
    
        $offsetX = 8;
        $offsetY = 8;
        
        $selected = $this->InventoryGrid->content->selectedItem;

        if ($selected == $this->InventoryGrid->content->Inv_Vodka)
        {
            $clientForm->Combobox_Drop->position = [$comboX + $offsetX, $comboY + $offsetY];
        }

        if ($selected == $this->InventoryGrid->content->Inv_Medkit)
        {
            $clientForm->Combobox_Use->position = [$comboX + $offsetX, $comboY + $offsetY];
        }
    
        if ($selected == $this->InventoryGrid->content->Inv_Outfit) 
        {
            if (!$this->InventoryGrid->content->isWearing)
            {
                $clientForm->Combobox_TakeOff->position = [$comboX + $offsetX, $comboY + $offsetY];
            }
            else
            {
                $clientForm->Combobox_PutOn->position = [$comboX + $offsetX, $comboY + $offsetY];
            }
        }        
    }
    function HideCombobox()
    {  
        $clientForm = $this->form('Client');
        
        $clientForm->main->hide();
        
        if ($this->InventoryGrid->content->selectedItem == $this->InventoryGrid->content->Inv_Medkit)
        {
            $clientForm->Combobox_Use->hide();
        }
        if ($this->InventoryGrid->content->selectedItem == $this->InventoryGrid->content->Inv_Vodka)
        {
            $clientForm->Combobox_Drop->hide();
        }
        if ($this->InventoryGrid->content->selectedItem == $this->InventoryGrid->content->Inv_Outfit)
        {       
            $clientForm->Combobox_TakeOff->hide();
            $clientForm->Combobox_PutOn->hide(); 
        }        
    }        
}
