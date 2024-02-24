<?php
namespace app\forms;

use std, gui, framework, app;


class opt extends AbstractForm
{
    /**
     * @event return_btn.click-Left
     */
    function BackBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->show();    
        $this->form('maingame')->fragment_opt->hide();      
    }
    /**
     * @event menusound_off.click-Left 
     */
    function OptMuteMenuSound(UXMouseEvent $e = null)
    {    
        Media::stop('menu_sound');
        $this->mutesound->show();
    }
    /**
     * @event menusound_on.click-Left 
     */
    function OptUnMuteMenuSound(UXMouseEvent $e = null)
    {
        Media::play('menu_sound');
        $this->mutesound->hide();             
    }
    /**
     * @event shadows_off.click-Left 
     */
    function ShadowOptOff(UXMouseEvent $e = null)
    {
        $this->form('maingame')->item_vodka_0000->dropShadowEffect->disable();   
        $this->form('maingame')->actor->dropShadowEffect->disable();  
        $this->form('maingame')->enemy->dropShadowEffect->disable();   
        $this->form('maingame')->dlg_btn->dropShadowEffect->disable();   
        $this->form('maingame')->damage_enemy_btn->dropShadowEffect->disable();  
        $this->form('maingame')->leave_btn->dropShadowEffect->disable();  
        $this->form('maingame')->health_static_enemy->dropShadowEffect->disable();     
        $this->form('maingame')->health_static_gg->dropShadowEffect->disable();     
        $this->form('maingame')->fight_label->dropShadowEffect->disable();                                                                         
    }
    /**
     * @event shadows_on.click-Left 
     */
    function ShadowOptOn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->item_vodka_0000->dropShadowEffect->enable();    
        $this->form('maingame')->actor->dropShadowEffect->enable();  
        $this->form('maingame')->enemy->dropShadowEffect->enable();   
        $this->form('maingame')->dlg_btn->dropShadowEffect->enable();   
        $this->form('maingame')->damage_enemy_btn->dropShadowEffect->enable();  
        $this->form('maingame')->leave_btn->dropShadowEffect->enable();  
        $this->form('maingame')->health_static_enemy->dropShadowEffect->enable();     
        $this->form('maingame')->health_static_gg->dropShadowEffect->enable(); 
        $this->form('maingame')->fight_label->dropShadowEffect->enable();                                
    }
    /**
     * @event allsound_off.click-Left 
     */
    function AllSoundOff(UXMouseEvent $e = null)
    {
        $this->sound->hide();
        $this->menusound_off->enabled = false;
        $this->menusound_on->enabled = false;        
        Media::stop('menu_sound');
        Media::stop('main_ambient');
        Media::stop('fight_sound');
        Media::stop();
    }
    /**
     * @event allsound_on.click-Left 
     */
    function AllSoundOn(UXMouseEvent $e = null)
    {
        $this->sound->show();
        $this->OptUnMuteMenuSound();
        $this->menusound_off->enabled = true;
        $this->menusound_on->enabled = true;          
    }

    /**
     * @event version_off.click-Left 
     */
    function WatermarkMenuOff(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->content->label_version->hide();
    }

    /**
     * @event version_on.click-Left 
     */
    function WatermarkMenuOn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->content->label_version->show();        
    }

    /**
     * @event show 
     */
    function InitOptions(UXWindowEvent $e = null)
    {    
        $this->background->image = $this->form('mainmenu')->background->image;
    }



}
