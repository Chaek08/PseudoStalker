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
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function InitOptions()
    {   
        $GLOBALS['ShadowsSwitcher_IsOn'] = ($this->form('Client')->ltx['r_shadows'] ?? 'off') !== 'on';
        $this->ShadowsSwitcher();

        $GLOBALS['VersionSwitcher_IsOn'] = ($this->form('Client')->ltx['r_version'] ?? 'off') !== 'on';
        $this->VersionSwitcher();
        
        $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
        $GLOBALS['FightSoundSwitcher_IsOn'] = true;
                          
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
           
        if ($this->form('Client')->ltx['all_sounds'] == 'off')
        {
            $this->AllSoundSwitcher();
        }  
        if ($this->form('Client')->ltx['mm_sound'] == 'off' && $this->form('Client')->ltx['all_sounds'] == 'on')
        {
            $this->MenuSoundSwitcher();
        }        
        if ($this->form('Client')->ltx['fight_sound'] == 'off' && $this->form('Client')->ltx['all_sounds'] == 'on')
        {
            $this->FightSoundSwitcher();
        }
        
        $this->Language_Switcher_Combobobx->value = ($this->form('Client')->ltx['language'] == 'rus') ? 'Русский' : 'English';           
    }
    function SyncSwitcherStyles()
    {
        $this->AllSound_Switcher_Btn->classesString = $GLOBALS['AllSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->MenuSound_Switcher_Btn->classesString = $GLOBALS['MenuSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
        $this->FightSound_Switcher_Btn->classesString = $GLOBALS['FightSoundSwitcher_IsOn'] ? 'switch-on' : 'switch-off';
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
            $this->AllSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->AllSound_Switcher_Btn->classesString = 'switch-off';
            
            $GLOBALS['AllSounds'] = false;
        
            if ($this->form('Client')->ltx['mm_sound'] != 'on')
            {
                if ($this->MenuSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
                {
                    if ($this->form('Client')->ltxInitialized == false)
                    {
                        if ($this->form('Client')->ltx['mm_sound'] == 'off')
                        {
                            $this->MenuSoundSwitcher();
                        }
                    }
                    else
                    {
                        $this->MenuSoundSwitcher();
                    }
                }    
            }

            if ($this->form('Client')->ltx['fight_sound'] != 'on')
            {
                if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
                {
                    if ($this->form('Client')->ltxInitialized == false)
                    {
                        if ($this->form('Client')->ltx['fight_sound'] == 'off')
                        {
                            $this->FightSoundSwitcher();
                        }
                    }
                    else
                    {
                        $this->FightSoundSwitcher();
                    }
                }
            }
            
            $this->form('Client')->StopAllSounds();
            
            $this->form('Client')->ltx['all_sounds'] = 'off';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
            return;
        }
        else 
        {
            $this->AllSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->AllSound_Switcher_Btn->classesString = 'switch-on';
            $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        
            $GLOBALS['AllSounds'] = true;
        
            if ($this->MenuSound_Switcher_Btn->text == $this->localization->get('TurnOff_Label'))
            {
                if ($this->form('Client')->ltx['mm_sound'] != 'on')
                {
                    $this->MenuSoundSwitcher();
                }
            }

            if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOff_Label'))
            {
                if ($this->form('Client')->ltx['fight_sound'] != 'on')
                {
                    $this->FightSoundSwitcher();
                }
            } 
            
            $this->form('Client')->ltx['all_sounds'] = 'on';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
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
            $this->MenuSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->MenuSound_Switcher_Btn->classesString = 'switch-off';
            
            $GLOBALS['MenuSound'] = false;
            Media::stop($this->form('Client')->MainMenu->content->MenuSound);
            
            $this->form('Client')->ltx['mm_sound'] = 'off';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
            $this->MenuSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->MenuSound_Switcher_Btn->classesString = 'switch-on';
            
            $GLOBALS['MenuSound'] = true;
            Media::play($this->form('Client')->MainMenu->content->MenuSound);
            
            $this->form('Client')->ltx['mm_sound'] = 'on';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
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
            $this->FightSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->FightSound_Switcher_Btn->classesString = 'switch-off';           
            
            $GLOBALS['FightSound'] = false;
            
            $this->form('Client')->ltx['fight_sound'] = 'off';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);             
            
            return;
        }
        else 
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = true;
            $this->FightSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->FightSound_Switcher_Btn->classesString = 'switch-on';            
            
            $GLOBALS['FightSound'] = true;
            
            $this->form('Client')->ltx['fight_sound'] = 'on';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
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
            $this->Shadows_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->Shadows_Switcher_Btn->classesString = 'switch-off';
            
            //Client
            $this->form('Client')->MainGame->content->item_vodka_0000->dropShadowEffect->disable();   
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
            $this->form('Client')->Inventory->content->main->dropShadowEffect->disable();   
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
            $this->form('Client')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->disable();
            $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->disable();
            
            $this->form('Client')->ltx['r_shadows'] = 'off';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = true;
            
            $this->Shadows_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Shadows_Switcher_Btn->classesString = 'switch-on';
            
            //Client
            $this->form('Client')->MainGame->content->item_vodka_0000->dropShadowEffect->enable();
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
            $this->form('Client')->Inventory->content->main->dropShadowEffect->enable();   
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
            $this->form('Client')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->enable();
            $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->enable();  
            
            $this->form('Client')->ltx['r_shadows'] = 'on';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);                      
                       
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
         
            $this->Version_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->Version_Switcher_Btn->classesString = 'switch-off';
            
            if (Debug_Build)
            {
                $this->form('Client')->version->hide();
                $this->form('Client')->version_detail->hide();
            }
            else 
            {  
                $this->form('Client')->MainMenu->content->version->hide();
                $this->form('Client')->MainMenu->content->version_detail->hide();
            }
            
            $this->form('Client')->ltx['r_version'] = 'off';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['VersionSwitcher_IsOn'] = true;
                     
            $this->Version_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Version_Switcher_Btn->classesString = 'switch-on';         
            
            if (Debug_Build)
            {
                $this->form('Client')->version->show();
                $this->form('Client')->version_detail->show();           
            }
            else
            {  
                $this->form('Client')->MainMenu->content->version->show();
                $this->form('Client')->MainMenu->content->version_detail->show();
            }
            
            $this->form('Client')->ltx['r_version'] = 'on';
            $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);            
           
            return;
        }
    } 
    /**
     * @event Language_Switcher_Combobobx.action 
     */
    function LanguageSwitcherCombobobx(UXEvent $e = null)
    {
        $language_box = $this->Language_Switcher_Combobobx->value;
        if ($language_box == 'Русский' || $language_box == 'English')
        {
            $this->localization->setLanguage($language_box);
        }
        
        $this->form('Client')->ltx['language'] = $this->localization->getCurrentLanguage();
        $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);
        
        $this->form('Client')->ShowLoadScreen(function()
        {
            $this->UpdateLocalization();
        });
    }

    function UpdateLocalization()
    {
        $this->Return_Btn->text = $this->localization->get('Return_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Return_Btn->text = $this->localization->get('Return_Btn');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Return_Btn->text = $this->localization->get('Return_Btn');
      
        $this->AllSound_Label->text = $this->localization->get('AllSound_Label');
        $this->MenuSound_Label->text = $this->localization->get('MenuSound_Label');
        $this->FightSound_Label->text = $this->localization->get('FightSound_Label');
        $this->Shadows_Label->text = $this->localization->get('Shadows_Label');
        $this->Version_Label->text = $this->localization->get('Version_Label');
        $this->Language_Label->text = $this->localization->get('Language_Label');

        $this->AllSound_Switcher_Btn->text = $this->localization->get($GLOBALS['AllSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->MenuSound_Switcher_Btn->text = $this->localization->get($GLOBALS['MenuSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->FightSound_Switcher_Btn->text = $this->localization->get($GLOBALS['FightSoundSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->Shadows_Switcher_Btn->text = $this->localization->get($GLOBALS['ShadowsSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');
        $this->Version_Switcher_Btn->text = $this->localization->get($GLOBALS['VersionSwitcher_IsOn'] ? 'TurnOn_Label' : 'TurnOff_Label');

        $this->form('Client')->MainMenu->content->Btn_Start_Game->text = $this->localization->get(($GLOBALS['NewGameState'] ? 'NewGame_Label' : 'ContinueGame_Label'));
        $this->form('Client')->MainMenu->content->Btn_Save_Game->text = $this->localization->get('SaveGame_Label');
        $this->form('Client')->MainMenu->content->Btn_Load_Game->text = $this->localization->get('LoadGame_Label');
        $this->form('Client')->MainMenu->content->Btn_End_Game->text = $this->localization->get('EndGame_Label');
        $this->form('Client')->MainMenu->content->Btn_Opt->text = $this->localization->get('Options_Label');
        $this->form('Client')->MainMenu->content->Btn_Exit_Windows->text = $this->localization->get('ExitToWindows_Label');
        
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Wnd_Label->text = $this->localization->get('SaveGame_Label');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Wnd_Label->text = $this->localization->get('LoadGame_Label');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Save_Btn->text = $this->localization->get('Save_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Load_Btn->text = $this->localization->get('Load_Btn');
        $this->form('Client')->MainMenu->content->UISaveWnd->content->Remove_Save_Btn->text = $this->localization->get('Remove_Btn');
        $this->form('Client')->MainMenu->content->UILoadWnd->content->Remove_Save_Btn->text = $this->localization->get('Remove_Btn');

        $this->form('Client')->MainGame->content->Talk_Label->text = $this->localization->get('Talk_Label');
        $this->form('Client')->MainGame->content->leave_btn->text = $this->localization->get('Leave_Label');

        $this->form('Client')->Inventory->content->time_label->text = $this->localization->get('Time_Label');
        $this->form('Client')->Inventory->content->button5->text = $this->localization->get('Inventory_Label');
        $this->form('Client')->Inventory->content->button6->text = $this->localization->get('Item_Label');
        $this->form('Client')->Inventory->content->button7->text = $this->localization->get('Equipment_Label');
        $this->form('Client')->Inventory->content->Combobox_Drop->text = $this->localization->get('Drop_Label');
        $this->form('Client')->Inventory->content->Combobox_Use->text = $this->localization->get('Use_Label');
        $this->form('Client')->Inventory->content->Combobox_TakeOff->text = $this->localization->get('TakeOff_Label');
        $this->form('Client')->Inventory->content->Combobox_PutOn->text = $this->localization->get('PutOn_Label');
        $this->form('Client')->Inventory->content->Combobox_MoveToSlot->text = $this->localization->get('MoveToSlot_Label');        
        $this->form('Client')->Inventory->content->maket_cond_label->text = $this->localization->get('Condition_Label');

        $this->form('Client')->Pda->content->tasks_label->text = $this->localization->get('Tasks_Label');
        $this->form('Client')->Pda->content->contacts_label->text = $this->localization->get('Contacts_Label');
        $this->form('Client')->Pda->content->ranks_label->text = $this->localization->get('Ranks_Label');
        $this->form('Client')->Pda->content->stat_label->text = $this->localization->get('Data_Label');
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->task_label->text = $this->localization->get('DefeatEnemy_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->step1->text = $this->localization->get('TalkToGoblin_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->step2->text = $this->localization->get('DefeatGoblin_Task');
        $this->form('Client')->Pda->content->Pda_Tasks->content->task_detail_text->text = $this->localization->get('TaskDetails');
        $this->form('Client')->Pda->content->Pda_Tasks->content->active_task->text = $this->localization->get('ActiveTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->passive_task->text = $this->localization->get('CompletedTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->failed_task->text = $this->localization->get('FailedTasks_Label');
        $this->form('Client')->Pda->content->Pda_Tasks->content->tab_button->text = $this->localization->get('Tasks_Label');

        $this->form('Client')->Pda->content->Pda_Contacts->content->name->text = $this->localization->get('Contact_Goblin');
        $this->form('Client')->Pda->content->Pda_Contacts->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Contacts->content->reputation_desc->text = $this->localization->get('Reputation_Desc');
        $this->form('Client')->Pda->content->Pda_Contacts->content->reputation->text = $this->localization->get('Reputation_Bad');
        $this->form('Client')->Pda->content->Pda_Contacts->content->relationship_desc->text = $this->localization->get('Attitude_Label');
        $this->form('Client')->Pda->content->Pda_Contacts->content->relationship->text = $this->localization->get('Relationship_Enemy');
        $this->form('Client')->Pda->content->Pda_Contacts->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Contacts->content->rank->text = $this->localization->get('Rank_Veterinarian');
        $this->form('Client')->Pda->content->Pda_Contacts->content->bio->text = $this->localization->get('GoblindaV_Bio');
        $this->form('Client')->Pda->content->Pda_Contacts->content->tab_button->text = $this->localization->get('Contacts_Label');
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->name_label->text = $this->localization->get('Name_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->rank_label->text = $this->localization->get('Rank_Bio_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Ranking->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->attitude->text = $this->localization->get('Attitude_Label');
        $this->form('Client')->Pda->content->Pda_Ranking->content->actor_in_raiting_name->text = $this->localization->get('GG_Name');
        $this->form('Client')->Pda->content->Pda_Ranking->content->valerok_in_raiting_name->text = $this->localization->get('Ranking_Valerok');
        $this->form('Client')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_name->text = $this->localization->get('Enemy_Name');
        $this->form('Client')->Pda->content->Pda_Ranking->content->tab_button->text = $this->localization->get('Ranks_Label');
    
        $this->form('Client')->Pda->content->Pda_Statistic->content->tab_button->text = $this->localization->get('GG_Name');
        $this->form('Client')->Pda->content->Pda_Statistic->content->Statistic_Label->text = $this->localization->get('Statistic_Label');
        $this->form('Client')->Pda->content->Pda_Statistic->content->statistic->text = $this->localization->get('Statistic_Details');
        $this->form('Client')->Pda->content->Pda_Statistic->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('Client')->Pda->content->Pda_Statistic->content->rank->text = $this->localization->get('Rank_Master');
        $this->form('Client')->Pda->content->Pda_Statistic->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('Client')->Pda->content->Pda_Statistic->content->reputation_desc->text = $this->localization->get('Reputation_Desc');
        $this->form('Client')->Pda->content->Pda_Statistic->content->reputation->text = $this->localization->get('Statistic_Reputation_Excellent');
        $this->form('Client')->Pda->content->Pda_Statistic->content->buttonAlt->text = $this->localization->get('Info_Button');
        $this->form('Client')->Pda->content->Pda_Statistic->content->tab_final->text = $this->localization->get('Timeline_Tab');
        $this->form('Client')->Pda->content->Pda_Statistic->content->target_label->text = $this->localization->get('Target_Label');

        $this->form('Client')->Dialog->content->answer_desc->text = $this->localization->get('Dialog_Actor_Desc1');
        $this->form('Client')->Dialog->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('Client')->Dialog->content->rank->text = $this->localization->get('Rank_Master');
        $this->form('Client')->Dialog->content->labelAlt->text = $this->localization->get('Rank_Veterinarian');
        $this->form('Client')->Dialog->content->label->text = $this->localization->get('Rank_Desc');
        $this->form('Client')->Dialog->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('Client')->Dialog->content->label3->text = $this->localization->get('Group_Label');
    
        $this->form('Client')->Fail->content->returnbtn->text = $this->localization->get('Return_Button');
        $this->form('Client')->Fail->content->exitbtn->text = $this->localization->get('Exit_Button');
            
        if ($GLOBALS['QuestCompleted']) 
        {
            $this->form('Client')->Fail->content->UpdateFailState();
            $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
            
            $this->form('Client')->Task_Step_Label->text = $this->localization->get('No_Active_Task');
        }
        
        $this->form('Client')->MainMenu->content->UILoadWnd->content->ShowSavePreview();
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->UpdateData();
        
        if ($this->form('Client')->MainMenu->visible)
        {
            $GLOBALS['discord']->setDetails($this->localization->get('RPC_MainMenu'));
        }
        else 
        {
            $GLOBALS['discord']->setDetails($this->localization->get('RPC_Ingame'));
        }
        if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
        {
            $GLOBALS['discord']->setState($this->localization->get('RPC_Fight'));
        }
        else 
        {
            $GLOBALS['discord']->setState(null);
        }
        $GLOBALS['discord']->updateState();
    }
}
