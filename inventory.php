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
        $GLOBALS['item_pm_selected'] = false;
        $GLOBALS['item_ammo_9x18_selected'] = false;
        $GLOBALS['item_ak74_selected'] = false;
        $GLOBALS['item_ammo_5x45_selected'] = false;
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
        if ($this->InventoryGrid->content->Inv_Wpn_Pm->visible)
        {
           $totalWeight += $this->pmWeight; 
        }        
        if ($this->InventoryGrid->content->Inv_Ammo_9x18->visible)
        {
           $totalWeight += $this->pmAmmoWeight; 
        }
        if ($this->InventoryGrid->content->Inv_Wpn_AK74->visible)
        {
           $totalWeight += $this->ak74Weight; 
        }  
         if ($this->InventoryGrid->content->Inv_Ammo_5x45->visible)
        {
           $totalWeight += $this->ak74AmmoWeight; 
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
        if ($GLOBALS['item_pm_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/wpn_pm.png');
            
            $this->maket_label->text = $this->localization->get('PM_Name');
            $this->maket_desc->text = $this->localization->get('PM_Desc');
            
            Element::setText($this->maket_count, "280" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->pmWeight));
        }
        if ($GLOBALS['item_ammo_9x18_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/mag_9_18.png');
            
            $this->maket_label->text = $this->localization->get('Ammo9x18_Name');
            $this->maket_desc->text = $this->localization->get('Ammo9x18_Desc');
            
            Element::setText($this->maket_count, "70" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->pmAmmoWeight));            
        }
        if ($GLOBALS['item_ak74_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/wpn_ak74.png');
            
            $this->maket_label->text = $this->localization->get('AK74_Name');
            $this->maket_desc->text = $this->localization->get('AK74_Desc');
            
            Element::setText($this->maket_count, "2000" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->ak74Weight));
        }
        if ($GLOBALS['item_ammo_5x45_selected'])
        {
            $this->inv_maket->image = new UXImage('res://.data/ui/weapons/mag_5_45.png');
            
            $this->maket_label->text = $this->localization->get('Ammo5x45_Name');
            $this->maket_desc->text = $this->localization->get('Ammo5x45_Desc');
            
            Element::setText($this->maket_count, "200" . ' ' . $this->moneyCurrency);
            Element::setText($this->maket_weight, sprintf('%.1fkg', $this->ak74AmmoWeight));            
        }        
    }
    function UseSlotSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/inv_slot.mp3', 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/inv_properties.mp3', 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('Client')->Inventory->visible)
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/inv_drop.mp3', 'inv_drop'); 
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
    function ComboboxPutOn(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->PutOnItem();
    }
    /**
     * @event Combobox_MoveToSlot.click-Left 
     */
    function ComboboxMoveToSlot(UXMouseEvent $e = null)
    {
        $selected = $this->InventoryGrid->content->selectedItem;
        
        if ($selected == $this->InventoryGrid->content->Inv_Wpn_AK74)
        {
           $this->InventoryGrid->content->MoveAK74ToSlot();  
        }
        if ($selected == $this->InventoryGrid->content->Inv_Wpn_Pm)
        {
           $this->InventoryGrid->content->MovePmToSlot();  
        }   
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
        
        $this->InventoryGrid->content->selectedItem = $this->form('Client')->Inventory->content->InventoryGrid->content->Inv_Outfit;
        $this->InventoryGrid->content->PutOnItem();
            
        $this->InventoryGrid->content->akAmmoCount = 60;
        $this->InventoryGrid->content->pmAmmoCount = 25;
                   
        $this->InventoryGrid->content->addVodkaToInventory();
        $this->InventoryGrid->content->addMedkitToInventory();
        $this->InventoryGrid->content->addAmmo5x45ToInventory();
        $this->InventoryGrid->content->addAmmo9x18ToInventory();     
        
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
            $minHPWidth = 54;
            $maxHPWidth = 264;
            $hpWidth = $this->form('Client')->MainGame->content->health_bar_gg->width;
    
            $hpPercent = round((($hpWidth - $minHPWidth) / ($maxHPWidth - $minHPWidth)) * 100);
            $hpPercent = max(1, min(100, $hpPercent));
    
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
    
            $this->form('Client')->animateResizeWidth($this->maket_cond, $target, 10);
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
        if ($GLOBALS['item_pm_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
        }
        if ($GLOBALS['item_ammo_9x18_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
        }
        if ($GLOBALS['item_ak74_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('Client')->animateResizeWidth($this->maket_cond, 208, 10);
        }
         if ($GLOBALS['item_ammo_5x45_selected'])
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

        $clientForm = $this->form('Client');

        foreach (['main', 'Combobox_Use', 'Combobox_Drop', 'Combobox_TakeOff', 'Combobox_PutOn', 'Combobox_MoveToSlot'] as $name)
        {
            $el = $this->{$name} ?? null;
            if (is_object($el) && $el->parent != $clientForm)
            {
                $clientForm->add($el);
            }
        }

        $clientForm->main->position = [$comboX, $comboY];

        foreach (['Combobox_Use', 'Combobox_Drop', 'Combobox_TakeOff', 'Combobox_PutOn', 'Combobox_MoveToSlot'] as $name)
        {
            $clientForm->{$name}->hide();
        }

        $clientForm->main->show();
        $clientForm->main->scale = $clientForm->MainGame->scale;
        $clientForm->main->toFront();

        $selected = $this->InventoryGrid->content->selectedItem;

        if ($selected == $this->InventoryGrid->content->Inv_Vodka)
        {
            $clientForm->Combobox_Drop->position = [$comboX + 8, $comboY + 8];
            $clientForm->Combobox_Drop->scale = $clientForm->MainGame->scale;
            $clientForm->Combobox_Drop->toFront();
            $clientForm->Combobox_Drop->show();
        }
        elseif ($selected == $this->InventoryGrid->content->Inv_Medkit)
        {
            $clientForm->Combobox_Use->position = [$comboX + 8, $comboY + 8];
            $clientForm->Combobox_Use->scale = $clientForm->MainGame->scale;
            $clientForm->Combobox_Use->toFront();
            $clientForm->Combobox_Use->show();
        }
        elseif ($selected == $this->InventoryGrid->content->Inv_Wpn_Pm || $selected == $this->InventoryGrid->content->Inv_Wpn_AK74)
        {
            $clientForm->Combobox_MoveToSlot->position = [$comboX + 8, $comboY + 8];
            $clientForm->Combobox_MoveToSlot->scale = $clientForm->MainGame->scale;
            $clientForm->Combobox_MoveToSlot->toFront();
            $clientForm->Combobox_MoveToSlot->show();
        }        
        elseif ($selected == $this->InventoryGrid->content->Inv_Outfit)
        {    
            $targetBtn = $this->InventoryGrid->content->isWearing ? 'Combobox_PutOn' : 'Combobox_TakeOff';
            $clientForm->{$targetBtn}->position = [$comboX + 8, $comboY + 8];
            $clientForm->{$targetBtn}->scale = $clientForm->MainGame->scale;
            $clientForm->{$targetBtn}->toFront();
            $clientForm->{$targetBtn}->show();
        }
    }
    function UpdateComboboxPosition()
    {
        if (!$this->InventoryGrid->content->selectedItem) return;
    
        list($comboX, $comboY) = $this->form('Client')->CustomCursor->position;
        $clientForm = $this->form('Client');

        if (!isset($clientForm->main) || !is_object($clientForm->main)) return;

        if (isset($clientForm->width, $clientForm->main->width))
        {
            $maxX = max(0, $clientForm->width - $clientForm->main->width);
            $comboX = max(0, min($comboX, $maxX));
        }
        
        if (isset($clientForm->height, $clientForm->main->height))
        {
            $maxY = max(0, $clientForm->height - $clientForm->main->height);
            $comboY = max(0, min($comboY, $maxY));
        }

        $clientForm->main->position = [$comboX, $comboY];

        $selected = $this->InventoryGrid->content->selectedItem;

        if ($selected == $this->InventoryGrid->content->Inv_Vodka)
        {
            if (isset($clientForm->Combobox_Drop) && is_object($clientForm->Combobox_Drop))
            {
                $clientForm->Combobox_Drop->position = [$comboX + 8, $comboY + 8];
            }
        }

        if ($selected == $this->InventoryGrid->content->Inv_Medkit)
        {
            if (isset($clientForm->Combobox_Use) && is_object($clientForm->Combobox_Use))
            {
                $clientForm->Combobox_Use->position = [$comboX + 8, $comboY + 8];
            }
        }

        if ($selected == $this->InventoryGrid->content->Inv_Wpn_Pm || $selected == $this->InventoryGrid->content->Inv_Wpn_AK74)
        {
            if (isset($clientForm->Combobox_MoveToSlot) && is_object($clientForm->Combobox_MoveToSlot))
            {
                $clientForm->Combobox_MoveToSlot->position = [$comboX + 8, $comboY + 8];
            }
        }

        if ($selected === $this->InventoryGrid->content->Inv_Outfit)
        {
            $btnName = $this->InventoryGrid->content->isWearing ? 'Combobox_TakeOff' : 'Combobox_PutOn';
            if (isset($clientForm->{$btnName}) && is_object($clientForm->{$btnName}))
            {
                $clientForm->{$btnName}->position = [$comboX + 8, $comboY + 8];
            }
        }        
    }
    function HideCombobox()
    {  
        $clientForm = $this->form('Client');

        if (isset($clientForm->main) && is_object($clientForm->main))
        {
            $clientForm->main->hide();
        }

        $selected = $this->InventoryGrid->content->selectedItem;

        if ($selected == $this->InventoryGrid->content->Inv_Medkit)
        {
            if (isset($clientForm->Combobox_Use) && is_object($clientForm->Combobox_Use))
            {
                $clientForm->Combobox_Use->hide();
            }
        }

        if ($selected == $this->InventoryGrid->content->Inv_Vodka)
        {
            if (isset($clientForm->Combobox_Drop) && is_object($clientForm->Combobox_Drop))
            {
                $clientForm->Combobox_Drop->hide();
            }
        }
        
        if ($selected == $this->InventoryGrid->content->Inv_Wpn_Pm || $selected == $this->InventoryGrid->content->Inv_Wpn_AK74)
        {
            if (isset($clientForm->Combobox_MoveToSlot) && is_object($clientForm->Combobox_MoveToSlot))
            {
                $clientForm->Combobox_MoveToSlot->hide();
            }
        }        

        if ($selected == $this->InventoryGrid->content->Inv_Outfit)
        {
            foreach (['Combobox_TakeOff', 'Combobox_PutOn'] as $btn)
            {
                if (isset($clientForm->{$btn}) && is_object($clientForm->{$btn}))
                {
                    $clientForm->{$btn}->hide();
                }
            }
        }     
    }        
}
