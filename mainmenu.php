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

    public $SDK_MMBackground = '';

    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function InitMainMenu()
    {
        $GLOBALS['NewGameState'] = true;
        
        Media::open('res://.data/audio/menu/menu_sound.mp3', false, $this->MenuSound);
        if ($GLOBALS['AllSounds'] && $GLOBALS['MenuSound'])
        {
            Media::play($this->MenuSound);
        }
        
        //отрендерим задник меню
        $this->MainMenuBackground->view = $this->dynamic_background;
        $sdk_background = trim($this->SDK_MMBackground);
        $backgroundPath = ($sdk_background != '') ? $sdk_background : '.\gamedata\textures\menu\background.mp4';
        Media::open($backgroundPath, true, $this->MainMenuBackground);
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
        
        if (!$GLOBALS['HudVisible']) $this->form('maingame')->ToggleHud();
        
        if ($GLOBALS['NewGameState']) $this->SwitchGameState();
        
        Media::pause($this->MenuSound);
        Media::pause($this->MainMenuBackground);
        
        $this->form('maingame')->PlayEnvironment();
        
        if ($this->form('maingame')->fight_image->visible)
        {
            if ($GLOBALS['AllSounds'] && $GLOBALS['FightSound'])
            {
                 Media::play($this->form('maingame')->FightSound);
            }
        }   
        
        $this->form('maingame')->Dialog->content->UpdateData();         
    }
    function SwitchGameState()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if ($GLOBALS['NewGameState'])
        {
            $GLOBALS['NewGameState'] = false;
            
            $this->Btn_Start_Game->text = $this->localization->get('ContinueGame_Label');
            
            $this->Btn_Save_Game->show();
            $this->Btn_End_Game->show();
            
            $this->Btn_Load_Game->y = 424;
            $this->Btn_Save_Game->y = 496;
            $this->Btn_Opt->y = 568;
            $this->Btn_End_Game->y = 640;
            $this->Btn_Exit_Windows->y = 712;
            
            $GLOBALS['ContinueGameState'] = true;
            return;
        }
        if ($GLOBALS['ContinueGameState'])
        {
            $GLOBALS['ContinueGameState'] = false;
            
            $this->Btn_Start_Game->text = $this->localization->get('NewGame_Label');
        
            $this->Btn_Save_Game->hide();
            $this->Btn_End_Game->hide();
            
            $this->Btn_Load_Game->y = 424;
            $this->Btn_Opt->y = 496;
            $this->Btn_Exit_Windows->y = 568;
            
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
        
        $this->form('maingame')->ExitDialog->content->UpdateDialogWnd();
        $GLOBALS['EndGameWndType'] = true;
        $this->form('maingame')->ExitDialog->content->SetDialogWndType();
        $this->form('maingame')->ExitDialog->show();
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
        
        $this->form('maingame')->ExitDialog->content->UpdateDialogWnd();
        $GLOBALS['ExitWndType'] = true;
        $this->form('maingame')->ExitDialog->content->SetDialogWndType();
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
        $this->dynamic_background->toFront();
        $this->Options->show();
        $this->Options->toFront();
    }
    /**
     * @event Btn_Opt.mouseUp-Left 
     */
    function BtnOpt_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnOpt_MouseExit();
    }
    /**
     * @event Btn_Save_Game.mouseExit 
     */
    function BtnSaveGame_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_Save_Game->textColor = '#ffffff';
    }
    /**
     * @event Btn_Save_Game.mouseEnter 
     */
    function BtnSaveGame_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_Save_Game->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_Save_Game.mouseDown-Left 
     */
    function BtnSaveGame_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_Save_Game->textColor = '#808080';
        
        $this->dynamic_background->toFront();
        $this->UISaveWnd->show();
        $this->UISaveWnd->toFront();
    }
    /**
     * @event Btn_Save_Game.mouseUp-Left 
     */
    function BtnSaveGame_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnSaveGame_MouseExit();
    }
    /**
     * @event Btn_Load_Game.mouseExit 
     */
    function BtnLoadGame_MouseExit(UXMouseEvent $e = null)
    {
        $this->Btn_Load_Game->textColor = '#ffffff';
    }
    /**
     * @event Btn_Load_Game.mouseEnter 
     */
    function BtnLoadGame_MouseEnter(UXMouseEvent $e = null)
    {
        $this->Btn_Load_Game->textColor = '#b3b3b3';
    }
    /**
     * @event Btn_Load_Game.mouseDown-Left 
     */
    function BtnLoadGame_MouseDownLeft(UXMouseEvent $e = null)
    {
        $this->Btn_Load_Game->textColor = '#808080';
        
        $this->dynamic_background->toFront();
        $this->UILoadWnd->show();
        $this->UILoadWnd->toFront();
    }
    /**
     * @event Btn_Load_Game.mouseUp-Left 
     */
    function BtnLoadGame_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnLoadGame_MouseExit();
    }
}
