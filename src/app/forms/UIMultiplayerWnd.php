<?php
namespace app\forms;


use app\forms\classes\Log;
use php\gui\UXApplication;
use php\gui\UXDialog;
use php\lang\Thread;
use php\framework\Logger;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXKeyEvent; 

use app\forms\classes\Multiplayer\NET_Server;
use app\forms\classes\Multiplayer\NET_Client;

class UIMultiplayerWnd extends AbstractForm
{
    private $server;
    private $client;
    private $ip;
    private $port;
    
    function InitMPModule()
    {
        if ($this->server) return; //защита от повторного захода из меню
    
        Log::info('[gastrit system] Init');
        $this->server = new NET_Server();
        $this->client = new NET_Client();
        $this->ip = "127.0.0.1";
        $this->port = "1337";
        
        $this->client->onMessage(function($message) {
            Log::info('GET MSG');
            $this->form('Client')->MainGame->content->NET_HandleServerMessage($message);
        });         
        
        $this->client->onConnect(function($welcome, $state) {
            uiLater(function() use ($welcome, $state) {
                Log::info('Connected to server', ['welcome' => $welcome, 'state' => $state]);
            });
        });
        
        $this->client->onDisconnect(function() {
            uiLater(function() {
                Log::info('Disconnected from server');
            });
        });
        
        $this->Edit_PlayerName->text = $this->client->getDefaultNickname();
        
        Log::info("[gastrit system] MPModule Loaded");
    }


    /**
     * @event main_frame.click-Left 
     */
    function doMain_frameClickLeft(UXMouseEvent $e = null)
    {
        
    }

    /**
     * @event Return_Btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('Client')->MainMenu->content->dynamic_background->toBack();
        $this->form('Client')->MainMenu->content->UIMultiplayerWnd->hide();
    }



    /**
     * @event Edit_PlayerName.keyDown-Enter 
     */
    function doEdit_PlayerNameKeyDownEnter(UXKeyEvent $e = null)
    {
   
    }

    /**
     * @event Btn_CreateServer.click-Left 
     */
    function CreateServerBtn(UXMouseEvent $e = null)
    {
        $result = $this->server->startServer($this->ip === '127.0.0.1' ? '0.0.0.0' : $this->ip, $this->port);
    
        if ($result)
        {
            Log::info('Server started successfully');
    
            $this->Connect2ServerBtn();
        }
    }

    /**
     * @event Btn_Connect2Server.click-Left 
     */
    function Connect2ServerBtn(UXMouseEvent $e = null)
    {
        if (!$this->client->isConnected())
        {
            $this->UpdateClientNickname();
        
            $input = trim($this->Edit_ServerIP->text);
    
            if ($input == '')
            {
                $targetIp = '127.0.0.1';
                $targetPort = $this->port;
            }
            else
            {
                if (strpos($input, ':') !== false)
                {
                    list($targetIp, $targetPort) = explode(':', $input, 2);
                    $targetPort = is_numeric($targetPort) ? (int)$targetPort : $this->port;
                }
                else
                {
                    $targetIp = $input;
                    $targetPort = $this->port;
                }
            }
    
            //$dlg = $this->form('ConnectDialog');
            //$dlg->connectIpLabel->text = "Connect to:\n{$targetIp}:{$targetPort}";
            //$dlg->connectStatusLabel->text = "Connecting...";
            //$dlg->show();
    
            (new Thread(function() use ($targetIp, $targetPort) {
                $result = $this->client->connectToServer($targetIp, $targetPort);
    
                uiLater(function() use ($result, $targetIp, $targetPort) {
                    if ($result)
                    {
                        Log::info("Connected to server successfully at {$targetIp}:{$targetPort}");
                        //$dlg->connectStatusLabel->text = "Connected!";
                        
                        //$dlg->hide();
    
                        //$this->labelAlt->text = "CONNECTED";
                        
                        $this->form('Client')->ShowLoadScreen(function() {
                            $this->ReturnBtn();
                            $this->form('Client')->MainMenu->content->BtnStartGame(); //temp
                        });                        
    
                        $this->form('Client')->MainGame->content->NET_MultiplayerBehaviour($this->client);
                        
                    }
                    else
                    {
                        Log::warn("Failed to connect to server at {$targetIp}:{$targetPort}");
                        //$dlg->hide();
    
                        UXDialog::showAndWait("Failed to connect to the server:\n{$targetIp}:{$targetPort}", 'ERROR');
                    }
                });
            }))->start();
    
        }
        else
        {
            Log::warn('Client is already connected');
        }
    }
    private function UpdateClientNickname()
    {
        $nickname = trim($this->Edit_PlayerName->text);
    
        if ($nickname === '')
        {
            $nickname = $this->client->getDefaultNickname();
        }
    
        $this->client->setNickname($nickname);
    
        Log::info('Player nickname set: ' . $this->client->getNickname());
    }
    /**
     * @event Edit_ServerIP.keyDown-Enter 
     */
    function doEdit_ServerIPKeyDownEnter(UXKeyEvent $e = null)
    {    
        
    }

    /**
     * @event gen_nick_btn.click-Left 
     */
    function gen_nick(UXMouseEvent $e = null)
    {    
        $words = [
            'Huesos',
            'Gastrit',
            'Pidor',
            'Gnida',
            'Lox',
            'Ishak',
            'Goblin',
            'Mrazota',
            'Mudak',
            'Chmo',
            'Utyrok',
            'Dalbaeb',
            'Ogloeb',
            'Suchara',
            'Vodolaz',
            'Psi',
            'Blyahamuha',
            'WandererNikolai',
            'Energia',
            'Rassolnikbebe228'
        ];
    
        $word = $words[rand(0, count($words) - 1)];
        $number = rand(10, 9999);
    
        $nickname = $word . '_' . $number;
    
        $this->Edit_PlayerName->text = $nickname;
    }


}
