<?php
namespace app\forms;

use std, gui, framework, app;

class inventory extends AbstractForm
{
    /**
     * @event show 
     */
    function Inventory(UXWindowEvent $e = null)
    {    
        $this->GetCurrentHealth();
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
        $this->SetVodkaCondition();        
        $this->maket_cond->show(); 
    }
    function ShowOutfitMaket() 
    {
        $this->HideVodkaMaket();    
        $this->inv_maket_select->show();
        $this->SetOutfitCondition();        
        $this->maket_cond->show();        
    }    
    function ShowUIText()
    {
        $this->maket_label->show();
        $this->maket_count->show();
        $this->maket_desc->show();
        $this->maket_cond_static->show();
        $this->maket_weight->show();        
    }
    function HideUIText()
    {
        $this->maket_label->hide();
        $this->maket_count->hide();
        $this->maket_desc->hide();
        $this->maket_cond_static->hide();
        $this->maket_cond->hide();
        $this->maket_weight->hide();         
    }
    function SetUIText()
    {
        if ($this->inv_maket_select_2->visible)
        {
            Element::setText($this->maket_label, "Водка Казаки");
            Element::setText($this->maket_desc, "Огненная водичка! Можно устроить пожар в заднице алекса , метнув в него бутылку. Управление бутылкой: ЛКМ - Метнуть бутылку, ПКМ - Отметнуть к себе");    
            Element::setText($this->maket_count, "100 RU"); 
            Element::setText($this->maket_weight, "0.5kg");            
        }
        if ($this->inv_maket_select->visible)
        {
            Element::setText($this->maket_label, "Броня вовчика");
            Element::setText($this->maket_desc, "100% защита от радиации и пердежа алекса, сделано из плоти мамонтов");    
            Element::setText($this->maket_count, "1000 RU"); 
            Element::setText($this->maket_weight, "1.0kg");           
        }
    }
    function SlotUseSound()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/inv_slot.mp3', true, 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/inv_properties.mp3', true, 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/inv_drop.mp3', true, 'inv_drop'); 
        }               
    }
    function HideCombobox()
    {  
        $this->main->hide();
        $this->button_drop->hide();
    }
    function ShowCombobox()
    {     
        $this->PropertiesSound();
        $this->main->show();
        $this->button_drop->show();        
    }
    function CloseInventory()
    {
        $this->form('maingame')->fragment_inv->hide();
    }
    /**
     * @event inv_maket.click-Left 
     */
    function OutfitMaketFunc(UXMouseEvent $e = null)
    {
        $this->SlotUseSound();
        $this->ShowOutfitMaket();
        $this->ShowUIText();
        $this->SetUIText();       
    }
    /**
     * @event inv_item_vodka.click-Left 
     */
    function VodkaMaketFunc(UXMouseEvent $e = null)
    {    
        $this->SlotUseSound();
        $this->ShowVodkaMaket();
        $this->ShowUIText();
        $this->SetUIText();                
    }
    function GetCurrentHealth()
    {
        if ($this->health_bar_gg->visible) {} else
        {
            $this->health_bar_gg->show();
        }
        $this->health_bar_gg->width = 416; //100%
        $this->health_bar_gg->text = "100%";
    }
    /**
     * @event rci.click-Left 
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
        $this->DropSound();   
        $this->HideVodkaMaket();
        $this->HideUIText();   
        $this->HideCombobox(); 
        $this->SpawnVodka();  

    }
    function SpawnVodka()
    {
        Element::setText($this->weight_desc, 'Вес  3.8 / 50.0');
        $this->inv_item_vodka->hide();
        $this->form('maingame')->item_vodka_0000->show();                
    }
    function DespawnVodka()
    {
        Element::setText($this->weight_desc, 'Вес  4.3 / 50.0');
        $this->inv_item_vodka->show();
        $this->form('maingame')->item_vodka_0000->hide();    
        $this->form('maingame')->item_vodka_0000->x = 240;
        $this->form('maingame')->item_vodka_0000->y = 704;                           
    }    
    /**
     * @event inv_item_vodka.click-Right 
     */
    function VodkaActions(UXMouseEvent $e = null)
    {    
        $this->ShowCombobox();
    }  
    
    function SetOutfitCondition()
    {
        if ($this->form('maingame')->health_bar_gg->width == 164)
        {
            $this->maket_cond->text = "75 %"; 
            $this->maket_cond->width = 139;            
        }
        if ($this->form('maingame')->health_bar_gg->width == 114)
        {
            $this->maket_cond->text = "55 %";     
            $this->maket_cond->width = 104;                                           
        }           
        if ($this->form('maingame')->health_bar_gg->width == 24)
        {
            $this->maket_cond->text = "45 %";   
            $this->maket_cond->width = 49;                                                    
        }   
    } 
    function ResetOutfitCondition()
    {
        $this->maket_cond->text = "100 %";       
        $this->maket_cond->width = 216;         
    }
    function SetVodkaCondition()
    {
        $this->maket_cond->text = "100 %";       
        $this->maket_cond->width = 216; 
    }
}
