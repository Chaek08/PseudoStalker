<?php
namespace app\forms;

use std, gui, framework, app;


class help extends AbstractForm
{
    /**
     * @event start_button.click-Left 
     */
    function NewGameButton(UXMouseEvent $e = null)
    {        
        $this->form('maingame')->fragment_help->hide();
        //debug log        
        if ($this->form('maingame')->log_ingame->show) {        
        Element::appendText($this->form('maingame')->log_ingame, "use function 'NewGameButton' \n");}                
    }
    /**
     * @event exit_button.click-Left 
     */
    function ExitButton(UXMouseEvent $e = null)
    {  
        app()->shutdown();
    }
}
