<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use app\forms\classes\Localization;
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXMouseEvent; 

class exit_dlg extends AbstractForm
{
    const TYPE_EXIT               = 'exit';
    const TYPE_END_GAME           = 'end_game';
    const TYPE_REWRITE_SAVE       = 'rewrite_save';
    const TYPE_REMOVE_SAVE        = 'remove_save';
    const TYPE_CORRUPT_SAVE       = 'corrupt_save';
    const TYPE_CLIENT_VERSION_ERR = 'client_version_error';
    const TYPE_LOAD_WITH_LOSS     = 'load_with_loss';

    private $currentType = null;
        
    function showDialog(string $type)
    {
        $this->dialog_warning->image = null;
        $this->btn_yes->show();

        $this->btn_yes->text = Localization::get('Yes_Label');
        $this->btn_no->text  = Localization::get('No_Label');

        switch ($type)
        {
            case self::TYPE_EXIT:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_warning.png');
                $this->dialog_text->text = Localization::get('ExitDialog_Text');
                break;

            case self::TYPE_END_GAME:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_warning.png');
                $this->dialog_text->text = Localization::get('EndGameDialog_Text');
                break;

            case self::TYPE_REWRITE_SAVE:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
                $this->dialog_text->text = Localization::get('brainAFKToast');
                break;

            case self::TYPE_REMOVE_SAVE:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
                $this->dialog_text->text = Localization::get('RemoveSaveWnd_Text');
                break;

            case self::TYPE_CORRUPT_SAVE:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
                $this->dialog_text->text = Localization::get('SaveCorruptToast');
                $this->btn_yes->hide();
                $this->btn_no->text = 'OK';
                break;

            case self::TYPE_CLIENT_VERSION_ERR:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_error.png');
                $this->dialog_text->text = Localization::get('InvalidGameClientToast');
                $this->btn_yes->hide();
                $this->btn_no->text = 'OK';
                break;
                
            case self::TYPE_LOAD_WITH_LOSS:
                $this->dialog_warning->image = new UXImage('res://.data/ui/exit_dialog/dialog_warning.png');
                $this->dialog_text->text = Localization::get('LoadWithLossDialog_Text');
                break;                
        }

        $this->currentType = $type;
        $this->form('Client')->ExitDialog->show();
    }

    /**
     * @event btn_yes.click-Left 
     */
    function AcceptButton(UXMouseEvent $e = null)
    { 
        switch ($this->currentType)
        {
            case self::TYPE_EXIT:
                $this->form('Client')->ShowLoadScreen(function() {
                    UXApplication::runLater(function() {
                        $this->form('Client')->DestroyClient();
                    });
                });
                break;

            case self::TYPE_END_GAME:
                $this->form('Client')->MainGame->content->ResetGameClient();
                $this->form('Client')->ExitDialog->hide();
                break;

            case self::TYPE_REWRITE_SAVE:
                $this->form('Client')->MainMenu->content->UISaveWnd->content->BtnSaveGame();
                $this->form('Client')->ExitDialog->hide();
                break;

            case self::TYPE_REMOVE_SAVE:
                if ($this->form('Client')->MainMenu->content->UILoadWnd->visible)
                {
                    $this->form('Client')->MainMenu->content->UILoadWnd->content->RemoveSaveBtn();
                }
                if ($this->form('Client')->MainMenu->content->UISaveWnd->visible)
                {
                    $this->form('Client')->MainMenu->content->UISaveWnd->content->RemoveSaveBtn();
                }
                $this->form('Client')->ExitDialog->hide();
                break;
                
            case self::TYPE_LOAD_WITH_LOSS:
                $this->form('Client')->MainMenu->content->UILoadWnd->content->BtnLoadSave();
                $this->form('Client')->ExitDialog->hide();
                break;                

            case self::TYPE_CORRUPT_SAVE:
            case self::TYPE_CLIENT_VERSION_ERR:
                return; //в пизду запретить нахуй
        }
    }

    /**
     * @event btn_no.click-Left 
     */
    function DisagreeButton(UXMouseEvent $e = null)
    {
        $this->form('Client')->ExitDialog->hide();
        
        if (!$this->form('Client')->MainMenu->visible)
        {
            $this->form('Client')->MainGame->content->RenderHud(true);
        }
    }
}
