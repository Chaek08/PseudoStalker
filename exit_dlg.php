<?php
namespace app\forms;

use std, gui, framework, app;


class exit_dlg extends AbstractForm
{
    /**
     * @event btn_yes.click-Left 
     */
    function AcceptButton(UXMouseEvent $e = null)
    { 
        $this->form('maingame')->LoadScreen();
        
        app()->shutdown();
    }
    /**
     * @event btn_no.click-Left 
     */
    function DisagreeButton(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ExitDialog->hide();
        
        $this->form('maingame')->ToggleHud();
    }
}
