<?php
namespace app\forms;

use std, gui, framework, app;


class wow_menu extends AbstractForm
{
    /**
     * @event colorPicker.action 
     */
    function SetColorOnProgressBarH(UXEvent $e = null)
    {   
         if ($this->form('maingame')->skull_alex->visible)
         { 
             $this->form('maingame')->progress_bar_health_actor->color = $this->colorPicker->value; //чтобы нас не ебали потому что актор сдох                  
         }
         else 
         {
             $this->form('maingame')->progress_bar_health_actor->color = $this->colorPicker->value;
             $this->form('maingame')->progress_bar_health_alex->color = $this->colorPicker->value;           
         } 
         
         if ($this->form('maingame')->skull_actor->visible)
         { 
             $this->form('maingame')->progress_bar_health_alex->color = $this->colorPicker->value; //чтобы нас не ебали потому что алекс сдох                  
         }
         else 
         {
             $this->form('maingame')->progress_bar_health_actor->color = $this->colorPicker->value;
             $this->form('maingame')->progress_bar_health_alex->color = $this->colorPicker->value;           
         }               
    }
    /**
     * @event button.action 
     */
    function ButtonAction(UXEvent $e = null)
    {    
         Element::setText($this->form('maingame')->fight_label, uiText($this->form('wow_menu')->change_fight_tex));
    }
    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        if ($this->form('maingame')->fight_label->visible)
        {
            $this->button->show();
            $this->change_fight_tex->show();
            $this->change_fight_text_label->show();
        }
        else 
        {
            $this->button->hide();
            $this->change_fight_tex->hide();
            $this->change_fight_text_label->hide();                        
        }
    }
}
