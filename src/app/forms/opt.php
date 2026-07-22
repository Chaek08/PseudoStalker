<?php
namespace app\forms;

use Exception;
use std, gui, framework, app;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXEvent;
use app\forms\classes\Localization;

class opt extends AbstractForm
{
    function InitOptions()
    {
        $this->form('Client')->ltx;

        $GLOBALS['ShadowsSwitcher_IsOn'] = !$this->form('Client')->ltx->r_bool('r_shadows');
        $this->ShadowsSwitcher();

        $GLOBALS['VersionSwitcher_IsOn'] = !$this->form('Client')->ltx->r_bool('r_version');
        $this->VersionSwitcher();

        $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
        $GLOBALS['FightSoundSwitcher_IsOn'] = true;
        $GLOBALS['AmbientSoundSwitcher_IsOn'] = true;

        if (!$GLOBALS['AllSounds'])
        {
            $this->AllSoundSwitcher();
            return;
        }

        if (!$GLOBALS['MenuSound'])
        {
            $this->MenuSoundSwitcher();
        }

        if (!$GLOBALS['FightSound'])
        {
            $this->FightSoundSwitcher();
        }

        if (!$GLOBALS['AmbientSound'])
        {
            $this->AmbientSoundSwitcher();
        }

        if (!$this->form('Client')->ltx->r_bool('all_sounds'))
        {
            $this->AllSoundSwitcher();
        }

        if (!$this->form('Client')->ltx->r_bool('mm_sound') && $this->form('Client')->ltx->r_bool('all_sounds'))
        {
            $this->MenuSoundSwitcher();
        }

        if (!$this->form('Client')->ltx->r_bool('fight_sound') && $this->form('Client')->ltx->r_bool('all_sounds'))
        {
            $this->FightSoundSwitcher();
        }

        if (!$this->form('Client')->ltx->r_bool('ambient_sound') && $this->form('Client')->ltx->r_bool('all_sounds'))
        {
            $this->AmbientSoundSwitcher();
        }

        $lang = $this->form('Client')->ltx->r_string('language');
        
        foreach (Localization::getDisplayLanguages() as $lang)
        {
            $this->Language_Switcher_Combobobx->items->add($lang);
        }
        
        $this->Language_Switcher_Combobobx->value = Localization::getDisplayLanguage();
    }

    function SyncSwitcherStyles()
    {
        $this->AllSound_Switcher_Btn->classesString = $GLOBALS['AllSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->MenuSound_Switcher_Btn->classesString = $GLOBALS['MenuSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->FightSound_Switcher_Btn->classesString = $GLOBALS['FightSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->AmbientSound_Switcher_Btn->classesString = $GLOBALS['AmbientSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';        
        $this->Shadows_Switcher_Btn->classesString = $GLOBALS['ShadowsSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->Version_Switcher_Btn->classesString = $GLOBALS['VersionSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
    }    
    /**
     * @event Return_Btn.mouseDown-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('Client')->MainMenu->content->dynamic_background->toBack();
        $this->form('Client')->MainMenu->content->Options->hide();
    }
    /**
     * @event AllSound_Switcher_Btn.mouseDown-Left 
     */
    function AllSoundSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['AllSoundSwitcher_IsOn'])
        {
            $GLOBALS['AllSoundSwitcher_IsOn'] = false;       
            $this->AllSound_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->AllSound_Switcher_Btn->classesString = 'switch-off';
            
            $GLOBALS['AllSounds'] = false;
        
            if ($GLOBALS['MenuSound'])
            {
                $this->MenuSoundSwitcher();
                $this->MenuSound_Switcher_Btn->enabled = false;
            }
            if ($GLOBALS['FightSound'])
            {
                $this->FightSoundSwitcher();
                $this->FightSound_Switcher_Btn->enabled = false;
            }
            if ($GLOBALS['AmbientSound'])
            {
                $this->AmbientSoundSwitcher();
                $this->AmbientSound_Switcher_Btn->enabled = false;
            }            
            
            $this->form('Client')->ltx->w_string('all_sounds', 'off');
            $this->form('Client')->ltx->save();              
            
            return;
        }
        else 
        {
            $this->AllSound_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->AllSound_Switcher_Btn->classesString = 'switch-on';
            $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        
            $GLOBALS['AllSounds'] = true;
        
            if (!$GLOBALS['MenuSound'])
            {
                $this->MenuSoundSwitcher();
                $this->MenuSound_Switcher_Btn->enabled = true;
            }
            if (!$GLOBALS['FightSound'])
            {
                $this->FightSoundSwitcher();
                $this->FightSound_Switcher_Btn->enabled = true;
            }
            if (!$GLOBALS['AmbientSound'])
            {
                $this->AmbientSoundSwitcher();
                $this->AmbientSound_Switcher_Btn->enabled = true;
            }             
            
            $this->form('Client')->ltx->w_string('all_sounds', 'on');
            $this->form('Client')->ltx->save();               
            
            return;
        }
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseDown-Left 
     */
    function MenuSoundSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['MenuSoundSwitcher_IsOn'])
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = false;
            $this->MenuSound_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->MenuSound_Switcher_Btn->classesString = 'switch-off';
            
            $GLOBALS['MenuSound'] = false;
            $this->form('Client')->MainMenu->content->menuPlayer->pause();
            
            $this->form('Client')->ltx->w_string('mm_sound', 'off');
            $this->form('Client')->ltx->save();              
            
            return;
        }
        else 
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
            $this->MenuSound_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->MenuSound_Switcher_Btn->classesString = 'switch-on';
            
