<?php
namespace app\forms;

use std, gui, framework, app;


class opt extends AbstractForm
{
    /**
     * @event back_btn.click-Left
     */
    function BackBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->show();    
        $this->form('maingame')->fragment_opt->hide();      
    }
    /**
     * @event btn_menu_sound_mute.click-Left 
     */
    function OptMuteMenuSound(UXMouseEvent $e = null)
    {    
        Media::stop("menu_sound");
        $this->mutesound->show();
    }
    /**
     * @event btn_menu_sound_unmute.click-Left 
     */
    function OptUnMuteMenuSound(UXMouseEvent $e = null)
    {
        Media::play("menu_sound");
        $this->mutesound->hide();             
    }
    /**
     * @event item_shadow_off.click-Left 
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
    }
    /**
     * @event item_shadow_on.click-Left 
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
    }
    /**
     * @event all_sound_off.click-Left 
     */
    function AllSoundOff(UXMouseEvent $e = null)
    {
        $this->sound->hide();
        Media::stop("menu_sound");
        Media::stop("fight_sound");
        Media::stop();
    }
    /**
     * @event all_sound_on.click-Left 
     */
    function AllSoundOn(UXMouseEvent $e = null)
    {
        $this->sound->show();
    }
}
