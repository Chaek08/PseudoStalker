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
       
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
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
            
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);        
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
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $this->inv_maket->image = null;
        $this->maket_count->text = null;
        $this->maket_weight->text = null;
        $this->maket_label->text = null;
        $this->maket_desc->text = null;        
        
        if ($GLOBALS['item_vodka_selected'])
        {
            if (SDK_Mode)
            {
                $this->inv_maket->image = new UXImage($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemIcon_Vodka->text);
                $this->maket_label->text =
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka->text ?:
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka->promptText;

                $this->maket_desc->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Vodka->text ?:
                    $this->localization->get('Vodka_Inv_Desc');
                    
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Vodka) . ' ' . $this->moneyCurrency);
                
                $this->vodkaWeight = (float) uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Vodka);
                Element::setText($this->maket_weight, sprintf('%.1fkg', $this->vodkaWeight));
            }
            else 
            {
                $this->inv_maket->image = new UXImage('res://.data/ui/inventory/item_vodka.png');
                $this->maket_label->text = $this->localization->get('Vodka_Inv_Name');
                $this->maket_desc->text = $this->localization->get('Vodka_Inv_Desc');
                
                Element::setText($this->maket_count, "250" .  ' ' . $this->moneyCurrency);
                Element::setText($this->maket_weight, sprintf('%.1fkg', $this->vodkaWeight));
            }
        }
        if ($GLOBALS['item_outfit_selected'])
        {
            if (SDK_Mode)
            {
                $this->inv_maket->image = new UXImage($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemIcon_Outfit->text);
                
                $this->maket_label->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit->text ?: 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit->promptText;

                $this->maket_desc->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit->text ?: 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit->promptText;
                    
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Outfit) . ' ' . $this->moneyCurrency);                
                    
                $this->outfitWeight = (float) uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Outfit);
                Element::setText($this->maket_weight, sprintf('%.1fkg', $this->outfitWeight));
            }
            else 
            {
                $this->inv_maket->image = new UXImage('res://.data/ui/inventory/bandit_outfit.png');
                
                $this->maket_label->text = $this->localization->get('Outfit_Inv_Name');
                $this->maket_desc->text = $this->localization->get('Outfit_Inv_Desc');
                
                Element::setText($this->maket_count, "2599" . ' ' . $this->moneyCurrency);
                Element::setText($this->maket_weight, sprintf('%.1fkg', $this->outfitWeight));
            }
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
        if ($GLOBALS['AllSounds'] && $this->form('maingame')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_slot.mp3', true, 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('maingame')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_properties.mp3', true, 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($GLOBALS['AllSounds'] && $this->form('maingame')->Inventory->visible)
        {
            Media::open('res://.data/audio/inv_drop.mp3', true, 'inv_drop'); 
        }               
    }    
    /**
     * @event inv_maket_visual.click-Left 
     */
    function SelectOutfit(UXMouseEvent $e = null)
    {
        $this->InventoryGrid->content->HideCombobox();
        
        if ($GLOBALS['item_outfit_selected']) return;
        
        $this->UpdateSelectedItems();
        $GLOBALS['item_outfit_selected'] = true;
        
        $this->ShowUIText();
        $this->SetItemInfo();
        $this->SetItemCondition();
        
        $this->UseSlotSound();       
    }
    function DespawnItems()
    {
        $this->InventoryGrid->content->medkitCount = 0;
        
        $this->InventoryGrid->content->addVodkaToInventory();
        $this->InventoryGrid->content->addMedkitToInventory();
        
        $this->form('maingame')->item_vodka_0000->hide();
        $this->form('maingame')->item_vodka_0000->enabled = true;
        $this->form('maingame')->item_vodka_0000->opacity = 100;
        $this->form('maingame')->item_vodka_0000->position = [256,696];
    }
    function SetItemCondition()
    {
        $this->maket_cond->width = 0;
        
        if ($GLOBALS['item_outfit_selected'])
        {
            if ($this->form('maingame')->health_bar_gg->width == 264) // Дефолтный размер health bar, без наподобности в функции ResetOutfitCondition
            {
                $this->maket_cond->text = "100 %";
                $this->maket_cond->color = '#4d804d';
                $this->form('maingame')->animateResizeWidth($this->maket_cond, 208, 8);
            }
            if ($this->form('maingame')->health_bar_gg->width == 204 || $this->form('maingame')->health_bar_gg->width == 234)
            {
                $this->maket_cond->text = "82 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('maingame')->animateResizeWidth($this->maket_cond, 168, 8);
            }
            if ($this->form('maingame')->health_bar_gg->width == 174 || $this->form('maingame')->health_bar_gg->width == 144)
            {
                $this->maket_cond->text = "67 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('maingame')->animateResizeWidth($this->maket_cond, 138, 8);
            }
            if ($this->form('maingame')->health_bar_gg->width == 114)
            {
                $this->maket_cond->text = "45 %";
                $this->maket_cond->color = '#b3801a';
                $this->form('maingame')->animateResizeWidth($this->maket_cond, 118, 8);
            }
            if ($this->form('maingame')->health_bar_gg->width == 84 || $this->form('maingame')->health_bar_gg->width == 54)
            {
                $this->maket_cond->text = "13 %";
                $this->maket_cond->color = '#990000';
                $this->form('maingame')->animateResizeWidth($this->maket_cond, 74, 8);
            }     
        }
        if ($GLOBALS['item_vodka_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('maingame')->animateResizeWidth($this->maket_cond, 208, 8);
        }
        if ($GLOBALS['item_medkit_selected'])
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('maingame')->animateResizeWidth($this->maket_cond, 208, 8);
        }        
    }  
}
