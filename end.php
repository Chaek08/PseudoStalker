<?php
namespace app\forms;

use std, gui, framework, app;


class end extends AbstractForm
{
    /**
     * @event button.click-Left 
     */
    function ExitButton(UXMouseEvent $e = null)
    {    
        app()->shutdown();
    }

    /**
     * @event return_btn.click-Left 
     */
    function Return_Btn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_end->hide();
        //debug log
        if ($this->form('maingame')->log_ingame->visible) {        
        Element::appendText($this->form('maingame')->log_ingame, "use function 'Return_Btn' \n"); }          
    }
}
