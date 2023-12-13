<?php
namespace app\forms;

use std, gui, framework, app;


class opt extends AbstractForm
{

    /**
     * @event backbtn.click-Left
     */
    function BackBtn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_menu->show();    
        $this->form('maingame')->fragment_opt->hide();      
    }

    /**
     * @event btn_menu_sound.click-Left 
     */
    function OptMuteMenuSound(UXMouseEvent $e = null)
    {    
        Media::stop("menu_sound");
    }

}
