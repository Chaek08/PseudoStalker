<?php
namespace app\forms;

use std, gui, framework, app;


class opt extends AbstractForm
{
    /**
     * @event show 
     */
    function InitOptions(UXWindowEvent $e = null)
    {    
        $this->background->image = $this->form('mainmenu')->background->image;
    }
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
        //maingame
        $this->form('maingame')->item_vodka_0000->dropShadowEffect->disable();   
        $this->form('maingame')->actor->dropShadowEffect->disable();  
        $this->form('maingame')->enemy->dropShadowEffect->disable();   
        $this->form('maingame')->dlg_btn->dropShadowEffect->disable();   
        $this->form('maingame')->leave_btn->dropShadowEffect->disable();  
        $this->form('maingame')->health_static_enemy->dropShadowEffect->disable();     
        $this->form('maingame')->health_static_gg->dropShadowEffect->disable();     
        $this->form('maingame')->health_bar_enemy->dropShadowEffect->disable();     
        $this->form('maingame')->health_bar_gg->dropShadowEffect->disable();          
        $this->form('maingame')->fight_image->dropShadowEffect->disable();  
        //dialog
        $this->form('maingame')->fragment_dlg->content->actor_character->dropShadowEffect->disable();
        $this->form('maingame')->fragment_dlg->content->alex_character->dropShadowEffect->disable();
        $this->form('maingame')->fragment_dlg->content->icon_enemy->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_dlg->content->icon_gg->dropShadowEffect->disable();      
        //pda + all fragments
        $this->form('maingame')->fragment_pda->content->toolbar_frame_main->dropShadowEffect->disable();
        $this->form('maingame')->fragment_pda->content->toolbar_frame_time->dropShadowEffect->disable();
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->frame_01->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->frame_detail_02->dropShadowEffect->disable();  
        //$this->form('maingame')->fragment_pda->content->fragment_tasks->content->icon_task->dropShadowEffect->disable();  
        //$this->form('maingame')->fragment_pda->content->fragment_tasks->content->quest_detail_btn->dropShadowEffect->disable();                  
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->button->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->icon->dropShadowEffect->disable();                                                 
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->user_icon->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->button->dropShadowEffect->disable();   
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->button->dropShadowEffect->disable();  
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->icon->dropShadowEffect->disable();   
        //inventory
        $this->form('maingame')->fragment_inv->content->health_bar_gg->dropShadowEffect->disable();   
        $this->form('maingame')->fragment_inv->content->health_static_gg->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_inv->content->main->dropShadowEffect->disable();   
        //fail wnd             
        $this->form('maingame')->fragment_win_fail->content->Win_fail_desc->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_win_fail->content->Win_fail_text->dropShadowEffect->disable();   
        $this->form('maingame')->fragment_win_fail->content->Win_object->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_win_fail->content->exitbtn->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_win_fail->content->returnbtn->dropShadowEffect->disable(); 
        //mainmenu + opt                
        $this->form('maingame')->fragment_menu->content->btn_exit_windows->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_menu->content->btn_start_game->dropShadowEffect->disable();  
        $this->form('maingame')->fragment_menu->content->btn_opt->dropShadowEffect->disable();  
        $this->form('maingame')->fragment_menu->content->logo->dropShadowEffect->disable();
        $this->form('maingame')->fragment_opt->content->allsound_label->dropShadowEffect->disable();
        $this->form('maingame')->fragment_opt->content->allsound_off->dropShadowEffect->disable();   
        $this->form('maingame')->fragment_opt->content->allsound_on->dropShadowEffect->disable();               
        $this->form('maingame')->fragment_opt->content->menusound_label->dropShadowEffect->disable(); 
        $this->form('maingame')->fragment_opt->content->menusound_off->dropShadowEffect->disable();  
        $this->form('maingame')->fragment_opt->content->menusound_on->dropShadowEffect->disable();              
        $this->form('maingame')->fragment_opt->content->shadows_label->dropShadowEffect->disable();
        $this->form('maingame')->fragment_opt->content->shadows_off->dropShadowEffect->disable();  
        $this->form('maingame')->fragment_opt->content->shadows_on->dropShadowEffect->disable();              
        $this->form('maingame')->fragment_opt->content->version_label->dropShadowEffect->disable();    
        $this->form('maingame')->fragment_opt->content->version_off->dropShadowEffect->disable();   
        $this->form('maingame')->fragment_opt->content->version_on->dropShadowEffect->disable();                                                                                                                                                                                                                
    }
    /**
     * @event shadows_on.click-Left 
     */
    function ShadowOptOn(UXMouseEvent $e = null)
    {
        //maingame
        $this->form('maingame')->item_vodka_0000->dropShadowEffect->enable();   
        $this->form('maingame')->actor->dropShadowEffect->enable();  
        $this->form('maingame')->enemy->dropShadowEffect->enable();   
        $this->form('maingame')->dlg_btn->dropShadowEffect->enable();   
        $this->form('maingame')->leave_btn->dropShadowEffect->enable();  
        $this->form('maingame')->health_static_enemy->dropShadowEffect->enable();     
        $this->form('maingame')->health_static_gg->dropShadowEffect->enable();     
        $this->form('maingame')->health_bar_enemy->dropShadowEffect->enable();     
        $this->form('maingame')->health_bar_gg->dropShadowEffect->enable();          
        $this->form('maingame')->fight_image->dropShadowEffect->enable();  
        //dialog
        $this->form('maingame')->fragment_dlg->content->actor_character->dropShadowEffect->enable();
        $this->form('maingame')->fragment_dlg->content->alex_character->dropShadowEffect->enable();
        $this->form('maingame')->fragment_dlg->content->icon_enemy->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_dlg->content->icon_gg->dropShadowEffect->enable();      
        //pda + all fragments
        $this->form('maingame')->fragment_pda->content->toolbar_frame_main->dropShadowEffect->enable();
        $this->form('maingame')->fragment_pda->content->toolbar_frame_time->dropShadowEffect->enable();
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->frame_01->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->frame_detail_02->dropShadowEffect->enable();  
        //$this->form('maingame')->fragment_pda->content->fragment_tasks->content->icon_task->dropShadowEffect->enable();  
        //$this->form('maingame')->fragment_pda->content->fragment_tasks->content->quest_detail_btn->dropShadowEffect->enable();                  
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->button->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->icon->dropShadowEffect->enable();                                                 
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->user_icon->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->button->dropShadowEffect->enable();   
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->button->dropShadowEffect->enable();  
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->icon->dropShadowEffect->enable();   
        //inventory
        $this->form('maingame')->fragment_inv->content->health_bar_gg->dropShadowEffect->enable();   
        $this->form('maingame')->fragment_inv->content->health_static_gg->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_inv->content->main->dropShadowEffect->enable();   
        //fail wnd             
        $this->form('maingame')->fragment_win_fail->content->Win_fail_desc->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_win_fail->content->Win_fail_text->dropShadowEffect->enable();   
        $this->form('maingame')->fragment_win_fail->content->Win_object->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_win_fail->content->exitbtn->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_win_fail->content->returnbtn->dropShadowEffect->enable(); 
        //mainmenu + opt                
        $this->form('maingame')->fragment_menu->content->btn_exit_windows->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_menu->content->btn_start_game->dropShadowEffect->enable();  
        $this->form('maingame')->fragment_menu->content->btn_opt->dropShadowEffect->enable();  
        $this->form('maingame')->fragment_menu->content->logo->dropShadowEffect->enable();
        $this->form('maingame')->fragment_opt->content->allsound_label->dropShadowEffect->enable();
        $this->form('maingame')->fragment_opt->content->allsound_off->dropShadowEffect->enable();   
        $this->form('maingame')->fragment_opt->content->allsound_on->dropShadowEffect->enable();               
        $this->form('maingame')->fragment_opt->content->menusound_label->dropShadowEffect->enable(); 
        $this->form('maingame')->fragment_opt->content->menusound_off->dropShadowEffect->enable();  
        $this->form('maingame')->fragment_opt->content->menusound_on->dropShadowEffect->enable();              
        $this->form('maingame')->fragment_opt->content->shadows_label->dropShadowEffect->enable();
        $this->form('maingame')->fragment_opt->content->shadows_off->dropShadowEffect->enable();  
        $this->form('maingame')->fragment_opt->content->shadows_on->dropShadowEffect->enable();              
        $this->form('maingame')->fragment_opt->content->version_label->dropShadowEffect->enable();    
        $this->form('maingame')->fragment_opt->content->version_off->dropShadowEffect->enable();   
        $this->form('maingame')->fragment_opt->content->version_on->dropShadowEffect->enable();                                 
    }
    /**
     * @event allsound_off.click-Left 
     */
    function AllSoundOff(UXMouseEvent $e = null)
    {   
        $this->sound->hide();
        $this->menusound_off->enabled = false;
        $this->menusound_on->enabled = false;    
            
        if (Media::isStatus('PLAYING', 'fight_sound')){Media::stop('fight_sound');}
        if (Media::isStatus('PLAYING', 'main_ambient')){Media::stop('main_ambient');}
        if (Media::isStatus('PLAYING', 'menu_sound')){Media::stop('menu_sound');}        
        if (Media::isStatus('PLAYING', 'v_enemy')){Media::stop('v_enemy');}
        if (Media::isStatus('PLAYING', 'v_actor')){Media::stop('v_actor');}
        if (Media::isStatus('PLAYING', 'hit_alex')){Media::stop('hit_alex');}
        if (Media::isStatus('PLAYING', 'hit_alex_damage')){Media::stop('hit_alex_damage');}      
        if (Media::isStatus('PLAYING', 'hit_actor')){Media::stop('hit_actor');}
        if (Media::isStatus('PLAYING', 'hit_actor_damage')){Media::stop('hit_actor_damage');} 
        if (Media::isStatus('PLAYING', 'die_alex')){Media::stop('die_alex');}
        if (Media::isStatus('PLAYING', 'die_actor')){Media::stop('die_actor');}                    
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
    function WatermarkOff(UXMouseEvent $e = null)
    {       
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->form('maingame')->label_version->hide();             
        }
        else 
        {
            $this->form('maingame')->fragment_menu->content->label_version->hide(); 
        }          
    }

    /**
     * @event version_on.click-Left 
     */
    function WatermarkOn(UXMouseEvent $e = null)
    {      
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->form('maingame')->label_version->show();             
        }
        else 
        {
            $this->form('maingame')->fragment_menu->content->label_version->show(); 
        }    
    }
}
