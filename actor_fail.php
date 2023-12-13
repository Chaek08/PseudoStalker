<?php
namespace app\forms;

use std, gui, framework, app;


class actor_fail extends AbstractForm
{
    /**
     * @event exitbtn.click-Left 
     */
    function ExitBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->show();
        $this->form('maingame')->ResetGameClient();
        Media::stop("fight_sound");
        $this->form('mainmenu')->InitGameClient(); 
    }
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_act_fail->hide();
    }
}
