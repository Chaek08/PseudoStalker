<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\text\UXFont;
use php\gui\event\UXMouseEvent; 
use app\forms\classes\Localization;
use app\forms\classes\Environment\EnvironmentBase;
use app\forms\classes\Environment\EnvironmentBrightness;

class mainmenu extends AbstractForm
{
    private $localization;

    public $SDK_MMBackground;
    
    public $menuPlayer;

    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }
    
    function InitMainMenu()
    {
        $GLOBALS['NewGameState'] = true;
        
        if (is_object($this->menuPlayer))
        {
            $this->menuPlayer->stop();
        }
        
        $this->menuPlayer = new MediaPlayerScript();
        $this->menuPlayer->open('res://.data/audio/menu/menu_sound.mp3');
        $this->menuPlayer->loop = true;
        
        if ($GLOBALS['AllSounds'] && $GLOBALS['MenuSound'])
        {
            $this->menuPlayer->play();
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
        $this->form('Client')->MainMenu->hide();
        $this->form('Client')->MainGame->show();
        
        $this->form('Client')->MainGame->content->RenderHud(true);
        
        if ($GLOBALS['NewGameState']) 
        {
            $this->SwitchGameState();
            
            $this->form('Client')->MainGame->content->InitMainGame();
        }
        
        $this->menuPlayer->pause();
        
        Media::pause($this->MainMenuBackground);        
        
        $this->form('Client')->MainGame->content->Environment->resume();
        
        if ($GLOBALS['AllSounds'] && $GLOBALS['FightSound'])
        {
            if (!$GLOBALS['QuestCompleted'] && $GLOBALS['QuestStep1'])
            {
                 $this->form('Client')->MainGame->content->fightPlayer->play();
            }
        }
           
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        if ($this->form('Client')->ltx->r_bool('discord_rpc'))
        {
            $GLOBALS['discord']->setDetails($this->localization->get('RPC_Ingame'));
            $GLOBALS['discord']->updateState();
        }
    }
    
    function SwitchGameState()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
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
        $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_END_GAME);
    }
    /**
     * @event Btn_Exit_Windows.mouseDown-Left 
     */
    function BtnExitWindows(UXMouseEvent $e = null)
    {
        $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_EXIT);
    }
    /**
     * @event Btn_Opt.mouseDown-Left 
     */
    function BtnOpt(UXMouseEvent $e = null)
    {
        $this->dynamic_background->toFront();
        $this->Options->content->SyncSwitcherStyles();
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
