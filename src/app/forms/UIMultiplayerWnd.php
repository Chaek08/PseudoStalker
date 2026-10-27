<?php
namespace app\forms;


use php\gui\UXImageView;
use php\gui\UXImage;
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
    private $server, $client;
    
    private $ip, $port;
    
    private $currentConnectStatus = null;
    
    private $avatarPath = null;  

    function InitMPModule()
    {
        if ($this->server) return; //защита от повторного захода из меню
    
        Log::info('[gastrit system] Init');
        $this->server = new NET_Server();
        $this->client = new NET_Client();
        $this->ip = "127.0.0.1";
        $this->port = "1337";
        
        $this->client->onMessage(function($message) {
            $this->form('Client')->MainGame->content->NET_HandleServerMessage($message);
        });  
        
        $this->client->onStatus(function($status) {
            uiLater(function() use ($status) {
                $this->currentConnectStatus = $status; //для хуйни в failed connect
                $this->ServerConnectWnd->content->Status_Label->text = Localization::get($status);
            });
        });               

/*        
        $this->client->onConnect(function($welcome, $state) {
            uiLater(function() use ($welcome, $state) {
                Log::info('Connected to server', ['welcome' => $welcome, 'state' => $state]);
            });
        });
*/        
        $this->client->onDisconnect(function() {
            uiLater(function() {
                Log::info('Disconnected from server');
                
                $this->form('Client')->MainMenu->content->SetupMenu();                
            });
        });
        
        $this->Edit_PlayerName->text = $this->client->getDefaultNickname();
        
        Log::info("[gastrit system] Loaded");
    }
    

    public function IsMultiplayerSessionActive()
    {
        return is_object($this->client) && $this->client->isConnected();
    }
    
    public function GetClient()
    {
        return $this->client;
    }
    
    public function GetConnectStatus()
    {
        return $this->currentConnectStatus;
    }

    public function GetAvatarPath()
    {
        return $this->avatarPath;
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
    function HotkeyConnect2Server(UXKeyEvent $e = null)
    {
        $this->Connect2ServerBtn();
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
    
            $this->ServerConnectWnd->content->ShowConnectDialog($targetIp, $targetPort);
    
            (new Thread(function() use ($targetIp, $targetPort) {
                $result = $this->client->connectToServer($targetIp, $targetPort);
    
                uiLater(function() use ($result, $targetIp, $targetPort) {
                    if ($result)
                    {
                        Log::info("Connected to server successfully at {$targetIp}:{$targetPort}");
                        
                        $this->ServerConnectWnd->content->HideConnectDialog();
                        
                        $this->form('Client')->ShowLoadScreen(function() {
                            $this->ReturnBtn();
                            $this->form('Client')->MainMenu->content->BtnStartGame(); //temp
                        });                        
    
                        $this->form('Client')->MainGame->content->NET_MultiplayerBehaviour($this->client);
                        
                        //точно ли здесь
                        $this->SendLocalAvatar();                     
                        
                    }
                    else
                    {
                        Log::warn("Failed to connect to server at {$targetIp}:{$targetPort}");

                        $this->ServerConnectWnd->content->FailedConnectState();
                        
                        Timer::after(1200, function() {
                            uiLater(function() {
                                $this->ServerConnectWnd->content->HideConnectDialog();
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
    }
    
    /**
     * @event ChangeAvatarHueta.click-Left 
     */
    function ChangeAvatar(UXMouseEvent $e = null)
    {    
        $this->ImageFileChooser->inputNode = $this->Avatar;
        
        if ($this->ImageFileChooser->execute())
        {
            $path = $this->ImageFileChooser->file;
    
            $this->Avatar->image = new UXImage($path);
            $this->avatarPath = $path;
        }     
    }    
    
    private function SendLocalAvatar(): void
    {
        $avatarPath = $this->GetAvatarPath();
    
        if ($avatarPath === null || !file_exists($avatarPath))
        {
            Log::info('No custom avatar selected');
            return;
        }
    
        $content = $this->form('Client')->MainGame->content;
    
        $content->NET_SetLocalAvatar($avatarPath);
        $this->client->sendAvatar($avatarPath);
    
        Log::info("Sending local avatar: {$avatarPath}");
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