            $GLOBALS['MenuSound'] = true;
            if ($this->form('Client')->MainMenu->visible)
            {
                $this->form('Client')->MainMenu->content->menuPlayer->play();
            }
            
            $this->form('Client')->ltx->w_string('mm_sound', 'on');
            $this->form('Client')->ltx->save();            
            
            return;
        }
    }
    /**
     * @event FightSound_Switcher_Btn.mouseDown-Left 
     */
    function FightSoundSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['FightSoundSwitcher_IsOn'])
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = false;
            $this->FightSound_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->FightSound_Switcher_Btn->classesString = 'switch-off';           
            
            $GLOBALS['FightSound'] = false;
            
            $this->form('Client')->ltx->w_string('fight_sound', 'off');
            $this->form('Client')->ltx->save();              
            
            return;
        }
        else 
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = true;
            $this->FightSound_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->FightSound_Switcher_Btn->classesString = 'switch-on';            
            
            $GLOBALS['FightSound'] = true;
            
            $this->form('Client')->ltx->w_string('fight_sound', 'on');
            $this->form('Client')->ltx->save();            
            
            return;
        }
    }
    
    /**
     * @event AmbientSound_Switcher_Btn.mouseDown-Left 
     */
    function AmbientSoundSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['AmbientSoundSwitcher_IsOn'])
        {
            $GLOBALS['AmbientSoundSwitcher_IsOn'] = false;
            $this->AmbientSound_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->AmbientSound_Switcher_Btn->classesString = 'switch-off';
            
            $GLOBALS['AmbientSound'] = false;
            
            $this->form('Client')->ltx->w_string('ambient_sound', 'off');
            $this->form('Client')->ltx->save();             
            
            return;
        }
        else 
        {
            $GLOBALS['AmbientSoundSwitcher_IsOn'] = true;
            $this->AmbientSound_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->AmbientSound_Switcher_Btn->classesString = 'switch-on';
            
            $GLOBALS['AmbientSound'] = true;
            
            $this->form('Client')->ltx->w_string('ambient_sound', 'on');
            $this->form('Client')->ltx->save();          
            
            return;
        }
    }    
    
    /**
     * @event Shadows_Switcher_Btn.mouseDown-Left 
     */
    function ShadowsSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['ShadowsSwitcher_IsOn'])
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = false;        
            $this->Shadows_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->Shadows_Switcher_Btn->classesString = 'switch-off';
            
            //Client
            uiLater(function () {
                $this->form('Client')->MainGame->content->ItemVodka->disableShadow();  
                $this->form('Client')->MainGame->content->actor->dropShadowEffect->disable();  
                $this->form('Client')->MainGame->content->enemy->dropShadowEffect->disable();   
                $this->form('Client')->MainGame->content->Talk_Label->dropShadowEffect->disable();   
                $this->form('Client')->MainGame->content->SavedGame_Toast->dropShadowEffect->disable();
                $this->form('Client')->MainGame->content->leave_btn->dropShadowEffect->disable();  
                $this->form('Client')->MainGame->content->health_static_enemy->dropShadowEffect->disable();     
                $this->form('Client')->MainGame->content->health_static_gg->dropShadowEffect->disable();     
                $this->form('Client')->MainGame->content->health_bar_enemy->dropShadowEffect->disable();     
                $this->form('Client')->MainGame->content->health_bar_gg->dropShadowEffect->disable();          
                $this->form('Client')->MainGame->content->fight_image->dropShadowEffect->disable();       
                //dialog
                $this->form('Client')->Dialog->content->actor_character->dropShadowEffect->disable();
                $this->form('Client')->Dialog->content->alex_character->dropShadowEffect->disable();
                $this->form('Client')->Dialog->content->icon_enemy->dropShadowEffect->disable(); 
                $this->form('Client')->Dialog->content->icon_gg->dropShadowEffect->disable();      
                //pda + all fragments
                $this->form('Client')->Pda->content->toolbar_frame_main->dropShadowEffect->disable();
                $this->form('Client')->Pda->content->toolbar_frame_time->dropShadowEffect->disable();
                $this->form('Client')->Pda->content->Pda_Tasks->content->frame_01->dropShadowEffect->disable(); 
                $this->form('Client')->Pda->content->Pda_Tasks->content->frame_detail_02->dropShadowEffect->disable();  
                $this->form('Client')->Pda->content->Pda_Contacts->content->frame_01->dropShadowEffect->disable();    
                $this->form('Client')->Pda->content->Pda_Contacts->content->button->dropShadowEffect->disable();    
                $this->form('Client')->Pda->content->Pda_Contacts->content->icon->dropShadowEffect->disable();                                                 
                $this->form('Client')->Pda->content->Pda_Ranking->content->user_icon->dropShadowEffect->disable(); 
                $this->form('Client')->Pda->content->Pda_Ranking->content->frame_01->dropShadowEffect->disable();    
                $this->form('Client')->Pda->content->Pda_Ranking->content->button->dropShadowEffect->disable();   
                $this->form('Client')->Pda->content->Pda_Statistic->content->frame_01->dropShadowEffect->disable();    
                $this->form('Client')->Pda->content->Pda_Statistic->content->button->dropShadowEffect->disable();  
                $this->form('Client')->Pda->content->Pda_Statistic->content->icon->dropShadowEffect->disable();   
                //inventory
                $this->form('Client')->Inventory->content->health_bar_gg->dropShadowEffect->disable();   
                $this->form('Client')->Inventory->content->health_static_gg->dropShadowEffect->disable(); 
                $this->form('Client')->Inventory->content->contextMenu->dropShadowEffect->disable();
              
                //fail wnd             
                $this->form('Client')->Fail->content->Win_fail_desc->dropShadowEffect->disable(); 
                $this->form('Client')->Fail->content->Win_fail_text->dropShadowEffect->disable();   
                $this->form('Client')->Fail->content->Win_object->dropShadowEffect->disable(); 
                $this->form('Client')->Fail->content->exitbtn->dropShadowEffect->disable(); 
                $this->form('Client')->Fail->content->returnbtn->dropShadowEffect->disable(); 
                //mainmenu + opt                
                $this->form('Client')->MainMenu->content->Btn_Exit_Windows->dropShadowEffect->disable();    
                $this->form('Client')->MainMenu->content->Btn_Start_Game->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Btn_End_Game->dropShadowEffect->disable();          
                $this->form('Client')->MainMenu->content->Btn_Opt->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Btn_Save_Game->dropShadowEffect->disable();          
                $this->form('Client')->MainMenu->content->Btn_Load_Game->dropShadowEffect->disable();                     
                $this->form('Client')->MainMenu->content->logo->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->Return_Btn->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->AllSound_Label->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->AllSound_Switcher_Btn->dropShadowEffect->disable();              
                $this->form('Client')->MainMenu->content->Options->content->MenuSound_Label->dropShadowEffect->disable(); 
                $this->form('Client')->MainMenu->content->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->disable();            
                $this->form('Client')->MainMenu->content->Options->content->Shadows_Label->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->Shadows_Switcher_Btn->dropShadowEffect->disable();                
                $this->form('Client')->MainMenu->content->Options->content->Version_Label->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->Version_Switcher_Btn->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->FightSound_Label->dropShadowEffect->disable();    
                $this->form('Client')->MainMenu->content->Options->content->FightSound_Switcher_Btn->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->AmbientSound_Label->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->AmbientSound_Switcher_Btn->dropShadowEffect->disable();                
                $this->form('Client')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->disable();
                $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->disable();
                
                $w = $this->form('Client')->MainGame->content->GameActor->getWeapon();
                if ($w && $w->dropShadowEffect)
                {
                    $w->dropShadowEffect->disable();
                }
            }); 
            
            $this->form('Client')->ltx->w_string('r_shadows', 'off');
            $this->form('Client')->ltx->save();          
            
            return;
        }
        else 
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = true;
            
            $this->Shadows_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->Shadows_Switcher_Btn->classesString = 'switch-on';
            
            uiLater(function () {
                //Client
                $this->form('Client')->MainGame->content->ItemVodka->enableShadow();
                $this->form('Client')->MainGame->content->actor->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->enemy->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->Talk_Label->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->SavedGame_Toast->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->leave_btn->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->health_static_enemy->dropShadowEffect->enable();     
                $this->form('Client')->MainGame->content->health_static_gg->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->health_bar_enemy->dropShadowEffect->enable();
                $this->form('Client')->MainGame->content->health_bar_gg->dropShadowEffect->enable();          
                $this->form('Client')->MainGame->content->fight_image->dropShadowEffect->enable();       
                //dialog
                $this->form('Client')->Dialog->content->actor_character->dropShadowEffect->enable();
                $this->form('Client')->Dialog->content->alex_character->dropShadowEffect->enable();
                $this->form('Client')->Dialog->content->icon_enemy->dropShadowEffect->enable(); 
                $this->form('Client')->Dialog->content->icon_gg->dropShadowEffect->enable();      
                //pda + all fragments
                $this->form('Client')->Pda->content->toolbar_frame_main->dropShadowEffect->enable();
                $this->form('Client')->Pda->content->toolbar_frame_time->dropShadowEffect->enable();
                $this->form('Client')->Pda->content->Pda_Tasks->content->frame_01->dropShadowEffect->enable(); 
                $this->form('Client')->Pda->content->Pda_Tasks->content->frame_detail_02->dropShadowEffect->enable();  
                $this->form('Client')->Pda->content->Pda_Contacts->content->frame_01->dropShadowEffect->enable();    
                $this->form('Client')->Pda->content->Pda_Contacts->content->button->dropShadowEffect->enable();    
                $this->form('Client')->Pda->content->Pda_Contacts->content->icon->dropShadowEffect->enable();                                                 
                $this->form('Client')->Pda->content->Pda_Ranking->content->user_icon->dropShadowEffect->enable(); 
                $this->form('Client')->Pda->content->Pda_Ranking->content->frame_01->dropShadowEffect->enable();    
                $this->form('Client')->Pda->content->Pda_Ranking->content->button->dropShadowEffect->enable();   
                $this->form('Client')->Pda->content->Pda_Statistic->content->frame_01->dropShadowEffect->enable();    
                $this->form('Client')->Pda->content->Pda_Statistic->content->button->dropShadowEffect->enable();  
                $this->form('Client')->Pda->content->Pda_Statistic->content->icon->dropShadowEffect->enable();   
                //inventory
                $this->form('Client')->Inventory->content->health_bar_gg->dropShadowEffect->enable();   
                $this->form('Client')->Inventory->content->health_static_gg->dropShadowEffect->enable(); 
                $this->form('Client')->Inventory->content->contextMenu->dropShadowEffect->enable();
                //fail wnd             
                $this->form('Client')->Fail->content->Win_fail_desc->dropShadowEffect->enable(); 
                $this->form('Client')->Fail->content->Win_fail_text->dropShadowEffect->enable();   
                $this->form('Client')->Fail->content->Win_object->dropShadowEffect->enable(); 
                $this->form('Client')->Fail->content->exitbtn->dropShadowEffect->enable(); 
                $this->form('Client')->Fail->content->returnbtn->dropShadowEffect->enable(); 
                //mainmenu + opt                
                $this->form('Client')->MainMenu->content->Btn_Exit_Windows->dropShadowEffect->enable();    
                $this->form('Client')->MainMenu->content->Btn_Start_Game->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Btn_End_Game->dropShadowEffect->enable();          
                $this->form('Client')->MainMenu->content->Btn_Opt->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Btn_Save_Game->dropShadowEffect->enable();          
                $this->form('Client')->MainMenu->content->Btn_Load_Game->dropShadowEffect->enable();                  
                $this->form('Client')->MainMenu->content->logo->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->Return_Btn->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->AllSound_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->AllSound_Switcher_Btn->dropShadowEffect->enable();              
                $this->form('Client')->MainMenu->content->Options->content->MenuSound_Label->dropShadowEffect->enable(); 
                $this->form('Client')->MainMenu->content->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->enable();            
                $this->form('Client')->MainMenu->content->Options->content->Shadows_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->Shadows_Switcher_Btn->dropShadowEffect->enable();                
                $this->form('Client')->MainMenu->content->Options->content->Version_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->Version_Switcher_Btn->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->FightSound_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->FightSound_Switcher_Btn->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->AmbientSound_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->AmbientSound_Switcher_Btn->dropShadowEffect->enable();            
                $this->form('Client')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->enable();
                $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->enable();  
                
                $w = $this->form('Client')->MainGame->content->GameActor->getWeapon();
                if ($w && $w->dropShadowEffect)
                {
                    $w->dropShadowEffect->enable();
                }

            }); 
            
            $this->form('Client')->ltx->w_string('r_shadows', 'on');
            $this->form('Client')->ltx->save();
                       
            return;
        }      
    }
    /**
     * @event Version_Switcher_Btn.mouseDown-Left 
     */
    function VersionSwitcher(UXMouseEvent $e = null)
    {
        if ($GLOBALS['VersionSwitcher_IsOn'])
        {
            $GLOBALS['VersionSwitcher_IsOn'] = false;
         
            $this->Version_Switcher_Btn->text = Localization::get('TurnOff_Label');
            $this->Version_Switcher_Btn->classesString = 'switch-off';
            
            if (Debug_Build)
            {
                $this->form('Client')->DebugUtilities->content->version->hide();
                $this->form('Client')->DebugUtilities->content->version_detail->hide();
            }
            else 
            {  
                $this->form('Client')->MainMenu->content->version->hide();
                $this->form('Client')->MainMenu->content->version_detail->hide();
            }
            
            $this->form('Client')->ltx->w_string('r_version', 'off');
            $this->form('Client')->ltx->save();        
            
            return;
        }
        else 
        {
            $GLOBALS['VersionSwitcher_IsOn'] = true;
                     
            $this->Version_Switcher_Btn->text = Localization::get('TurnOn_Label');
            $this->Version_Switcher_Btn->classesString = 'switch-on';         
            
            if (Debug_Build)
            {
                $this->form('Client')->DebugUtilities->content->version->show();
                $this->form('Client')->DebugUtilities->content->version_detail->show();           
            }
            else
            {  
                $this->form('Client')->MainMenu->content->version->show();
                $this->form('Client')->MainMenu->content->version_detail->show();
            }
            
            $this->form('Client')->ltx->w_string('r_version', 'on');
            $this->form('Client')->ltx->save();  
           
            return;
        }
    } 
    
    /**
     * @event Language_Switcher_Combobobx.action 
     */
    function LanguageSwitcherCombobobx(UXEvent $e = null)
    {
        $code = Localization::getLanguageCode($this->Language_Switcher_Combobobx->value);
        
        if ($code !== null)
        {
            Localization::setLanguage($code);
        }
        
        $this->form('Client')->ltx->w_string('language', Localization::getCurrentLanguage());
        $this->form('Client')->ltx->save();
        
        $this->form('Client')->ShowLoadScreen(function()
        {
            $this->UpdateLocalization();
        });
    }

    function UpdateLocalization()
    {
        $this->Return_Btn->text = Localization::get('Return_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Return_Btn->text = Localization::get('Return_Btn');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Return_Btn->text = Localization::get('Return_Btn');
      
        $this->AllSound_Label->text = Localization::get('AllSound_Label');
        $this->MenuSound_Label->text = Localization::get('MenuSound_Label');
        $this->FightSound_Label->text = Localization::get('FightSound_Label');
        $this->AmbientSound_Label->text = Localization::get('AmbientSound_Label');        
        $this->Shadows_Label->text = Localization::get('Shadows_Label');
        $this->Version_Label->text = Localization::get('Version_Label');
        $this->Language_Label->text = Localization::get('Language_Label');

        $this->AllSound_Switcher_Btn->text = Localization::get($GLOBALS['AllSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->MenuSound_Switcher_Btn->text = Localization::get($GLOBALS['MenuSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->FightSound_Switcher_Btn->text = Localization::get($GLOBALS['FightSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->AmbientSound_Switcher_Btn->text = Localization::get($GLOBALS['AmbientSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');        
        $this->Shadows_Switcher_Btn->text = Localization::get($GLOBALS['ShadowsSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->Version_Switcher_Btn->text = Localization::get($GLOBALS['VersionSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');

        $this->form('Client')->MainMenu->content->Btn_Start_Game->text = Localization::get(($GLOBALS['NewGameState'] ? 'NewGame_Label' : 'ContinueGame_Label'));
        $this->form('Client')->MainMenu->content->Btn_Save_Game->text = Localization::get('SaveGame_Label');
        $this->form('Client')->MainMenu->content->Btn_Load_Game->text = Localization::get('LoadGame_Label');
        $this->form('Client')->MainMenu->content->Btn_End_Game->text = Localization::get('EndGame_Label');
        $this->form('Client')->MainMenu->content->Btn_Opt->text = Localization::get('Options_Label');
        $this->form('Client')->MainMenu->content->Btn_Exit_Windows->text = Localization::get('ExitToWindows_Label');
        
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Wnd_Label->text = Localization::get('SaveGame_Label');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Wnd_Label->text = Localization::get('LoadGame_Label');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Save_Btn->text = Localization::get('Save_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Load_Btn->text = Localization::get('Load_Btn');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Remove_Save_Btn->text = Localization::get('Remove_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Remove_Save_Btn->text = Localization::get('Remove_Btn');

        $this->form('Client')->MainGame->content->Talk_Label->text = Localization::get('Talk_Label');
        $this->form('Client')->MainGame->content->leave_btn->text = Localization::get('Leave_Label');

        $this->form('Client')->Inventory->content->time_label->text = Localization::get('Time_Label');
        $this->form('Client')->Inventory->content->button5->text = Localization::get('Inventory_Label');
        $this->form('Client')->Inventory->content->button6->text = Localization::get('Item_Label');
        $this->form('Client')->Inventory->content->button7->text = Localization::get('Equipment_Label');      
        $this->form('Client')->Inventory->content->maket_cond_label->text = Localization::get('Condition_Label');

        $this->form('Client')->Pda->content->tasks_label->text = Localization::get('Tasks_Label');
        $this->form('Client')->Pda->content->contacts_label->text = Localization::get('Contacts_Label');
        $this->form('Client')->Pda->content->ranks_label->text = Localization::get('Ranks_Label');
        $this->form('Client')->Pda->content->stat_label->text = Localization::get('Data_Label');
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->task_label->text = Localization::get('DefeatEnemy_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->step1->text = Localization::get('TalkToGoblin_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->step2->text = Localization::get('DefeatGoblin_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->task_detail_text->text = Localization::get('TaskDetails');
        $this->form('Client')->Pda->content->Pda_Tasks->content->active_task->text = Localization::get('ActiveTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->passive_task->text = Localization::get('CompletedTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->failed_task->text = Localization::get('FailedTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->tab_button->text = Localization::get('Tasks_Label');

        $this->form('Client')->Pda->content->Pda_Contacts->content->community_desc->text = Localization::get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Contacts->content->reputation_desc->text = Localization::get('Reputation_Desc');
        $this->form('Client')->Pda->content->Pda_Contacts->content->relationship_desc->text = Localization::get('Attitude_Label');
        $this->form('Client')->Pda->content->Pda_Contacts->content->rank_desc->text = Localization::get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Contacts->content->tab_button->text = Localization::get('Contacts_Label');
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->name_label->text = Localization::get('Name_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->rank_label->text = Localization::get('Rank_Bio_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->rank_desc->text = Localization::get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Ranking->content->community_desc->text = Localization::get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->attitude->text = Localization::get('Attitude_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->tab_button->text = Localization::get('Ranks_Label');
    
        $this->form('Client')->Pda->content->Pda_Statistic->content->Statistic_Label->text = Localization::get('Statistic_Label');
        $this->form('Client')->Pda->content->Pda_Statistic->content->statistic->text = Localization::get('Statistic_Details');
        $this->form('Client')->Pda->content->Pda_Statistic->content->rank_desc->text = Localization::get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Statistic->content->community_desc->text = Localization::get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Statistic->content->reputation_desc->text = Localization::get('Reputation_Desc');
        $this->form('Client')->Pda->content->Pda_Statistic->content->buttonAlt->text = Localization::get('Info_Button');
        $this->form('Client')->Pda->content->Pda_Statistic->content->tab_final->text = Localization::get('Timeline_Tab');
        $this->form('Client')->Pda->content->Pda_Statistic->content->target_label->text = Localization::get('Target_Label');

        $this->form('Client')->Dialog->content->rank_desc->text = Localization::get('Rank_Desc');
        $this->form('Client')->Dialog->content->label->text = Localization::get('Rank_Desc');
        $this->form('Client')->Dialog->content->community_desc->text = Localization::get('Group_Label');
        $this->form('Client')->Dialog->content->label3->text = Localization::get('Group_Label');
    
        $this->form('Client')->Fail->content->returnbtn->text = Localization::get('Return_Button');
        $this->form('Client')->Fail->content->exitbtn->text = Localization::get('Exit_Button');
        $this->form('Client')->Fail->content->nextlevelbutton->text = Localization::get('NextLevel_Label');
        
        $this->form('Client')->Dialog->content->UpdateData();
            
        if ($GLOBALS['QuestCompleted']) 
        {
            $this->form('Client')->Fail->content->UpdateFailState();
            $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
            
            $this->form('Client')->MainGame->content->Task_Step_Label->text = Localization::get('No_Active_Task');
        }
        
        $this->form('Client')->MainMenu->content->UILoadWnd->content->ShowSavePreview();
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->UpdateData();
        
        if ($this->form('Client')->ltx->r_bool('discord_rpc'))
        {
            if ($this->form('Client')->MainMenu->visible)
            {
                $GLOBALS['discord']->setDetails(Localization::get('RPC_MainMenu'));
            }
            else 
            {
                $GLOBALS['discord']->setDetails(Localization::get('RPC_Ingame'));
            }
            
            if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
            {
                $GLOBALS['discord']->setState(Localization::get('RPC_Fight'));
            }
            else 
            {
                $GLOBALS['discord']->setState(null);
            }
            
            $GLOBALS['discord']->updateState();            
        }
    }
}
