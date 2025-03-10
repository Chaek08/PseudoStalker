<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class opt extends AbstractForm
{
    function InitOptions()
    {
        if (!$GLOBALS['AllSounds'])
        {
            $this->AllSoundSwitcher_MouseDownLeft();
            $this->AllSoundSwitcher_MouseExit();
            
            return;
        }
        if (!$GLOBALS['MenuSound'])
        {
            $this->MenuSoundSwitcher_MouseDownLeft();
            $this->MenuSoundSwitcher_MouseExit();
        }
        if (!$GLOBALS['FightSound'])
        {
            $this->FightSoundSwitcher_MouseDownLeft();
            $this->FightSoundSwitcher_MouseExit();
        }
    }
    /**
     * @event Return_Btn.mouseExit 
     */
    function ReturnBtn_MouseExit(UXMouseEvent $e = null)
    {
        $this->Return_Btn->textColor = '#ffffff';
    }
    /**
     * @event Return_Btn.mouseEnter 
     */
    function ReturnBtn_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Return_Btn->textColor = '#b3b3b3';
    }
    /**
     * @event Return_Btn.mouseDown-Left 
     */
    function ReturnBtn_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Return_Btn->textColor = '#808080';
        
        $this->form('maingame')->MainMenu->show();
        $this->form('maingame')->Options->hide();
    }
    /**
     * @event Return_Btn.mouseUp-Left 
     */
    function ReturnBtn_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->ReturnBtn_MouseExit();
    }
    /**
     * @event AllSound_Switcher_Btn.mouseExit 
     */
    function AllSoundSwitcher_MouseExit(UXMouseEvent $e = null)
    {
        $this->AllSound_Switcher_Btn->textColor = ($this->AllSound_Switcher_Btn->text == 'Выкл') ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event AllSound_Switcher_Btn.mouseEnter 
     */
    function AllSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->AllSound_Switcher_Btn->textColor = ($this->AllSound_Switcher_Btn->text == 'Выкл') ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event AllSound_Switcher_Btn.mouseDown-Left 
     */
    function AllSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($this->AllSound_Switcher_Btn->text == 'Вкл')
        {
            $this->AllSound_Switcher_Btn->text = 'Выкл';
            
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {            
                $this->AllSound_Switcher_Btn->textColor = '#880911';
            }
            else 
            {
                $this->AllSoundSwitcher_MouseEnter();
            }
            
            $GLOBALS['AllSounds'] = false;
        
            if ($this->MenuSound_Switcher_Btn->text == 'Вкл') 
            {
                $this->MenuSoundSwitcher_MouseDownLeft();
                $this->MenuSoundSwitcher_MouseExit();
            }
            if ($this->FightSound_Switcher_Btn->text == 'Вкл')
            {
                $this->FightSoundSwitcher_MouseDownLeft();
                $this->FightSoundSwitcher_MouseExit();
            }
        
            if ($this->form('maingame')->fight_image->visible) $this->form('maingame')->ReplayBtn->hide();
            
            if (Media::isStatus('PLAYING', 'fight_sound')) Media::stop('fight_sound');
            if (Media::isStatus('PLAYING', 'main_ambient')) Media::stop('main_ambient');
            if (Media::isStatus('PLAYING', 'menu_sound')) Media::stop('menu_sound');        
            if (Media::isStatus('PLAYING', 'v_enemy')) Media::stop('v_enemy');
            if (Media::isStatus('PLAYING', 'v_actor')) Media::stop('v_actor');
            if (Media::isStatus('PLAYING', 'hit_alex')) Media::stop('hit_alex');
            if (Media::isStatus('PLAYING', 'hit_alex_damage')) Media::stop('hit_alex_damage');     
            if (Media::isStatus('PLAYING', 'hit_actor')) Media::stop('hit_actor');
            if (Media::isStatus('PLAYING', 'hit_actor_damage')) Media::stop('hit_actor_damage'); 
            if (Media::isStatus('PLAYING', 'die_alex')) Media::stop('die_alex');
            if (Media::isStatus('PLAYING', 'die_actor')) Media::stop('die_actor');
            
            return;
        }
        else 
        {
            $this->AllSound_Switcher_Btn->text = 'Вкл';
            
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {                       
                $this->AllSound_Switcher_Btn->textColor = '#099209';
            }
            else 
            {
                $this->AllSoundSwitcher_MouseEnter();
            }
        
            $GLOBALS['AllSounds'] = true;
        
            if ($this->MenuSound_Switcher_Btn->text == 'Выкл') 
            {
                $this->MenuSoundSwitcher_MouseDownLeft();
                $this->MenuSoundSwitcher_MouseExit();
            }
            if ($this->FightSound_Switcher_Btn->text == 'Выкл') 
            {
                $this->FightSoundSwitcher_MouseDownLeft();
                $this->FightSoundSwitcher_MouseExit();
            }       
        
            if ($this->form('maingame')->fight_image->visible) $this->form('maingame')->ReplayBtn->show();
        
            if (SDK_Mode)
            {
                Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, false, "fight_sound");
            }
            else 
            {  
                Media::open('res://.data/audio/fight/fight_baza.mp3', false, "fight_sound");
            }
            
            return;
        }
    }
    /**
     * @event AllSound_Switcher_Btn.mouseUp-Left 
     */
    function AllSoundSwitcher_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->AllSoundSwitcher_MouseExit();
        $this->AllSoundSwitcher_MouseEnter();
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseExit 
     */
    function MenuSoundSwitcher_MouseExit(UXMouseEvent $e = null)
    {
        $this->MenuSound_Switcher_Btn->textColor = ($this->MenuSound_Switcher_Btn->text == 'Выкл') ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseEnter 
     */
    function MenuSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->MenuSound_Switcher_Btn->textColor = ($this->MenuSound_Switcher_Btn->text == 'Выкл') ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseDown-Left 
     */
    function MenuSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($this->MenuSound_Switcher_Btn->text == 'Вкл')
        {
            $this->MenuSound_Switcher_Btn->text = 'Выкл';
            
            $this->MenuSound_Switcher_Btn->textColor = '#880911';
            
            Media::stop('menu_sound');
            $GLOBALS['MenuSound'] = false;
            
            return;
        }
        else 
        {
            $this->MenuSound_Switcher_Btn->text = 'Вкл';
            
            $this->MenuSound_Switcher_Btn->textColor = '#099209';
            
            Media::play('menu_sound');
            $GLOBALS['MenuSound'] = true;
            
            return;
        }
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseUp-Left 
     */
    function MenuSoundSwitcher_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->MenuSoundSwitcher_MouseExit();
        $this->MenuSoundSwitcher_MouseEnter();
    }

    /**
     * @event FightSound_Switcher_Btn.mouseExit 
     */
    function FightSoundSwitcher_MouseExit(UXMouseEvent $e = null)
    {
        $this->FightSound_Switcher_Btn->textColor = ($this->FightSound_Switcher_Btn->text == 'Выкл') ? '#d60d1b' : '#0dd60d';
    }

    /**
     * @event FightSound_Switcher_Btn.mouseEnter 
     */
    function FightSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->FightSound_Switcher_Btn->textColor = ($this->FightSound_Switcher_Btn->text == 'Выкл') ? '#b50b17' : '#0bb30b';
    }

    /**
     * @event FightSound_Switcher_Btn.mouseDown-Left 
     */
    function FightSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($this->FightSound_Switcher_Btn->text == 'Вкл')
        {
            $this->FightSound_Switcher_Btn->text = 'Выкл';
            
            $this->FightSound_Switcher_Btn->textColor = '#880911';
            
            $GLOBALS['FightSound'] = false;
        
            if ($this->form('maingame')->fight_image->visible) $this->form('maingame')->ReplayBtn->hide();
            
            return;
        }
        else 
        {
            $this->FightSound_Switcher_Btn->text = 'Вкл';
            
            $this->FightSound_Switcher_Btn->textColor = '#099209';
            
            $GLOBALS['FightSound'] = true;
        
            if ($this->form('maingame')->fight_image->visible) $this->form('maingame')->ReplayBtn->show();
            
            return;
        }
    }
    /**
     * @event FightSound_Switcher_Btn.mouseUp-Left 
     */
    function FightSoundSwitcher_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->FightSoundSwitcher_MouseExit();
        $this->FightSoundSwitcher_MouseEnter();
    }
    /**
     * @event Shadows_Switcher_Btn.mouseExit 
     */
    function ShadowsSwitcher_MouseExit(UXMouseEvent $e = null)
    {
        $this->Shadows_Switcher_Btn->textColor = ($this->Shadows_Switcher_Btn->text == 'Выкл') ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event Shadows_Switcher_Btn.mouseEnter 
     */
    function ShadowsSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Shadows_Switcher_Btn->textColor = ($this->Shadows_Switcher_Btn->text == 'Выкл') ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event Shadows_Switcher_Btn.mouseDown-Left 
     */
    function ShadowsSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($this->Shadows_Switcher_Btn->text == 'Вкл')
        {
            $this->Shadows_Switcher_Btn->text = 'Выкл';
            
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {            
                $this->Shadows_Switcher_Btn->textColor = '#880911';
            }
            else 
            {
                $this->ShadowsSwitcher_MouseEnter();
            }
            
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
            $this->form('maingame')->Options->content->Return_Btn->dropShadowEffect->disable();
            $this->form('maingame')->Options->content->AllSound_Label->dropShadowEffect->disable();
            $this->form('maingame')->Options->content->AllSound_Switcher_Btn->dropShadowEffect->disable();              
            $this->form('maingame')->Options->content->MenuSound_Label->dropShadowEffect->disable(); 
            $this->form('maingame')->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->disable();            
            $this->form('maingame')->Options->content->Shadows_Label->dropShadowEffect->disable();
            $this->form('maingame')->Options->content->Shadows_Switcher_Btn->dropShadowEffect->disable();                
            $this->form('maingame')->Options->content->Version_Label->dropShadowEffect->disable();
            $this->form('maingame')->Options->content->Version_Switcher_Btn->dropShadowEffect->disable();
            $this->form('maingame')->Options->content->FightSound_Label->dropShadowEffect->disable();    
            $this->form('maingame')->Options->content->FightSound_Switcher_Btn->dropShadowEffect->disable();
            //exit_dlg
            $this->form('maingame')->ExitDialog->content->main_frame->dropShadowEffect->disable();
            
            return;
        }
        else 
        {
            $this->Shadows_Switcher_Btn->text = 'Вкл';
               
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {                    
                $this->Shadows_Switcher_Btn->textColor = '#099209';
            }
            else 
            {
                $this->ShadowsSwitcher_MouseEnter();
            }
            
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
            $this->form('maingame')->Options->content->Return_Btn->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->AllSound_Label->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->AllSound_Switcher_Btn->dropShadowEffect->enable();              
            $this->form('maingame')->Options->content->MenuSound_Label->dropShadowEffect->enable(); 
            $this->form('maingame')->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->enable();            
            $this->form('maingame')->Options->content->Shadows_Label->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->Shadows_Switcher_Btn->dropShadowEffect->enable();                
            $this->form('maingame')->Options->content->Version_Label->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->Version_Switcher_Btn->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->FightSound_Label->dropShadowEffect->enable();
            $this->form('maingame')->Options->content->FightSound_Switcher_Btn->dropShadowEffect->enable();
            //exit_dlg
            $this->form('maingame')->ExitDialog->content->main_frame->dropShadowEffect->enable();
                       
            return;
        }
    }
    /**
     * @event Shadows_Switcher_Btn.mouseUp-Left 
     */
    function ShadowsSwitcher_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->ShadowsSwitcher_MouseExit();
        $this->ShadowsSwitcher_MouseEnter();
    }
    /**
     * @event Version_Switcher_Btn.mouseExit 
     */
    function VersionSwitcher_MouseExit(UXMouseEvent $e = null)
    {
        $this->Version_Switcher_Btn->textColor = ($this->Version_Switcher_Btn->text == 'Выкл') ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event Version_Switcher_Btn.mouseEnter 
     */
    function VersionSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Version_Switcher_Btn->textColor = ($this->Version_Switcher_Btn->text == 'Выкл') ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event Version_Switcher_Btn.mouseDown-Left 
     */
    function VersionSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($this->Version_Switcher_Btn->text == 'Вкл')
        {
            $this->Version_Switcher_Btn->text = 'Выкл';
            
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {
                $this->Version_Switcher_Btn->textColor = '#880911';
            }
            else 
            {
                $this->VersionSwitcher_MouseEnter();
            }
            
            if (Debug_Build)
            {
                $this->form('maingame')->version->hide();
                $this->form('maingame')->version_detail->hide();
            }
            else 
            {  
                $this->form('maingame')->MainMenu->content->version->hide();
                $this->form('maingame')->MainMenu->content->version_detail->hide();
            }
            
            return;
        }
        else 
        {
            $this->Version_Switcher_Btn->text = 'Вкл';
            
            if (!$this->form('maingame')->Console->visible && $this->form('maingame')->Console->content->edit->focused == false) //костыль ебучий
            {
                $this->Version_Switcher_Btn->textColor = '#099209';
            }
            else 
            {
                $this->VersionSwitcher_MouseEnter();
            }            
            
            if (Debug_Build)
            {
                $this->form('maingame')->version->show();
                $this->form('maingame')->version_detail->show();           
            }
            else
            {  
                $this->form('maingame')->MainMenu->content->version->show();
                $this->form('maingame')->MainMenu->content->version_detail->show();
            }
           
            return;
        }
    }
    /**
     * @event Version_Switcher_Btn.mouseUp-Left 
     */
    function VersionSwitcher_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->VersionSwitcher_MouseExit();
        $this->VersionSwitcher_MouseEnter();
    }
}
