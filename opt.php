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
        $GLOBALS['ShadowsSwitcher_IsOn'] = ($this->form('maingame')->ltx['r_shadows'] ?? 'off') !== 'on';
        $this->ShadowsSwitcher();

        $GLOBALS['VersionSwitcher_IsOn'] = ($this->form('maingame')->ltx['r_version'] ?? 'off') !== 'on';
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
        
        if ($this->form('maingame')->ltx['all_sounds'] == 'off')
        {
            $this->AllSoundSwitcher();
        }        
        if ($this->form('maingame')->ltx['mm_sound'] == 'off')
        {
            $this->MenuSoundSwitcher();
        }        
        if ($this->form('maingame')->ltx['fight_sound'] == 'off')
        {
            $this->FightSoundSwitcher();
        }
        
        $this->Language_Switcher_Combobobx->value = ($this->form('maingame')->ltx['language'] == 'rus') ? 'Русский' : 'English';           
    }
    /**
     * @event Return_Btn.mouseDown-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->MainMenu->content->dynamic_background->toBack();
        $this->form('maingame')->MainMenu->content->Options->hide();
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
        
            if ($this->MenuSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
            {
                $this->MenuSoundSwitcher();
            }
            if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
            {
                $this->FightSoundSwitcher();
            }
            
            $this->form('maingame')->StopAllSounds();
            
            $this->form('maingame')->ltx['all_sounds'] = 'off';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
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
                $this->MenuSoundSwitcher();
            }
            if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOff_Label'))
            {
                $this->FightSoundSwitcher();
            } 
            
            $this->form('maingame')->ltx['all_sounds'] = 'on';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
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
            Media::stop($this->form('maingame')->MainMenu->content->MenuSound);
            
            $this->form('maingame')->ltx['mm_sound'] = 'off';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
            $this->MenuSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->MenuSound_Switcher_Btn->classesString = 'switch-on';
            
            $GLOBALS['MenuSound'] = true;
            Media::play($this->form('maingame')->MainMenu->content->MenuSound);
            
            $this->form('maingame')->ltx['mm_sound'] = 'on';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
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
            
            $this->form('maingame')->ltx['fight_sound'] = 'off';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);             
            
            return;
        }
        else 
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = true;
            $this->FightSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->FightSound_Switcher_Btn->classesString = 'switch-on';            
            
            $GLOBALS['FightSound'] = true;
            
            $this->form('maingame')->ltx['fight_sound'] = 'on';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
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
            
            //maingame
            $this->form('maingame')->item_vodka_0000->dropShadowEffect->disable();   
            $this->form('maingame')->actor->dropShadowEffect->disable();  
            $this->form('maingame')->enemy->dropShadowEffect->disable();   
            $this->form('maingame')->Talk_Label->dropShadowEffect->disable();   
            $this->form('maingame')->SavedGame_Toast->dropShadowEffect->disable();
            $this->form('maingame')->leave_btn->dropShadowEffect->disable();  
            $this->form('maingame')->health_static_enemy->dropShadowEffect->disable();     
            $this->form('maingame')->health_static_gg->dropShadowEffect->disable();     
            $this->form('maingame')->health_bar_enemy->dropShadowEffect->disable();     
            $this->form('maingame')->health_bar_gg->dropShadowEffect->disable();          
            $this->form('maingame')->fight_image->dropShadowEffect->disable();       
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
            $this->form('maingame')->Inventory->content->InventoryGrid->content->main->dropShadowEffect->disable();   
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
            $this->form('maingame')->MainMenu->content->Options->content->Return_Btn->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->AllSound_Label->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->AllSound_Switcher_Btn->dropShadowEffect->disable();              
            $this->form('maingame')->MainMenu->content->Options->content->MenuSound_Label->dropShadowEffect->disable(); 
            $this->form('maingame')->MainMenu->content->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->disable();            
            $this->form('maingame')->MainMenu->content->Options->content->Shadows_Label->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->Shadows_Switcher_Btn->dropShadowEffect->disable();                
            $this->form('maingame')->MainMenu->content->Options->content->Version_Label->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->Version_Switcher_Btn->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->FightSound_Label->dropShadowEffect->disable();    
            $this->form('maingame')->MainMenu->content->Options->content->FightSound_Switcher_Btn->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->disable();
            $this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->disable();
            
            $this->form('maingame')->ltx['r_shadows'] = 'off';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = true;
            
            $this->Shadows_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Shadows_Switcher_Btn->classesString = 'switch-on';
            
            //maingame
            $this->form('maingame')->item_vodka_0000->dropShadowEffect->enable();
            $this->form('maingame')->actor->dropShadowEffect->enable();
            $this->form('maingame')->enemy->dropShadowEffect->enable();
            $this->form('maingame')->Talk_Label->dropShadowEffect->enable();
            $this->form('maingame')->SavedGame_Toast->dropShadowEffect->enable();
            $this->form('maingame')->leave_btn->dropShadowEffect->enable();
            $this->form('maingame')->health_static_enemy->dropShadowEffect->enable();     
            $this->form('maingame')->health_static_gg->dropShadowEffect->enable();
            $this->form('maingame')->health_bar_enemy->dropShadowEffect->enable();
            $this->form('maingame')->health_bar_gg->dropShadowEffect->enable();          
            $this->form('maingame')->fight_image->dropShadowEffect->enable();       
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
            $this->form('maingame')->Inventory->content->InventoryGrid->content->main->dropShadowEffect->enable();   
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
            $this->form('maingame')->MainMenu->content->Options->content->Return_Btn->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->AllSound_Label->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->AllSound_Switcher_Btn->dropShadowEffect->enable();              
            $this->form('maingame')->MainMenu->content->Options->content->MenuSound_Label->dropShadowEffect->enable(); 
            $this->form('maingame')->MainMenu->content->Options->content->MenuSound_Switcher_Btn->dropShadowEffect->enable();            
            $this->form('maingame')->MainMenu->content->Options->content->Shadows_Label->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->Shadows_Switcher_Btn->dropShadowEffect->enable();                
            $this->form('maingame')->MainMenu->content->Options->content->Version_Label->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->Version_Switcher_Btn->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->FightSound_Label->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->FightSound_Switcher_Btn->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->Language_Label->dropShadowEffect->enable();
            $this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->dropShadowEffect->enable();  
            
            $this->form('maingame')->ltx['r_shadows'] = 'on';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);                      
                       
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
                $this->form('maingame')->version->hide();
                $this->form('maingame')->version_detail->hide();
            }
            else 
            {  
                $this->form('maingame')->MainMenu->content->version->hide();
                $this->form('maingame')->MainMenu->content->version_detail->hide();
            }
            
            $this->form('maingame')->ltx['r_version'] = 'off';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
            
            return;
        }
        else 
        {
            $GLOBALS['VersionSwitcher_IsOn'] = true;
                     
            $this->Version_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Version_Switcher_Btn->classesString = 'switch-on';         
            
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
            
            $this->form('maingame')->ltx['r_version'] = 'on';
            $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);            
           
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
        
        $this->form('maingame')->ltx['language'] = $this->localization->getCurrentLanguage();
        $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);
        
        $this->form('maingame')->ShowLoadScreen(function()
        {
            $this->UpdateLocalization();
        });
    }

    function UpdateLocalization()
    {
        $this->Return_Btn->text = $this->localization->get('Return_Btn');
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->Return_Btn->text = $this->localization->get('Return_Btn');
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->Return_Btn->text = $this->localization->get('Return_Btn');
      
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

        $this->form('maingame')->MainMenu->content->Btn_Start_Game->text = $this->localization->get(($GLOBALS['NewGameState'] ? 'NewGame_Label' : 'ContinueGame_Label'));
        $this->form('maingame')->MainMenu->content->Btn_Save_Game->text = $this->localization->get('SaveGame_Label');
        $this->form('maingame')->MainMenu->content->Btn_Load_Game->text = $this->localization->get('LoadGame_Label');
        $this->form('maingame')->MainMenu->content->Btn_End_Game->text = $this->localization->get('EndGame_Label');
        $this->form('maingame')->MainMenu->content->Btn_Opt->text = $this->localization->get('Options_Label');
        $this->form('maingame')->MainMenu->content->Btn_Exit_Windows->text = $this->localization->get('ExitToWindows_Label');
        
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->Wnd_Label->text = $this->localization->get('SaveGame_Label');
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->Wnd_Label->text = $this->localization->get('LoadGame_Label');
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->Save_Btn->text = $this->localization->get('Save_Btn');
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->Load_Btn->text = $this->localization->get('Load_Btn');
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->Remove_Save_Btn->text = $this->localization->get('Remove_Btn');
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->Remove_Save_Btn->text = $this->localization->get('Remove_Btn');

        $this->form('maingame')->Talk_Label->text = $this->localization->get('Talk_Label');
        $this->form('maingame')->leave_btn->text = $this->localization->get('Leave_Label');

        $this->form('maingame')->Inventory->content->time_label->text = $this->localization->get('Time_Label');
        $this->form('maingame')->Inventory->content->button5->text = $this->localization->get('Inventory_Label');
        $this->form('maingame')->Inventory->content->button6->text = $this->localization->get('Item_Label');
        $this->form('maingame')->Inventory->content->button7->text = $this->localization->get('Equipment_Label');
        $this->form('maingame')->Inventory->content->InventoryGrid->content->Combobox_Drop->text = $this->localization->get('Drop_Label');
        $this->form('maingame')->Inventory->content->InventoryGrid->content->Combobox_Use->text = $this->localization->get('Use_Label');
        $this->form('maingame')->Inventory->content->maket_cond_label->text = $this->localization->get('Condition_Label');

        //$this->form('maingame')->Pda->content->Pda_Background->text = $this->localization->get('ChooseOption_Label');
        $this->form('maingame')->Pda->content->tasks_label->text = $this->localization->get('Tasks_Label');
        $this->form('maingame')->Pda->content->contacts_label->text = $this->localization->get('Contacts_Label');
        $this->form('maingame')->Pda->content->ranks_label->text = $this->localization->get('Ranks_Label');
        $this->form('maingame')->Pda->content->stat_label->text = $this->localization->get('Data_Label');
        
        $this->form('maingame')->Pda->content->Pda_Tasks->content->task_label->text = $this->localization->get('DefeatEnemy_Task');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->step1->text = $this->localization->get('TalkToGoblin_Task');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->step2->text = $this->localization->get('DefeatGoblin_Task');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->task_detail_text->text = $this->localization->get('TaskDetails');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->active_task->text = $this->localization->get('ActiveTasks_Label');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->passive_task->text = $this->localization->get('CompletedTasks_Label');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->failed_task->text = $this->localization->get('FailedTasks_Label');
        $this->form('maingame')->Pda->content->Pda_Tasks->content->tab_button->text = $this->localization->get('Tasks_Label');

        $this->form('maingame')->Pda->content->Pda_Contacts->content->name->text = $this->localization->get('Contact_Goblin');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->community->text = $this->localization->get('Community_Pido');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->reputation_desc->text = $this->localization->get('Reputation_Desc');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->reputation->text = $this->localization->get('Reputation_Bad');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->relationship_desc->text = $this->localization->get('Attitude_Label');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->relationship->text = $this->localization->get('Relationship_Enemy');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->rank->text = $this->localization->get('Rank_Veterinarian');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->bio->text = $this->localization->get('GoblindaV_Bio');
        $this->form('maingame')->Pda->content->Pda_Contacts->content->tab_button->text = $this->localization->get('Contacts_Label');
        
        $this->form('maingame')->Pda->content->Pda_Ranking->content->name_label->text = $this->localization->get('Name_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->rank_label->text = $this->localization->get('Rank_Bio_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->attitude->text = $this->localization->get('Attitude_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->actor_in_raiting_name->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->valerok_in_raiting_name->text = $this->localization->get('Ranking_Valerok');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_name->text = $this->localization->get('Enemy_Name');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->tab_button->text = $this->localization->get('Ranks_Label');
    
        $this->form('maingame')->Pda->content->Pda_Statistic->content->tab_button->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->Statistic_Label->text = $this->localization->get('Statistic_Label');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->statistic->text = $this->localization->get('Statistic_Details');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->rank->text = $this->localization->get('Rank_Master');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->reputation_desc->text = $this->localization->get('Reputation_Desc');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->reputation->text = $this->localization->get('Statistic_Reputation_Excellent');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->buttonAlt->text = $this->localization->get('Info_Button');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->tab_final->text = $this->localization->get('Timeline_Tab');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->target_label->text = $this->localization->get('Target_Label');

        $this->form('maingame')->Dialog->content->alex_label_1->text = $this->localization->get('Enemy_Name');
        $this->form('maingame')->Dialog->content->alex_desc_1->text = $this->localization->get('Dialog_Goblin_Desc1');
        $this->form('maingame')->Dialog->content->alex_label_2->text = $this->localization->get('Enemy_Name');
        $this->form('maingame')->Dialog->content->alex_desc_2->text = $this->localization->get('Dialog_Goblin_Desc2');
        $this->form('maingame')->Dialog->content->alex_label_3->text = $this->localization->get('Enemy_Name');
        $this->form('maingame')->Dialog->content->alex_desc_3->text = $this->localization->get('Dialog_Goblin_Desc3');
        $this->form('maingame')->Dialog->content->actor_label_1->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Dialog->content->actor_desc_1->text = $this->localization->get('Dialog_Actor_Desc1');
        $this->form('maingame')->Dialog->content->actor_label_3->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Dialog->content->actor_desc_3->text = $this->localization->get('Dialog_Actor_Desc3');
        $this->form('maingame')->Dialog->content->answer_name->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Dialog->content->answer_desc->text = $this->localization->get('Dialog_Actor_Desc1');
        $this->form('maingame')->Dialog->content->gg_name->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Dialog->content->enemy_name->text = $this->localization->get('Enemy_Name');
        $this->form('maingame')->Dialog->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Dialog->content->rank->text = $this->localization->get('Rank_Master');
        $this->form('maingame')->Dialog->content->labelAlt->text = $this->localization->get('Rank_Veterinarian');
        $this->form('maingame')->Dialog->content->label->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Dialog->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Dialog->content->label3->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Dialog->content->community_enemy->text = $this->localization->get('Community_Pido');
    
        $this->form('maingame')->Fail->content->returnbtn->text = $this->localization->get('Return_Button');
        $this->form('maingame')->Fail->content->exitbtn->text = $this->localization->get('Exit_Button');
            
        if ($GLOBALS['QuestCompleted']) 
        {
            $this->form('maingame')->Fail->content->UpdateFailState();
            $this->form('maingame')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
            
            $this->form('maingame')->Task_Step_Label->text = $this->localization->get('No_Active_Task');
        }
        
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->ShowSavePreview();
        
        $this->form('maingame')->Pda->content->Pda_Tasks->content->UpdateData();
        
        if ($this->form('maingame')->MainMenu->visible)
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
