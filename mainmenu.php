<?php
namespace app\forms;

use std, gui, framework, app;


class mainmenu extends AbstractForm
{
    /**
     * @event show 
     */
    function InitGameClient(UXWindowEvent $e = null)
    {    
        $this->GetVersion(); 
        Media::open('res://.data/audio/fight/im_alex.mp3', false, "menu_sound");
        Media::play("menu_sound");     
    }
    function StartMenuSound()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::play("menu_sound");
        }            
    }
    /**
     * @event newgamebtn.click-Left 
     */
    function NewGameBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->hide();
        $this->newgamebtn->text = "Вернуться в игру";
        Media::pause("menu_sound");
        if ($this->form('maingame')->fight_label->visible)
        {
            if ($this->form('maingame')->fragment_opt->content->sound->visible)
            {
                 Media::play("fight_sound");
            }
        }
    }
    /**
     * @event exitwindowsbtn.click-Left 
     */
    function ExitWindowsBtn(UXMouseEvent $e = null)
    {    
        $this->fragment_exit->show();
    }
    /**
     * @event opt_btn.click-Left 
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
            $this->label_version->text = "PseudoStalker, Build 420, Feb 17 2024"; //start date 24.12.2022
        }
        else
        {
            $this->label_version->text = "PseudoStalker\nVersion 1.0";
        }
    }
}
