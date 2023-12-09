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
        app()->shutdown();
    }
    /**
     * @event btn_no.click-Left 
     */
    function DisagreeButton(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->fragment_exit->visible)
        {
            $this->form('maingame')->HideExitDlg();
        }
        if ($this->form('maingame')->fragment_menu->content->fragment_exit->visible)
        {
            $this->form('maingame')->fragment_menu->content->fragment_exit->hide();
        }
    }
}
