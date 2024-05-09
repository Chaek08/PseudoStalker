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
        Media::open('res://.data/audio/menu/menu_sound.mp3', false, "menu_sound");
        Media::play("menu_sound");     
    }
    function ContinueGameMenu() //Continue Game
    {             
        $this->btn_start_game->image = new UXImage('res://.data/ui/mainmenu/btn_default/btn4.png');   
        $this->btn_start_game->hoverImage = new UXImage('res://.data/ui/mainmenu/btn_cursor/btn4.png'); 
        $this->btn_start_game->clickImage = new UXImage('res://.data/ui/mainmenu/btn_clicked/btn4.png');
        
        $this->btn_opt->y = 496;
        $this->btn_exit_windows->y = 568;
        $this->btn_end_game->show();
    }
    function NewGameMenu() //New Game
    {
        $this->btn_start_game->image = new UXImage('res://.data/ui/mainmenu/btn_default/btn1.png');   
        $this->btn_start_game->hoverImage = new UXImage('res://.data/ui/mainmenu/btn_cursor/btn1.png'); 
        $this->btn_start_game->clickImage = new UXImage('res://.data/ui/mainmenu/btn_clicked/btn1.png');
        
        $this->btn_opt->y = 424;
        $this->btn_exit_windows->y = 496;
        $this->btn_end_game->hide();
    }
    /**
     * @event btn_start_game.click-Left 
     */
    function NewGameBtn(UXMouseEvent $e = null)
    {      
        $this->form('maingame')->fragment_menu->hide();
        $this->ContinueGameMenu(); //Continue Game
        Media::pause("menu_sound");
        $this->form('maingame')->PlayMainAmbient();
        if ($this->form('maingame')->fight_image->visible)
        {
            if ($this->form('maingame')->fragment_opt->content->all_sounds->visible)
            {
                 if($this->form('maingame')->fragment_opt->content->mute_fight_sound->visible) {} else 
                 {
                     Media::play("fight_sound");
                 }     
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
    /**
     * @event btn_end_game.click-Left 
     */
    function EndGameBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ResetGameClient();
    }
}
