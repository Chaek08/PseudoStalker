<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\text\UXFont;

class mainmenu extends AbstractForm
{
    /**
     * @event show 
     */
    function InitGameClient(UXWindowEvent $e = null)
    {    
        $this->GetVersion(); 
        Media::open('res://.data/audio/menu/menu_sound.mp3', false, "menu_sound");
        Media::play("menu_sound");     
    }
    function StartMenuSound()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::play("menu_sound");
           
        }            
    }
    function Btn__ContinueGame()
    {             
        $this->btn_start_game->image = new UXImage('res://.data/ui/mainmenu/btn_default/btn4.png');   
        $this->btn_start_game->hoverImage = new UXImage('res://.data/ui/mainmenu/btn_cursor/btn4.png'); 
        $this->btn_start_game->clickImage = new UXImage('res://.data/ui/mainmenu/btn_clicked/btn4.png');                     
           
    }
    function Btn__NewGame()
    {
        $this->btn_start_game->image = new UXImage('res://.data/ui/mainmenu/btn_default/btn1.png');   
        $this->btn_start_game->hoverImage = new UXImage('res://.data/ui/mainmenu/btn_cursor/btn1.png'); 
        $this->btn_start_game->clickImage = new UXImage('res://.data/ui/mainmenu/btn_clicked/btn1.png');       
    }
    /**
     * @event btn_start_game.click-Left 
     */
    function NewGameBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->hide();
        $this->Btn__ContinueGame();
        Media::pause("menu_sound");
        $this->form('maingame')->PlayMainAmbient();
        if ($this->form('maingame')->fight_label->visible)
        {
            if ($this->form('maingame')->fragment_opt->content->sound->visible)
            {
                 Media::play("fight_sound");
            }
        }
    }
    /**
     * @event btn_exit_windows.click-Left 
     */
    function ExitWindowsBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->ShowExitDialog();
    }
    /**
     * @event btn_opt.click-Left 
     */
    function OptBtn(UXMouseEvent $e = null)
    {        
        $this->form('maingame')->fragment_menu->hide();
        $this->form('maingame')->fragment_opt->show();    
    }
    function GetVersion()
    {
        if ($this->debug_build->visible)
        {
            $this->label_version->text = "PseudoStalker, Build 429, Feb 26 2024"; //start date 24.12.2022
        }
        else
        {
            $this->label_version->text = "PseudoStalker\nVersion 1.0";
        }
    }
}
