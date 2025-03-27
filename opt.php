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
        $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
        $GLOBALS['FightSoundSwitcher_IsOn'] = true;
        $GLOBALS['ShadowsSwitcher_IsOn'] = true;
        $GLOBALS['VersionSwitcher_IsOn'] = true;
                                            
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
        
        $this->Language_Switcher_Combobobx->value = ($this->localization->getCurrentLanguage() == 'rus') ? 'Русский' : 'English';
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
        
        $this->form('maingame')->MainMenu->content->dynamic_background->toBack();
        $this->form('maingame')->MainMenu->content->Options->hide();
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
        $this->AllSound_Switcher_Btn->textColor = ($GLOBALS['AllSoundSwitcher_IsOn'] == false) ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event AllSound_Switcher_Btn.mouseEnter 
     */
    function AllSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->AllSound_Switcher_Btn->textColor = ($GLOBALS['AllSoundSwitcher_IsOn'] == false) ? '#b50b17' : '#0bb30b';
        
    }
    /**
     * @event AllSound_Switcher_Btn.mouseDown-Left 
     */
    function AllSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($GLOBALS['AllSoundSwitcher_IsOn'])
        {
            $GLOBALS['AllSoundSwitcher_IsOn'] = false;
            $this->AllSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->AllSound_Switcher_Btn->textColor = '#880911';
            
            $GLOBALS['AllSounds'] = false;
        
            if ($this->MenuSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
            {
                $this->MenuSoundSwitcher_MouseDownLeft();
                $this->MenuSoundSwitcher_MouseExit();
            }
            if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOn_Label'))
            {
                $this->FightSoundSwitcher_MouseDownLeft();
                $this->FightSoundSwitcher_MouseExit();
            }
            
            $this->form('maingame')->StopAllSounds();
            
            return;
        }
        else 
        {
            $this->AllSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->AllSound_Switcher_Btn->textColor = '#099209';
            $GLOBALS['AllSoundSwitcher_IsOn'] = true;
        
            $GLOBALS['AllSounds'] = true;
        
            if ($this->MenuSound_Switcher_Btn->text == $this->localization->get('TurnOff_Label')) 
            {
                $this->MenuSoundSwitcher_MouseDownLeft();
                $this->MenuSoundSwitcher_MouseExit();
            }
            if ($this->FightSound_Switcher_Btn->text == $this->localization->get('TurnOff_Label'))
            {
                $this->FightSoundSwitcher_MouseDownLeft();
                $this->FightSoundSwitcher_MouseExit();
            }               
            if (SDK_Mode)
            {
                Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, false, $this->form('maingame')->FightSound);
            }
            else 
            {  
                Media::open($fightsoundPath, $this->form('maingame')->FightSound);
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
        $this->MenuSound_Switcher_Btn->textColor = ($GLOBALS['MenuSoundSwitcher_IsOn'] == false) ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseEnter 
     */
    function MenuSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->MenuSound_Switcher_Btn->textColor = ($GLOBALS['MenuSoundSwitcher_IsOn'] == false) ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event MenuSound_Switcher_Btn.mouseDown-Left 
     */
    function MenuSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($GLOBALS['MenuSoundSwitcher_IsOn'])
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = false;
            $this->MenuSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->MenuSound_Switcher_Btn->textColor = '#880911';
            
            Media::stop($this->form('maingame')->MainMenu->content->MenuSound);
            $GLOBALS['MenuSound'] = false;
            
            return;
        }
        else 
        {
            $GLOBALS['MenuSoundSwitcher_IsOn'] = true;
            $this->MenuSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->MenuSound_Switcher_Btn->textColor = '#099209';
            
            Media::play($this->form('maingame')->MainMenu->content->MenuSound);
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
        $this->FightSound_Switcher_Btn->textColor = ($GLOBALS['FightSoundSwitcher_IsOn'] == false) ? '#d60d1b' : '#0dd60d';
    }

    /**
     * @event FightSound_Switcher_Btn.mouseEnter 
     */
    function FightSoundSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->FightSound_Switcher_Btn->textColor = ($GLOBALS['FightSoundSwitcher_IsOn'] == false) ? '#b50b17' : '#0bb30b';
    }

    /**
     * @event FightSound_Switcher_Btn.mouseDown-Left 
     */
    function FightSoundSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($GLOBALS['FightSoundSwitcher_IsOn'])
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = false;
            $this->FightSound_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->FightSound_Switcher_Btn->textColor = '#880911';
            
            $GLOBALS['FightSound'] = false;
            
            return;
        }
        else 
        {
            $GLOBALS['FightSoundSwitcher_IsOn'] = true;
            $this->FightSound_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->FightSound_Switcher_Btn->textColor = '#099209';
            
            $GLOBALS['FightSound'] = true;
            
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
        $this->Shadows_Switcher_Btn->textColor = ($GLOBALS['ShadowsSwitcher_IsOn'] == false) ? '#d60d1b' : '#0dd60d';
    }
    /**
     * @event Shadows_Switcher_Btn.mouseEnter 
     */
    function ShadowsSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Shadows_Switcher_Btn->textColor = ($GLOBALS['ShadowsSwitcher_IsOn'] == false) ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event Shadows_Switcher_Btn.mouseDown-Left 
     */
    function ShadowsSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($GLOBALS['ShadowsSwitcher_IsOn'])
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = false;
            $this->Shadows_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->Shadows_Switcher_Btn->textColor = '#880911';
            
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
            //exit_dlg
            $this->form('maingame')->ExitDialog->content->main_frame->dropShadowEffect->disable();
            
            return;
        }
        else 
        {
            $GLOBALS['ShadowsSwitcher_IsOn'] = true;
            $this->Shadows_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Shadows_Switcher_Btn->textColor = '#099209';
            
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
        $this->Version_Switcher_Btn->textColor = ($GLOBALS['VersionSwitcher_IsOn'] == false) ? 'd60d1b' : '#0dd60d';
    }
    /**
     * @event Version_Switcher_Btn.mouseEnter 
     */
    function VersionSwitcher_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Version_Switcher_Btn->textColor = ($GLOBALS['VersionSwitcher_IsOn'] == false) ? '#b50b17' : '#0bb30b';
    }
    /**
     * @event Version_Switcher_Btn.mouseDown-Left 
     */
    function VersionSwitcher_MouseDownLeft(UXMouseEvent $e = null)
    {
        if ($GLOBALS['VersionSwitcher_IsOn'])
        {
            $GLOBALS['VersionSwitcher_IsOn'] = false;
            $this->Version_Switcher_Btn->text = $this->localization->get('TurnOff_Label');
            $this->Version_Switcher_Btn->textColor = '#880911';
            
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
            $GLOBALS['VersionSwitcher_IsOn'] = true;
            $this->Version_Switcher_Btn->text = $this->localization->get('TurnOn_Label');
            $this->Version_Switcher_Btn->textColor = '#099209';         
            
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
    /**
     * @event Language_Switcher_Combobobx.action 
     */
    function LanguageSwitcherCombobobx(UXEvent $e = null)
    {
        if ($this->Language_Switcher_Combobobx->value == 'Русский')
        {
            $this->localization->setLanguage($this->Language_Switcher_Combobobx->value);
            if (Debug_Build && $this->form('maingame')->MainMenu->content->Options->visible) $this->form('maingame')->toast('Current language: ' . $this->localization->getCurrentLanguage() . ' (' . $this->Language_Switcher_Combobobx->value . ')');
        }
        if ($this->Language_Switcher_Combobobx->value == 'English')
        {
            $this->localization->setLanguage($this->Language_Switcher_Combobobx->value);
            if (Debug_Build && $this->form('maingame')->MainMenu->content->Options->visible) $this->form('maingame')->toast('Current language: ' . $this->localization->getCurrentLanguage() . ' (' . $this->Language_Switcher_Combobobx->value . ')');
        } 
        $this->UpdateLocalization();
    }
    function UpdateLocalization()
    {
        $this->form('maingame')->LoadScreen();
        
        $this->Return_Btn->text = $this->localization->get('Return_Btn');
      
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
        $this->form('maingame')->MainMenu->content->Btn_End_Game->text = $this->localization->get('EndGame_Label');
        $this->form('maingame')->MainMenu->content->Btn_Opt->text = $this->localization->get('Options_Label');
        $this->form('maingame')->MainMenu->content->Btn_Exit_Windows->text = $this->localization->get('ExitToWindows_Label');

        $this->form('maingame')->dlg_btn->text = $this->localization->get('StartDialog_Label');
        $this->form('maingame')->leave_btn->text = $this->localization->get('Leave_Label');

        $this->form('maingame')->ExitDialog->content->dialog_text->text = $this->localization->get('ExitDialog_Text');
        $this->form('maingame')->ExitDialog->content->btn_yes->text = $this->localization->get('Yes_Label');
        $this->form('maingame')->ExitDialog->content->btn_no->text = $this->localization->get('No_Label');

        $this->form('maingame')->Inventory->content->button5->text = $this->localization->get('Inventory_Label');
        $this->form('maingame')->Inventory->content->button6->text = $this->localization->get('Item_Label');
        $this->form('maingame')->Inventory->content->button7->text = $this->localization->get('Equipment_Label');
        $this->form('maingame')->Inventory->content->button_drop->text = $this->localization->get('Drop_Label');
        $this->form('maingame')->Inventory->content->maket_cond_label->text = $this->localization->get('Condition_Label');

        $this->form('maingame')->Pda->content->Pda_Background->text = $this->localization->get('ChooseOption_Label');
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
        
        $this->form('maingame')->Pda->content->Pda_Ranking->content->name_label->text = $this->localization->get('Name_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->rank_label->text = $this->localization->get('Rank_Bio_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->bio_new->text = $this->localization->get('Bio_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->rank_desc->text = $this->localization->get('Rank_Desc');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->community_desc->text = $this->localization->get('Group_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->attitude->text = $this->localization->get('Attitude_Label');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->actor_in_raiting_name->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->valerok_in_raiting_name->text = $this->localization->get('Ranking_Valerok');
        $this->form('maingame')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_name->text = $this->localization->get('Enemy_Name');
    
        $this->form('maingame')->Pda->content->Pda_Statistic->content->tab_button->text = $this->localization->get('GG_Name');
        $this->form('maingame')->Pda->content->Pda_Statistic->content->statistic_norm->text = $this->localization->get('Statistic_Label');
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
        
        $this->form('maingame')->Inventory->content->InitInventoryWeight();
        
        if ($GLOBALS['QuestCompleted']) 
        {
            $this->form('maingame')->Fail->content->UpdateFailState();
            $this->form('maingame')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
        }
        
        if (SDK_Mode)
        {
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->edit_actorname->promptText = $this->localization->get('GG_Name');
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->edit_enemyname->promptText = $this->localization->get('Enemy_Name');
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->Edit_ValerokName->promptText = $this->localization->get('Ranking_Valerok');
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->textArea_ActorBio->promptText = $this->localization->get('Actor_Bio');
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->textArea_EnemyBio->promptText = $this->localization->get('GoblindaV_Bio');
            $this->form('maingame')->Editor->content->f_UserDataEditor->content->textArea_ValerokBio->promptText = $this->localization->get('Valerok_Bio');
            
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Alex_Desc_1->promptText = $this->localization->get('Dialog_Goblin_Desc1');
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Alex_Desc_2->promptText = $this->localization->get('Dialog_Goblin_Desc2');
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Alex_Desc_3->promptText = $this->localization->get('Dialog_Goblin_Desc3');
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_1->promptText = $this->localization->get('Dialog_Actor_Desc1');
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_3->promptText = $this->localization->get('Dialog_Actor_Desc3');
            $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Final_Phase->promptText = $this->localization->get('Dialog_Final_Phase');
            
            $this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Text_Actor_Edit->promptText = $this->localization->get('ActorFail_Label');
            $this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Text_Enemy_Edit->promptText = $this->localization->get('EnemyFail_Label');
            $this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Desc_Actor_Edit->promptText = $this->localization->get('ActorFail_Desc');
            $this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Desc_Enemy_Edit->promptText = $this->localization->get('EnemyFail_Desc');
            
            $this->form('maingame')->Editor->content->f_RoleEditor->content->Edit_RoleName_Danila->promptText = $this->localization->get('DE_Community');
            $this->form('maingame')->Editor->content->f_RoleEditor->content->Edit_RoleName_Ladcega->promptText = $this->localization->get('LA_Community');
            $this->form('maingame')->Editor->content->f_RoleEditor->content->Edit_RoleName_Pido->promptText = $this->localization->get('Community_Pido');
            $this->form('maingame')->Editor->content->f_RoleEditor->content->labelAlt->text = $this->localization->get('SDK_RoleColor_Faggots');
            $this->form('maingame')->Editor->content->f_RoleEditor->content->label5->text = $this->localization->get('SDK_RoleName_Faggots');
            $this->form('maingame')->Editor->content->f_RoleEditor->content->label8->text = $this->localization->get('SDK_RoleIcon_Faggots');
            
            $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Outfit->promptText = $this->localization->get('Outfit_Inv_Name');
            $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Outfit->promptText = $this->localization->get('Outfit_Inv_Desc');
            $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemName_Vodka->promptText = $this->localization->get('Vodka_Inv_Name');
            $this->form('maingame')->Editor->content->f_InvEditor->content->Edit_ItemDesc_Vodka->promptText = $this->localization->get('Vodka_Inv_Desc');
            
            $this->form('maingame')->Editor->content->f_QuestEditor->content->Edit_QuestName->promptText = $this->localization->get('DefeatEnemy_Task');
            $this->form('maingame')->Editor->content->f_QuestEditor->content->Edit_QuestDesc->promptText = $this->localization->get('TaskDetails');
            $this->form('maingame')->Editor->content->f_QuestEditor->content->Edit_QuestStep1->promptText = $this->localization->get('TalkToGoblin_Task');
            $this->form('maingame')->Editor->content->f_QuestEditor->content->Edit_QuestStep2->promptText = $this->localization->get('DefeatGoblin_Task');
            $this->form('maingame')->Editor->content->f_QuestEditor->content->Edit_QuestTarget->promptText = $this->localization->get('Target_Label');
        }
    }
}
