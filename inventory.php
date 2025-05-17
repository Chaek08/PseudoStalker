<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 
use app\forms\classes\Localization;

class inventory extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function UpdateInvenotryWeight()
    {
        $maxWeight = 50.0;
        $baseWeight = 3.8;
        $totalWeight = $baseWeight;

        $items = [
            ['item' => $this->inv_item_vodka,  'weight' => 0.5]
        ];

        foreach ($items as $entry)
        {
            $item = $entry['item'];
            $weight = $entry['weight'];

            if ($item != null && $item->visible)
            {
                $totalWeight += $weight;
            }
        }
        
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);        
        $WeightLabel = $this->localization->get('Weight_Label');
        $text = $WeightLabel . "  " . round($totalWeight, 1) . " / " . round($maxWeight, 1);
        $this->weight_desc->text = $text;
    }
    function HideVodkaMaket()
    {
        $this->inv_maket_select_2->hide();
        $this->maket_cond->hide(); 
    }
    function HideOutfitMaket() 
    {
        $this->inv_maket_select->hide();
        $this->maket_cond->hide();
    }
    function ShowVodkaMaket()
    {
        $this->HideOutfitMaket();    
        $this->inv_maket_select_2->show();
        $this->SetItemCondition();        
        $this->maket_cond->show(); 
    }
    function ShowOutfitMaket() 
    {
        $this->HideVodkaMaket();    
        $this->inv_maket_select->show();
        $this->SetItemCondition();        
        $this->maket_cond->show();
    }    
    function ShowUIText()
    {
        $this->maket_label->show();
        $this->maket_count->show();
        $this->maket_desc->show();
        $this->maket_cond_label->show();   
        $this->maket_weight->show();        
    }
    function HideUIText()
    {
        $this->maket_label->hide();
        $this->maket_count->hide();
        $this->maket_desc->hide();               
        $this->maket_cond->hide();
        $this->maket_cond_label->hide();
        $this->maket_weight->hide();         
    }
    function SetUIText()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if ($this->inv_maket_select_2->visible)
        {
            if (SDK_Mode)
            {
                $this->maket_label->text =
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka->text ?:
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka->promptText;

                $this->maket_desc->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Vodka->text ?:
                    $this->localization->get('Vodka_Inv_Desc');
                    
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Vodka));
                Element::setText($this->maket_weight, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Vodka));
            }
            else 
            {
                Element::setText($this->maket_count, "250 RU");
                Element::setText($this->maket_weight, "0.50kg");
                $this->maket_label->text = $this->localization->get('Vodka_Inv_Name');
                $this->maket_desc->text = $this->localization->get('Vodka_Inv_Desc');
            }
        }
        if ($this->inv_maket_select->visible)
        {
            if (SDK_Mode)
            {
                $this->maket_label->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit->text ?: 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit->promptText;

                $this->maket_desc->text = 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit->text ?: 
                    $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit->promptText;
                    
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Outfit));
                Element::setText($this->maket_weight, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Outfit));
            }
            else 
            {
                Element::setText($this->maket_count, "2599 RU");
                Element::setText($this->maket_weight, "1.00kg");
                $this->maket_label->text = $this->localization->get('Outfit_Inv_Name');
                $this->maket_desc->text = $this->localization->get('Outfit_Inv_Desc');
            }
        }
    }
    function UseSlotSound()
    {
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/inv_slot.mp3', true, 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/inv_properties.mp3', true, 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/inv_drop.mp3', true, 'inv_drop'); 
        }               
    }
    function ShowCombobox()
    {     
        $this->PropertiesSound();
        
        $this->main->position = $this->form('maingame')->CustomCursor->position;
        $this->button_drop->x = $this->main->x + 8;
        $this->button_drop->y = $this->main->y + 8;
        
        if (Geometry::intersect($this->main, $this->vodka_selected))  
        {
            $this->main->x = 104;
            $this->button_drop->x = 112;
        }      
             
        if ($this->main->toggle() || $this->button_drop->toggle())
        {
            $this->main->show();
            $this->button_drop->show();
        }          
    }
    function HideCombobox()
    {  
        $this->main->hide();
        $this->button_drop->hide();
    }    
    /**
     * @event inv_maket_visual.click-Left 
     */
    function OutfitMaketFunc(UXMouseEvent $e = null)
    {
        $this->HideCombobox();
    
        if ($this->inv_maket_select->visible) return;

        $this->UseSlotSound();
        $this->ShowOutfitMaket();
        $this->ShowUIText();
        $this->SetUIText();       
    }
    /**
     * @event vodka_selected.click-Left 
     */
    function VodkaMaketFunc(UXMouseEvent $e = null)
    {    
        $this->HideCombobox();
        
        if ($this->inv_maket_select_2->visible) return;
  
        $this->UseSlotSound();
        $this->ShowVodkaMaket();
        $this->ShowUIText();
        $this->SetUIText();
    }
    /**
     * @event button4.click-Left 
     */
    function InvFrameFunc(UXMouseEvent $e = null)
    {  
        $this->HideOutfitMaket();
        $this->HideVodkaMaket();
        $this->HideUIText();   
        $this->HideCombobox();          
    }
    /**
     * @event button_drop.click-Left 
     */
    function DropVodka(UXMouseEvent $e = null)
    {       
        if ($this->form('maingame')->Inventory->visible) $this->DropSound();
        $this->HideCombobox();
        $this->SpawnVodka();
        $this->UpdateInvenotryWeight();
        if ($this->inv_maket_select_2->visible)
        {
            $this->HideVodkaMaket();
            $this->HideUIText();
            
            if ($this->inv_maket_select->visible) $this->inv_maket_select->hide();
        }  
    }
    function SpawnVodka()
    {
        $this->inv_item_vodka->hide();
        $this->vodka_selected->hide();
        $this->form('maingame')->item_vodka_0000->show();
        $this->form('maingame')->item_vodka_0000->opacity = 100;
    }
    function DespawnVodka()
    {
        $this->inv_item_vodka->show();
        $this->vodka_selected->show();
        $this->form('maingame')->item_vodka_0000->hide();    
        $this->form('maingame')->item_vodka_0000->enabled = true;
        $this->form('maingame')->item_vodka_0000->opacity = 100;
        $this->form('maingame')->item_vodka_0000->position = [256,696];
    }    
    /**
     * @event vodka_selected.click-Right 
     */
    function VodkaActions(UXMouseEvent $e = null)
    {    
        $this->ShowCombobox();
    }
    function SetItemCondition()
    {
        $this->maket_cond->width = 0;
        
        if ($this->inv_maket_select->visible)
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
        if ($this->inv_maket_select_2->visible)
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->color = '#4d804d';
            $this->form('maingame')->animateResizeWidth($this->maket_cond, 208, 8);
        }
    }  
}
