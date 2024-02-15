<?php
namespace app\forms;

use std, gui, framework, app;


class enemy_fail extends AbstractForm
{
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_enm_fail->hide(); 
        //if ($this->form('maingame')->fragment_opt->content->sound->visible)
        //{
        //    Media::stop("fight_sound");   
        //}              
    }
    /**
     * @event exitbtn.click-Left 
     */
    function ExitBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_menu->show();
        $this->form('maingame')->ResetGameClient();
        //if ($this->form('maingame')->fragment_opt->content->sound->visible)
        //{
        //    Media::stop("fight_sound");   
        //}
        $this->form('mainmenu')->StartMenuSound();     
    }

}
