<?php
namespace app\forms;

use std, gui, framework, app;

class inventory extends AbstractForm
{
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
        if ($this->inv_maket_select_2->visible)
        {
            if ($this->form('maingame')->SDK_Mode->visible)
            {
                Element::setText($this->maket_label, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka));
                Element::setText($this->maket_desc, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Vodka));
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Vodka));
                Element::setText($this->maket_weight, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Vodka));
            }
            else 
            {
                Element::setText($this->maket_label, "Водка Казаки");
                Element::setText($this->maket_desc, "С этой водичкой я могу создать настоящий огонь в заднице гоблиндава, просто метнув в него бутылку! Это мощное средство, которое гарантирует мою безопасность и эффективность в борьбе с любыми угрозами.\nУправление бутылкой:\nЛКМ - Метнуть бутылку во врага\nПКМ - Метнуть бутылку к себе");
                Element::setText($this->maket_count, "250 RU");
                Element::setText($this->maket_weight, "0.50kg");
            }
        }
        if ($this->inv_maket_select->visible)
        {
            if ($this->form('maingame')->SDK_Mode->visible)
            {
                Element::setText($this->maket_label, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit));
                Element::setText($this->maket_desc, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit));
                Element::setText($this->maket_count, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Count_Outfit));
                Element::setText($this->maket_weight, uiText($this->form('maingame')->Editor->content->f_InvEditor->content->Edit_Weight_Outfit));
            }
            else 
            {
                Element::setText($this->maket_label, "Броня Сани Кабана");
                Element::setText($this->maket_desc, "Этот продукт обеспечивает полную защиту от радиации и ужасного запаха гоблиндава! И что самое важное - он сделан из натуральной кабаньей плоти. Теперь я могу быть уверенным в своей безопасности и комфорте в любых условиях.");
                Element::setText($this->maket_count, "2599 RU");
                Element::setText($this->maket_weight, "1.00kg");
            }
        }
    }
    function UseSlotSound()
    {
        if ($this->form('maingame')->Options->content->all_sounds->visible)
        {
            Media::open('res://.data/audio/inv_slot.mp3', true, 'inv_use_slot'); 
        }     
    }
    function PropertiesSound()
    {
        if ($this->form('maingame')->Options->content->all_sounds->visible)
        {
            Media::open('res://.data/audio/inv_properties.mp3', true, 'inv_properties'); 
        }          
    }
    function DropSound()
    {
        if ($this->form('maingame')->Options->content->all_sounds->visible)
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
        
        $this->main->x = $this->form('maingame')->CustomCursor->x;
        $this->main->y = $this->form('maingame')->CustomCursor->y;
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
    function CloseInventory()
    {
        $this->form('maingame')->Inventory->hide();
    }
    /**
     * @event inv_maket_visual.click-Left 
     */
    function OutfitMaketFunc(UXMouseEvent $e = null)
    {
        if ($this->inv_maket_select->visible)
        {
            return;
        }
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
        if ($this->inv_maket_select_2->visible)
        {
            return;
        }    
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
        $this->DropSound();  
        $this->HideCombobox(); 
        $this->SpawnVodka();          
        if ($this->inv_maket_select_2->visible)
        {
            $this->HideVodkaMaket();
            $this->HideUIText();
            if ($this->inv_maket_select->visible)
            {
                $this->inv_maket_select->hide();
            }
        }  

    }
    function SpawnVodka()
    {
        Element::setText($this->weight_desc, 'Вес  3.8 / 50.0');
        $this->inv_item_vodka->hide();
        $this->vodka_selected->hide();
        $this->form('maingame')->item_vodka_0000->show();                
    }
    function DespawnVodka()
    {
        Element::setText($this->weight_desc, 'Вес  4.3 / 50.0');
        $this->inv_item_vodka->show();
        $this->vodka_selected->show();
        $this->form('maingame')->item_vodka_0000->hide();    
        $this->form('maingame')->item_vodka_0000->enabled = true;       
        $this->form('maingame')->item_vodka_0000->x = 256;
        $this->form('maingame')->item_vodka_0000->y = 696;                           
    }    
    /**
     * @event vodka_selected.click-Right 
     */
    function VodkaActions(UXMouseEvent $e = null)
    {    
        $this->ShowCombobox();
    }  
    /**
     * @event inv_maket_select_2.click-2x 
     */
    function VodkaMaketClickLeft(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->skull_actor->visible || $this->form('maingame')->skull_enemy->visible)
        {
            if ($this->form('maingame')->Options->content->all_sounds->visible)
            {        
                Media::open('res://.data/audio/movie_1.mp3', true, 'movie_1');  
            }           
        }
    }
    function SetItemCondition()
    {
        if ($this->inv_maket_select->visible)
        {
            if ($this->form('maingame')->health_bar_gg->width == 264) // Дефолтный размер health bar, без наподобности в функции ResetOutfitCondition
            {
                $this->maket_cond->text = "100 %";
                $this->maket_cond->width = 208;
            }
            if ($this->form('maingame')->health_bar_gg->width == 164)
            {
                $this->maket_cond->text = "75 %";
                $this->maket_cond->width = 168;
            }
            if ($this->form('maingame')->health_bar_gg->width == 114)
            {
                $this->maket_cond->text = "55 %";
                $this->maket_cond->width = 138;
            }
            if ($this->form('maingame')->health_bar_gg->width == 34)
            {
                $this->maket_cond->text = "45 %";
                $this->maket_cond->width = 118;      
            }
        }
        if ($this->inv_maket_select_2->visible)
        {
            $this->maket_cond->text = "100 %";
            $this->maket_cond->width = 208;
        }
    }
}
