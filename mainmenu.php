<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\text\UXFont;
use php\gui\event\UXMouseEvent; 

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
    function ContinueGameMenu()
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
        $this->form('maingame')->MainMenu->hide();
        
        $this->ContinueGameMenu(); //Continue Game Status Activate
        $this->form('maingame')->PlayMainAmbient();
                
        Media::pause("menu_sound");
        
        if ($this->form('maingame')->fight_image->visible)
        {
            if ($this->form('maingame')->Options->content->All_Sounds->visible)
            {
                 if (!$this->form('maingame')->Options->content->mute_fight_sound->visible)
                 {
                     Media::play('fight_sound');
                 }
            }
        }
    }
    /**
     * @event btn_exit_windows.click-Left 
     */
    function ExitWindowsBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->ExitDialog->show();
    }
    /**
     * @event btn_opt.click-Left 
     */
    function OptBtn(UXMouseEvent $e = null)
    {        
        $this->form('maingame')->MainMenu->hide();
        $this->form('maingame')->Options->show();    
    }
    /**
     * @event btn_end_game.click-Left 
     */
    function EndGameBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ResetGameClient();
    }
    /**
     * @event opensdk_btn.click-Left 
     */
    function OpenSdkBtn(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->MainMenu->visible) Media::pause("menu_sound");
        
        $this->form('maingame')->Editor->show();
    }
}
