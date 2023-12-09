<?php
namespace app\forms;

use std, gui, framework, app;


class end_alex_pobeda extends AbstractForm
{

    /**
     * @event button.click-Left 
     */
    function ExitBtn(UXMouseEvent $e = null)
    {
        app()->shutdown();
    }

    /**
     * @event return_btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_end_alex->hide();
        //debug log
        if ($this->form('maingame')->log_ingame->visible) {        
        Element::appendText($this->form('maingame')->log_ingame, "use function 'Return_Btn' \n"); }             
    }



}
