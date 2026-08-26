<?php
namespace app\forms\classes\Multiplayer;

use php\lang\System;
use Throwable;
use php\framework\Logger;
use app\forms\classes\Debug;
use app\forms\classes\Log;
use php\lang\Thread;

class NET_Client 
{
    private $IsConnected = false;
    private $socket;
    private $playerId;
    private $clientThread;
    public $isRunning = false;
    private $onMessageCallback;
    private $onConnectCallback;
    private $onDisconnectCallback;
    private $readBuffer = '';
    
    private $nickname = 'PLAYER';    
    
    public function __construct()
    {
        $this->IsConnected = false;
        Log::info("[gastrit system] client class loaded");
    }
    
    public function getDefaultNickname(): string
    {
        $env = System::getEnv();
    
        $domain = $env["COMPUTERNAME"] ?? "LOCAL";
        $user = System::getProperty("user.name") ?? "PLAYER";
    
        return "@{$domain}\\{$user}";
    }    
    
    public function setNickname(string $nickname): void
    {
        $nickname = trim($nickname);
    
        if ($nickname === '')
        {
            $nickname = $this->getDefaultNickname();
        }
    
        $this->nickname = $nickname;
    }
    
    public function getNickname(): string
    {
        return $this->nickname;
    }    
    
    public function connectToServer($ip, $port)
    {
        if ($this->IsConnected)
        {
            Log::warn('Client: already connected');
            return true;
        }
    
        try
        {
            $this->readBuffer = '';
            $this->playerId = null;
    
            $this->socket = new \php\net\Socket($ip, (int)$port);
    
            $this->IsConnected = true;
            $this->isRunning = true;
    
            $in = $this->socket->getInput();
    
            Log::info("Client: connected: {$ip}:{$port}");
    
            $welcome = $this->readLineFromInput($in);
            $state   = $this->readLineFromInput($in);
    
            if ($welcome !== null &&
                preg_match('/^WELCOME (.+)$/', trim($welcome), $matches))
            {
                $this->playerId = trim($matches[1]);
    
                Log::info("Client: assigned player id = {$this->playerId}");
    
                $this->sendNickname();
            }
    
            if ($this->onConnectCallback)
            {
                call_user_func($this->onConnectCallback, trim((string)$welcome), trim((string)$state));
            }
    
            $this->startListening();
    
            return true;
        }
        catch (\Throwable $e)
        {
            Log::error('Client: connection error: '.$e->getMessage());
    
            $this->IsConnected = false;
            $this->isRunning = false;
            $this->socket = null;
    
            return false;
        }
    }
    
    
    public function readLineFromInput($in)
    {
        while (true)
        {
            $pos = strpos($this->readBuffer, "\n");
    
            if ($pos !== false)
            {
                $line = substr($this->readBuffer, 0, $pos);
                $this->readBuffer = substr($this->readBuffer, $pos + 1);
    
                return rtrim($line, "\r");
            }
    
            try
            {
                $chunk = $in->read(1024);
            }
            catch (\Throwable $e)
            {
                return null;
            }
    
            if ($chunk === null || $chunk === '')
            {
                return $this->readBuffer === '' ? null : $this->readBuffer;
            }
    
            $this->readBuffer .= $chunk;
        }
    }

    
    private function startListening()
    {
        $socket = $this->socket;
        $client = $this;
    
        $this->clientThread = new Thread(function () use ($socket, $client) {
            if (!$socket) return;
    
            $in = $socket->getInput();
    
            while ($client->isRunning && $in)
            {
                $line = $client->readLineFromInput($in);
    
                if ($line === null)
                {
                    if ($client->isRunning)
                    {
                        $client->disconnect();
                    }
                
                    break;
                }
    
                $line = trim($line);
    
                if ($line !== '')
                {
                    Log::info('Client: received - '.$line);
    
                    $callback = $client->getOnMessageCallback();
                    
                    if ($callback)
                    {
                        call_user_func($callback, $line);
                    }
                }
            }
        });
    
        $this->clientThread->start();
    }

    
    public function sendPosition($x, $y)
    {
        if (!$this->IsConnected || !$this->socket || !$this->playerId)
        {
            Log::info('Client: cannot send position - not connected');
            return false;
        }
        
        try {
            $out = $this->socket->getOutput();
            $out->write("POS {$this->playerId} $x $y\n");
            $out->flush();
            
            //Log::info('Client: sent position', ['x' => $x, 'y' => $y]);
            return true;
            
        } catch (\Throwable $e) {
            Log::info('Client: send position error: '.$e->getMessage(), ['err' => $e->getMessage()]);
            return false;
        }
    }
    
    public function sendShot()
    {
        return $this->sendMessage("SHOT {$this->playerId}");
    }    
    
    public function sendNickname()
    {
        return $this->sendMessage("NICK {$this->playerId} {$this->nickname}");
    }
    
    public function sendMessage($message)
    {
        if (!$this->IsConnected || !$this->socket)
        {
            Log::warn('Client: cannot send message - not connected');
            return false;
        }
        
        try {
            $out = $this->socket->getOutput();
            $out->write($message . "\n");
            $out->flush();
            
            Log::info('Client: sent message', ['msg' => $message]);
            return true;
            
        } catch (\Throwable $e) {
            Log::warn('Client: send message error', ['err' => $e->getMessage()]);
            return false;
        }
    }
    
    public function ping()
    {
        return $this->sendMessage("PING");
    }
    
    public function disconnect()
    {
        if (!$this->IsConnected && !$this->isRunning)
        {
            return;
        }
    
        $this->isRunning = false;
        $this->IsConnected = false;
    
        $socket = $this->socket;
        $this->socket = null;
    
        if ($socket)
        {
            try
            {
                $socket->close();
            }
            catch (\Throwable $e)
            {
            }
        }
    
        if ($this->onDisconnectCallback)
        {
            call_user_func($this->onDisconnectCallback);
        }
    
        Log::info('Client: disconnected');
    }
    
    // Колбэки для событий
    
    public function getOnMessageCallback()
    {
        return $this->onMessageCallback;
    }    
    
    public function onMessage($callback)
    {
        $this->onMessageCallback = $callback;
    }
    
    public function onConnect($callback)
    {
        $this->onConnectCallback = $callback;
    }
    
    public function onDisconnect($callback)
    {
        $this->onDisconnectCallback = $callback;
    }
    
    public function isConnected()
    {
        return $this->IsConnected;
    }
    
    public function getPlayerId()
    {
        return $this->playerId;
    }
}