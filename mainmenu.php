<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\text\UXFont;
use php\gui\event\UXMouseEvent; 
use app\forms\classes\Localization;

class mainmenu extends AbstractForm
{
    private $localization;

    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function InitMainMenu()
    {
        $GLOBALS['NewGameState'] = true;
        if ($GLOBALS['AllSounds'] && $GLOBALS['MenuSound'])
        {
            Media::open('res://.data/audio/menu/menu_sound.mp3', false, $this->MenuSound);
            Media::play($this->MenuSound);
        }
        
        //отрендерим задник меню
        $this->MainMenuBackground->view = $this->dynamic_background;
        $backgroundPath = SDK_Mode
            ? $this->form('maingame')->Editor->content->f_MgEditor->content->Edit_MenuBackground->text 
            : 'res://.data/video/menu_background.mp4';
        Media::open($backgroundPath, true, $this->MainMenuBackground);
    }
    /**
     * @event opensdk_btn.click-Left 
     */
    function OpenSdkBtn(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->MainMenu->visible) 
        {
            Media::pause($this->MenuSound);
            Media::pause($this->MainMenuBackground);
        }
        $this->form('maingame')->Editor->show();
    }
    /**
     * @event Btn_Start_Game.mouseExit 
     */
    function BtnStartGame_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_Start_Game->textColor = '#ffffff';
    }
    /**
     * @event Btn_Start_Game.mouseEnter 
     */
    function BtnStartGame_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_Start_Game->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_Start_Game.mouseDown-Left 
     */
    function BtnStartGame_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_Start_Game->textColor = '#808080';
    
        $this->form('maingame')->MainMenu->hide();
        
        if (ToggleHudFeature && !$GLOBALS['HudVisible']) $this->form('maingame')->ToggleHud(); //вот так нахуй надо
        
        if ($GLOBALS['NewGameState']) $this->SwitchGameState();
        
        $this->form('maingame')->OpenMainAmbient();
        $this->form('maingame')->PlayMainAmbient();
                
        Media::pause($this->MenuSound);
        Media::pause($this->MainMenuBackground);
        
        if ($this->form('maingame')->fight_image->visible)
        {
            if ($GLOBALS['AllSounds'] && $GLOBALS['FightSound'])
            {
                 Media::play($this->form('maingame')->FightSound);
            }
        }            
    }
    function SwitchGameState()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if ($GLOBALS['NewGameState'])
        {
            $GLOBALS['NewGameState'] = false;
            
            $this->Btn_Start_Game->text = $this->localization->get('ContinueGame_Label');
        
            $this->Btn_Opt->y = 496;
            $this->Btn_Exit_Windows->y = 568;
            $this->Btn_End_Game->show();
            
            $GLOBALS['ContinueGameState'] = true;
            return;
        }
        if ($GLOBALS['ContinueGameState'])
        {
            $GLOBALS['ContinueGameState'] = false;
            
            $this->Btn_Start_Game->text = $this->localization->get('NewGame_Label');
        
            $this->Btn_Opt->y = 424;
            $this->Btn_Exit_Windows->y = 496;
            $this->Btn_End_Game->hide();
            
            $GLOBALS['NewGameState'] = true;
            return;
        }              
    }
    /**
     * @event Btn_Start_Game.mouseUp-Left 
     */
    function BtnStartGame_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnStartGame_MouseExit();
    }
    /**
     * @event Btn_End_Game.mouseExit 
     */
    function BtnEndGame_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_End_Game->textColor = '#ffffff';
    }
    /**
     * @event Btn_End_Game.mouseEnter 
     */
    function BtnEndGame_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_End_Game->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_End_Game.mouseDown-Left 
     */
    function BtnEndGame_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_End_Game->textColor = '#808080';
        
        $this->form('maingame')->ResetGameClient();
    }
    /**
     * @event Btn_End_Game.mouseUp-Left 
     */
    function BtnEndGame_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnEndGame_MouseExit();
    }
    /**
     * @event Btn_Exit_Windows.mouseExit 
     */
    function BtnExitWindows_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_Exit_Windows->textColor = '#ffffff';
    }
    /**
     * @event Btn_Exit_Windows.mouseEnter 
     */
    function BtnExitWindows_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_Exit_Windows->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_Exit_Windows.mouseDown-Left 
     */
    function BtnExitWindows_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_Exit_Windows->textColor = '#808080';
        
        $this->form('maingame')->ExitDialog->show();
    }
    /**
     * @event Btn_Exit_Windows.mouseUp-Left 
     */
    function BtnExitWindowsMouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnExitWindows_MouseExit();
    }
    /**
     * @event Btn_Opt.mouseExit 
     */
    function BtnOpt_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_Opt->textColor = '#ffffff';
    }
    /**
     * @event Btn_Opt.mouseEnter 
     */
    function BtnOpt_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_Opt->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_Opt.mouseDown-Left 
     */
    function BtnOpt_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_Opt->textColor = '#808080';
        
        //$this->form('maingame')->MainMenu->hide();
        //$this->form('maingame')->Options->show();
        //рендерить два задника это дорого
        $this->Options->show();
        $this->dynamic_background->toFront();
        $this->Options->toFront();
    }
    /**
     * @event Btn_Opt.mouseUp-Left 
     */
    function BtnOpt_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnOpt_MouseExit();
    }
}
