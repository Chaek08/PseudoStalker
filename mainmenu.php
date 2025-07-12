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
     * @event Btn_Start_Game.mouseDown-Left 
     */
    function BtnStartGame(UXMouseEvent $e = null)
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);    
    
        $this->form('maingame')->MainMenu->hide();
        
        $this->form('maingame')->RenderHud(true);
        
        if ($GLOBALS['NewGameState']) 
        {
            $this->SwitchGameState();
            
            $this->form('maingame')->Pda->content->Pda_Tasks->content->UpdateData();
            $this->form('maingame')->Dialog->content->UpdateData();
        }
        
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
        
        $GLOBALS['discord']->setDetails($this->localization->get('RPC_Ingame'));
        $GLOBALS['discord']->updateState();
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
     * @event Btn_End_Game.mouseDown-Left 
     */
    function BtnEndGame(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ExitDialog->content->UpdateDialogWnd();
        $GLOBALS['EndGameWndType'] = true;
        $this->form('maingame')->ExitDialog->content->SetDialogWndType();
        $this->form('maingame')->ExitDialog->show();
    }
    /**
     * @event Btn_Exit_Windows.mouseDown-Left 
     */
    function BtnExitWindows(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ExitDialog->content->UpdateDialogWnd();
        $GLOBALS['ExitWndType'] = true;
        $this->form('maingame')->ExitDialog->content->SetDialogWndType();
        $this->form('maingame')->ExitDialog->show();
    }
    /**
     * @event Btn_Opt.mouseDown-Left 
     */
    function BtnOpt(UXMouseEvent $e = null)
    {
        $this->dynamic_background->toFront();
        $this->Options->show();
        $this->Options->toFront();
    }
    /**
     * @event Btn_Save_Game.mouseDown-Left 
     */
    function BtnSaveGame(UXMouseEvent $e = null)
    {
        $this->dynamic_background->toFront();
        $this->UISaveWnd->show();
        $this->UISaveWnd->toFront();
    }
    /**
     * @event Btn_Load_Game.mouseDown-Left 
     */
    function BtnLoadGame(UXMouseEvent $e = null)
    {
        $this->dynamic_background->toFront();
        $this->UILoadWnd->show();
        $this->UILoadWnd->toFront();
    }
}
