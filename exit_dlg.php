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
        if ($this->form('maingame')->MainMenu->visible)
        {
            $this->form('maingame')->MainMenu->content->HideExitDialog();
        }
        else 
        {
            $this->form('maingame')->HideExitDialog();
        }
    }
}
