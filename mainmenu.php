<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\text\UXFont;
use php\gui\event\UXMouseEvent; 

class mainmenu extends AbstractForm
{
    function InitMainMenu()
    {
        if ($GLOBALS['AllSounds'] || $GLOBALS['MenuSound'])
        {
            Media::open('res://.data/audio/menu/menu_sound.mp3', false, "menu_sound");
            Media::play("menu_sound");
        }
    }
    /**
     * @event opensdk_btn.click-Left 
     */
    function OpenSdkBtn(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->MainMenu->visible) Media::pause("menu_sound");
        
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
        
        $this->ContinueGameMenu(); //Continue Game Status Activate
        
        $this->form('maingame')->OpenMainAmbient();
        $this->form('maingame')->PlayMainAmbient();
                
        Media::pause("menu_sound");
        
        if ($this->form('maingame')->fight_image->visible)
        {
            if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
            {
                 Media::play('fight_sound');
            }
        }            
    }
    function ContinueGameMenu()
    {
        $this->Btn_Start_Game->text = "Продолжить игру";
        
        $this->Btn_Opt->y = 496;
        $this->Btn_Exit_Windows->y = 568;
        $this->Btn_End_Game->show();
    }
    function NewGameMenu()
    {
        $this->Btn_Start_Game->text = "Новая игра";
        
        $this->Btn_Opt->y = 424;
        $this->Btn_Exit_Windows->y = 496;
        $this->Btn_End_Game->hide();
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
        
        $this->form('maingame')->MainMenu->hide();
        $this->form('maingame')->Options->show();
    }
    /**
     * @event Btn_Opt.mouseUp-Left 
     */
    function BtnOpt_MouseUpLeft(UXMouseEvent $e = null)
    {
        $this->BtnOpt_MouseExit();
    }
}
