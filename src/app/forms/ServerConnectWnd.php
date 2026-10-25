<?php
namespace app\forms;

use app\forms\classes\Localization;
use app\forms\classes\Log;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class ServerConnectWnd extends AbstractForm
{


    /**
     * @event Btn_Cancel.click-Left 
     */
    function CancelConnect(UXMouseEvent $e = null)
    {
        Log::info('Cancelling connection...');
        
        $mp_wnd = $this->form('Client')->MainMenu->content->UIMultiplayerWnd->content;        
        
        $mp_wnd->GetClient()->cancelConnection();
        
        $mp_wnd->ServerConnectWnd->hide();
    }
    
    function ShowConnectDialog($targetIp = null, $targetPort = null)
    {
        $mp_wnd = $this->form('Client')->MainMenu->content->UIMultiplayerWnd->content;
    
        if ($targetIp === null)
        {
            $targetIp = trim($mp_wnd->Edit_ServerIP->text);
    
            if ($targetIp === '')
            {
                $targetIp = '127.0.0.1';
            }
        }
    
        if ($targetPort === null)
        {
            $targetPort = $mp_wnd->port;
        }
        
        $mp_wnd->ServerConnectWnd->show();
    
        $this->Connect_Label->text = Localization::get('Connect_Label') . "\n{$targetIp}:{$targetPort}";
    
        //$this->Status_Label->text = Localization::get('Status_Label_Process');
    
        //$this->progressBar->progress = -100;
    
        $this->Connect_Label->textColor = '#cccccc';

        $this->Btn_Cancel->text = Localization::get('Btn_Cancel');        
    
        $mp_wnd->button10->hide();
        $mp_wnd->button3->hide();
        $mp_wnd->button->hide();
        $mp_wnd->label->hide();
        $mp_wnd->labelAlt->hide();
        $mp_wnd->Edit_ServerIP->hide();
        $mp_wnd->Edit_PlayerName->hide();
        $mp_wnd->gen_nick_btn->hide();
        $mp_wnd->Btn_Connect2Server->hide();
        $mp_wnd->Btn_CreateServer->hide();
        $mp_wnd->Return_Btn->hide();
        
        if (!$this->progressBar->visible)
        {
            $this->progressBar->show();
            $this->Btn_Cancel->show();
            $this->progressBar_background->show();
            $this->Status_Label->show();            
        }
    }
    
    function HideConnectDialog()
    {
        $mp_wnd = $this->form('Client')->MainMenu->content->UIMultiplayerWnd->content;
        
        $mp_wnd->ServerConnectWnd->hide();
        
        $mp_wnd->button10->show();
        $mp_wnd->button3->show();
        $mp_wnd->button->show();
        $mp_wnd->label->show();
        $mp_wnd->labelAlt->show();
        $mp_wnd->Edit_ServerIP->show();
        $mp_wnd->Edit_PlayerName->show();
        $mp_wnd->gen_nick_btn->show();
        $mp_wnd->Btn_Connect2Server->show();
        $mp_wnd->Btn_CreateServer->show();
        $mp_wnd->Return_Btn->show();        
    }
    
    function FailedConnectState()
    {
        $this->progressBar->hide();
        $this->Btn_Cancel->hide();
        $this->progressBar_background->hide();
        $this->Status_Label->hide();
        
        $mp_wnd = $this->form('Client')->MainMenu->content->UIMultiplayerWnd->content;
        
        $this->Connect_Label->textColor = 'red';
        $this->Connect_Label->text = Localization::get($mp_wnd->GetConnectStatus());           
    }
    

}
