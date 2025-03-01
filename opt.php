<?php
namespace app\forms;

use std, gui, framework, app;


class opt extends AbstractForm
{
    /**
     * @event show 
     */
    function InitOptions(UXWindowEvent $e = null) {}
    /**
     * @event return_btn.click-Left
     */
    function BackBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->MainMenu->show();    
        $this->form('maingame')->Options->hide();      
    }
    /**
     * @event menusound_off.click-Left 
     */
    function OptMuteMenuSound(UXMouseEvent $e = null)
    {       
        Media::stop('menu_sound');
        $this->mute_menu_sound->show();
    }
    /**
     * @event menusound_on.click-Left 
     */
    function OptUnMuteMenuSound(UXMouseEvent $e = null)
    {    
        Media::play('menu_sound');
        $this->mute_menu_sound->hide();             
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
        $this->form('maingame')->ReplayBtn->dropShadowEffect->disable();        
        //dialog
        $this->form('maingame')->Dialog->content->actor_character->dropShadowEffect->disable();
        $this->form('maingame')->Dialog->content->alex_character->dropShadowEffect->disable();
        $this->form('maingame')->Dialog->content->icon_enemy->dropShadowEffect->disable(); 
        $this->form('maingame')->Dialog->content->icon_gg->dropShadowEffect->disable();      
        //pda + all fragments
        $this->form('maingame')->Pda->content->toolbar_frame_main->dropShadowEffect->disable();
        $this->form('maingame')->Pda->content->toolbar_frame_time->dropShadowEffect->disable();
        $this->form('maingame')->Pda->content->Pda_Tasks->content->frame_01->dropShadowEffect->disable(); 
        $this->form('maingame')->Pda->content->Pda_Tasks->content->frame_detail_02->dropShadowEffect->disable();  
        $this->form('maingame')->Pda->content->Pda_Contacts->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->Pda->content->Pda_Contacts->content->button->dropShadowEffect->disable();    
        $this->form('maingame')->Pda->content->Pda_Contacts->content->icon->dropShadowEffect->disable();                                                 
        $this->form('maingame')->Pda->content->Pda_Ranking->content->user_icon->dropShadowEffect->disable(); 
        $this->form('maingame')->Pda->content->Pda_Ranking->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->Pda->content->Pda_Ranking->content->button->dropShadowEffect->disable();   
        $this->form('maingame')->Pda->content->Pda_Statistic->content->frame_01->dropShadowEffect->disable();    
        $this->form('maingame')->Pda->content->Pda_Statistic->content->button->dropShadowEffect->disable();  
        $this->form('maingame')->Pda->content->Pda_Statistic->content->icon->dropShadowEffect->disable();   
        //inventory
        $this->form('maingame')->Inventory->content->health_bar_gg->dropShadowEffect->disable();   
        $this->form('maingame')->Inventory->content->health_static_gg->dropShadowEffect->disable(); 
        $this->form('maingame')->Inventory->content->main->dropShadowEffect->disable();   
        //fail wnd             
        $this->form('maingame')->Fail->content->Win_fail_desc->dropShadowEffect->disable(); 
        $this->form('maingame')->Fail->content->Win_fail_text->dropShadowEffect->disable();   
        $this->form('maingame')->Fail->content->Win_object->dropShadowEffect->disable(); 
        $this->form('maingame')->Fail->content->exitbtn->dropShadowEffect->disable(); 
        $this->form('maingame')->Fail->content->returnbtn->dropShadowEffect->disable(); 
        //mainmenu + opt                
        $this->form('maingame')->MainMenu->content->Btn_Exit_Windows->dropShadowEffect->disable();    
        $this->form('maingame')->MainMenu->content->Btn_Start_Game->dropShadowEffect->disable();
        $this->form('maingame')->MainMenu->content->Btn_End_Game->dropShadowEffect->disable();          
        $this->form('maingame')->MainMenu->content->Btn_Opt->dropShadowEffect->disable();  
        $this->form('maingame')->MainMenu->content->logo->dropShadowEffect->disable();
        $this->form('maingame')->Options->content->allsound_label->dropShadowEffect->disable();
        $this->form('maingame')->Options->content->allsound_off->dropShadowEffect->disable();   
        $this->form('maingame')->Options->content->allsound_on->dropShadowEffect->disable();               
        $this->form('maingame')->Options->content->menusound_label->dropShadowEffect->disable(); 
        $this->form('maingame')->Options->content->menusound_off->dropShadowEffect->disable();  
        $this->form('maingame')->Options->content->menusound_on->dropShadowEffect->disable();              
        $this->form('maingame')->Options->content->shadows_label->dropShadowEffect->disable();
        $this->form('maingame')->Options->content->shadows_off->dropShadowEffect->disable();  
        $this->form('maingame')->Options->content->shadows_on->dropShadowEffect->disable();              
        $this->form('maingame')->Options->content->version_label->dropShadowEffect->disable();    
        $this->form('maingame')->Options->content->version_off->dropShadowEffect->disable();   
        $this->form('maingame')->Options->content->version_on->dropShadowEffect->disable();
        $this->form('maingame')->Options->content->fightsound_label->dropShadowEffect->disable();    
        $this->form('maingame')->Options->content->fightsound_off->dropShadowEffect->disable();   
        $this->form('maingame')->Options->content->fightsound_on->dropShadowEffect->disable();
        //exit_dlg
        $this->form('maingame')->ExitDialog->content->main_frame->dropShadowEffect->disable();
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
        $this->form('maingame')->ReplayBtn->dropShadowEffect->enable();
        //dialog
        $this->form('maingame')->Dialog->content->actor_character->dropShadowEffect->enable();
        $this->form('maingame')->Dialog->content->alex_character->dropShadowEffect->enable();
        $this->form('maingame')->Dialog->content->icon_enemy->dropShadowEffect->enable(); 
        $this->form('maingame')->Dialog->content->icon_gg->dropShadowEffect->enable();      
        //pda + all fragments
        $this->form('maingame')->Pda->content->toolbar_frame_main->dropShadowEffect->enable();
        $this->form('maingame')->Pda->content->toolbar_frame_time->dropShadowEffect->enable();
        $this->form('maingame')->Pda->content->Pda_Tasks->content->frame_01->dropShadowEffect->enable(); 
        $this->form('maingame')->Pda->content->Pda_Tasks->content->frame_detail_02->dropShadowEffect->enable();  
        $this->form('maingame')->Pda->content->Pda_Contacts->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->Pda->content->Pda_Contacts->content->button->dropShadowEffect->enable();    
        $this->form('maingame')->Pda->content->Pda_Contacts->content->icon->dropShadowEffect->enable();                                                 
        $this->form('maingame')->Pda->content->Pda_Ranking->content->user_icon->dropShadowEffect->enable(); 
        $this->form('maingame')->Pda->content->Pda_Ranking->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->Pda->content->Pda_Ranking->content->button->dropShadowEffect->enable();   
        $this->form('maingame')->Pda->content->Pda_Statistic->content->frame_01->dropShadowEffect->enable();    
        $this->form('maingame')->Pda->content->Pda_Statistic->content->button->dropShadowEffect->enable();  
        $this->form('maingame')->Pda->content->Pda_Statistic->content->icon->dropShadowEffect->enable();   
        //inventory
        $this->form('maingame')->Inventory->content->health_bar_gg->dropShadowEffect->enable();   
        $this->form('maingame')->Inventory->content->health_static_gg->dropShadowEffect->enable(); 
        $this->form('maingame')->Inventory->content->main->dropShadowEffect->enable();   
        //fail wnd             
        $this->form('maingame')->Fail->content->Win_fail_desc->dropShadowEffect->enable(); 
        $this->form('maingame')->Fail->content->Win_fail_text->dropShadowEffect->enable();   
        $this->form('maingame')->Fail->content->Win_object->dropShadowEffect->enable(); 
        $this->form('maingame')->Fail->content->exitbtn->dropShadowEffect->enable(); 
        $this->form('maingame')->Fail->content->returnbtn->dropShadowEffect->enable(); 
        //mainmenu + opt                
        $this->form('maingame')->MainMenu->content->Btn_Exit_Windows->dropShadowEffect->enable();    
        $this->form('maingame')->MainMenu->content->Btn_Start_Game->dropShadowEffect->enable();
        $this->form('maingame')->MainMenu->content->Btn_End_Game->dropShadowEffect->enable();
        $this->form('maingame')->MainMenu->content->Btn_Opt->dropShadowEffect->enable();  
        $this->form('maingame')->MainMenu->content->logo->dropShadowEffect->enable();
        $this->form('maingame')->Options->content->allsound_label->dropShadowEffect->enable();
        $this->form('maingame')->Options->content->allsound_off->dropShadowEffect->enable();   
        $this->form('maingame')->Options->content->allsound_on->dropShadowEffect->enable();               
        $this->form('maingame')->Options->content->menusound_label->dropShadowEffect->enable(); 
        $this->form('maingame')->Options->content->menusound_off->dropShadowEffect->enable();  
        $this->form('maingame')->Options->content->menusound_on->dropShadowEffect->enable();              
        $this->form('maingame')->Options->content->shadows_label->dropShadowEffect->enable();
        $this->form('maingame')->Options->content->shadows_off->dropShadowEffect->enable();  
        $this->form('maingame')->Options->content->shadows_on->dropShadowEffect->enable();              
        $this->form('maingame')->Options->content->version_label->dropShadowEffect->enable();    
        $this->form('maingame')->Options->content->version_off->dropShadowEffect->enable();   
        $this->form('maingame')->Options->content->version_on->dropShadowEffect->enable();                                 
        $this->form('maingame')->Options->content->fightsound_label->dropShadowEffect->enable();    
        $this->form('maingame')->Options->content->fightsound_off->dropShadowEffect->enable();   
        $this->form('maingame')->Options->content->fightsound_on->dropShadowEffect->enable();      
        //exit_dlg
        $this->form('maingame')->ExitDialog->content->main_frame->dropShadowEffect->enable();        
    }
    /**
     * @event allsound_off.click-Left 
     */
    function AllSoundOff(UXMouseEvent $e = null)
    {   
        $this->All_Sounds->hide();
        
        $this->menusound_off->enabled = false;
        $this->menusound_on->enabled = false;
        
        $this->form('maingame')->ReplayBtn->enabled = false;
        $this->form('maingame')->ReplayBtn->opacity = 0;
            
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
        $this->OptUnMuteMenuSound();
        
        $this->All_Sounds->show();
        
        $this->menusound_off->enabled = true;
        $this->menusound_on->enabled = true;        
        
        $this->form('maingame')->ReplayBtn->enabled = true;       
        $this->form('maingame')->ReplayBtn->opacity = 100;
        
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, false, "fight_sound");
        }
        else 
        {
            Media::open('res://.data/audio/fight/fight_baza.mp3', false, "fight_sound");
        }            
    }
    /**
     * @event version_off.click-Left 
     */
    function WatermarkOff(UXMouseEvent $e = null)
    {       
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->form('maingame')->version->hide();             
            $this->form('maingame')->version_detail->hide();                         
        }
        else 
        {
            $this->form('maingame')->MainMenu->content->version->hide(); 
            $this->form('maingame')->MainMenu->content->version_detail->hide();             
        }          
    }
    /**
     * @event version_on.click-Left 
     */
    function WatermarkOn(UXMouseEvent $e = null)
    {      
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->form('maingame')->version->show();             
            $this->form('maingame')->version_detail->show();                         
        }
        else 
        {
            $this->form('maingame')->MainMenu->content->version->show(); 
            $this->form('maingame')->MainMenu->content->version_detail->show();             
        }    
    }
    /**
     * @event fightsound_on.click-Left 
     */
    function FightSoundOn(UXMouseEvent $e = null)
    {    
        $this->mute_fight_sound->hide();
        
        $this->form('maingame')->ReplayBtn->enabled = true;
        $this->form('maingame')->ReplayBtn->opacity = 100; 
    }
    /**
     * @event fightsound_off.click-Left 
     */
    function FightSoundOff(UXMouseEvent $e = null)
    {    
        $this->mute_fight_sound->show();
        
        $this->form('maingame')->ReplayBtn->enabled = false;       
        $this->form('maingame')->ReplayBtn->opacity = 0;
    }
}
