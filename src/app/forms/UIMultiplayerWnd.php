<?php
namespace app\forms;


use app\forms\classes\Localization;
use php\time\Timer;
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
    
    private $currentConnectStatus = null;    

    function InitMPModule()
    {
        if ($this->server) return; //защита от повторного захода из меню
    
        Log::info('[gastrit system] Init');
        $this->server = new NET_Server();
        $this->client = new NET_Client();
        $this->ip = "127.0.0.1";
        $this->port = "1337";
        
        $this->client->onMessage(function($message) {
            //Log::info('GET MSG');
            $this->form('Client')->MainGame->content->NET_HandleServerMessage($message);
        });  
        
        $this->client->onStatus(function($status) {
            uiLater(function() use ($status) {
                $this->currentConnectStatus = $status; //для хуйни в failed connect
                $this->Status_Label->text = Localization::get($status);
            });
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
        
        Log::info("[gastrit system] Loaded");
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
    
            $this->ShowConnectDialog($targetIp, $targetPort);
    
            (new Thread(function() use ($targetIp, $targetPort) {
                $result = $this->client->connectToServer($targetIp, $targetPort);
    
                uiLater(function() use ($result, $targetIp, $targetPort) {
                    if ($result)
                    {
                        Log::info("Connected to server successfully at {$targetIp}:{$targetPort}");
                        
                        $this->HideConnectDialog();
                        
                        $this->form('Client')->ShowLoadScreen(function() {
                            $this->ReturnBtn();
                            $this->form('Client')->MainMenu->content->BtnStartGame(); //temp
                        });                        
    
                        $this->form('Client')->MainGame->content->NET_MultiplayerBehaviour($this->client);
                        
                    }
                    else
                    {
                        Log::warn("Failed to connect to server at {$targetIp}:{$targetPort}");

                        $this->progressBar->hide();
                        $this->Btn_Cancel->hide();
                        $this->button4->hide();
                        $this->Status_Label->hide();
                        
                        $this->Connect_Label->textColor = 'red';
                        $this->Connect_Label->text = Localization::get($this->currentConnectStatus);
                        
                        Timer::after(1200, function() {
                            uiLater(function() {
                                $this->HideConnectDialog();
                            });
                        });                        
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
        
    function ShowConnectDialog($targetIp = null, $targetPort = null)
    {
        if ($targetIp === null)
        {
            $targetIp = trim($this->Edit_ServerIP->text);
    
            if ($targetIp === '')
            {
                $targetIp = '127.0.0.1';
            }
        }
    
        if ($targetPort === null)
        {
            $targetPort = $this->port;
        }
    
        $this->Connect_Label->text = Localization::get('Connect_Label') . "\n{$targetIp}:{$targetPort}";
    
        //$this->Status_Label->text = Localization::get('Status_Label_Process');
    
        $this->progressBar->progress = -100;
    
        $this->Connect_Label->show();
        $this->Connect_Label->textColor = '#cccccc';
        $this->Status_Label->show();
        $this->progressBar->show();
        $this->button4->show();
        $this->GameLogo_Image->show();
        $this->Btn_Cancel->show();
        $this->Btn_Cancel->text = Localization::get('Btn_Cancel');        
    
        $this->button10->hide();
        $this->button3->hide();
        $this->button->hide();
        $this->label->hide();
        $this->labelAlt->hide();
        $this->Edit_ServerIP->hide();
        $this->Edit_PlayerName->hide();
        $this->gen_nick_btn->hide();
        $this->Btn_Connect2Server->hide();
        $this->Btn_CreateServer->hide();
        $this->Return_Btn->hide();
    }
    
    function HideConnectDialog()
    {
        $this->Connect_Label->hide();
        $this->Status_Label->hide();
        $this->progressBar->hide();
        $this->button4->hide();
        $this->GameLogo_Image->hide();
        $this->Btn_Cancel->hide();
    
        $this->button10->show();
        $this->button3->show();
        $this->button->show();
        $this->label->show();
        $this->labelAlt->show();
        $this->Edit_ServerIP->show();
        $this->Edit_PlayerName->show();
        $this->gen_nick_btn->show();
        $this->Btn_Connect2Server->show();
        $this->Btn_CreateServer->show();
        $this->Return_Btn->show();
    }
    
    /**
     * @event Btn_Cancel.click-Left 
     */
    function CancelConnect(UXMouseEvent $e = null)
    {
        Log::info('Cancelling connection...');
    
        $this->client->cancelConnection();
    
        $this->HideConnectDialog();
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
