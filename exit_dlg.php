<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use app\forms\classes\Localization;
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXMouseEvent; 

class exit_dlg extends AbstractForm
{
    private $localization;
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function UpdateDialogWnd()
    {
        $GLOBALS['ExitWndType'] = false;
        $GLOBALS['RewriteSaveType'] = false;
        $GLOBALS['ClientVersionErrorType'] = false;
        $GLOBALS['CorruptSaveType'] = false;
        $GLOBALS['EndGameWndType'] = false;
        $GLOBALS['RemoveSaveType'] = false;
    }
    function SetDialogWndType()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $this->dialog_warning->image = null;
        $this->btn_yes->show();
        $this->btn_yes->text = $this->localization->get('Yes_Label');
        $this->btn_no->text = $this->localization->get('No_Label');
        
        if ($GLOBALS['ExitWndType'])
        {
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_warning.png');
            $this->dialog_text->text = $this->localization->get('ExitDialog_Text');
        }
        if ($GLOBALS['EndGameWndType'])
        {
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_warning.png');
            $this->dialog_text->text = $this->localization->get('EndGameDialog_Text');
        }
        if ($GLOBALS['RewriteSaveType'])
        {  
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
            $this->dialog_text->text = $this->localization->get('brainAFKToast');
        }
        if ($GLOBALS['RemoveSaveType'])
        {  
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
            $this->dialog_text->text = $this->localization->get('RemoveSaveWnd_Text');
        }        
        if ($GLOBALS['CorruptSaveType'])
        {
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
            $this->dialog_text->text = $this->localization->get('SaveCorruptToast');
            
            $this->btn_yes->hide();
            $this->btn_no->text = 'OK';
        }
        if ($GLOBALS['ClientVersionErrorType'])
        {  
            $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
            $this->dialog_text->text = $this->localization->get('InvalidGameClientToast');
            
            $this->btn_yes->hide();
            $this->btn_no->text = 'OK';
        }
    }
    /**
     * @event btn_yes.click-Left 
     */
    function AcceptButton(UXMouseEvent $e = null)
    { 
        if ($GLOBALS['ExitWndType'])
        {
            $this->form('maingame')->LoadScreen();
        
            app()->shutdown();
        }
        if ($GLOBALS['EndGameWndType'])
        {
            $this->form('maingame')->ToggleHud();
            $this->form('maingame')->ResetGameClient();
            
            $this->form('maingame')->ExitDialog->hide();
        }        
        if ($GLOBALS['RewriteSaveType'])
        {
            $this->form('maingame')->MainMenu->content->UISaveWnd->content->BtnSaveGame();
            
            $this->form('maingame')->ExitDialog->hide();
        }
        if ($GLOBALS['RemoveSaveType'])
        {
            if ($this->form('maingame')->MainMenu->content->UILoadWnd->visible)
            {
                $this->form('maingame')->MainMenu->content->UILoadWnd->content->RemoveSaveBtn();
            }
            if ($this->form('maingame')->MainMenu->content->UISaveWnd->visible)
            {
                $this->form('maingame')->MainMenu->content->UISaveWnd->content->RemoveSaveBtn();
            }
                        
            $this->form('maingame')->ExitDialog->hide();
        }
        if ($GLOBALS['ClientVersionErrorType'] || $GLOBALS['CorruptSaveType'])
        {
            return;
        }
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
