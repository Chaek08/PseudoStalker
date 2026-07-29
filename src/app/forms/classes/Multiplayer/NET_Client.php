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
    private $isRunning = false;
    private $onMessageCallback;
    private $onConnectCallback;
    private $onDisconnectCallback;
    private $readBuffer = '';
    
    public function GetPlayerID()
    {
        return $this->playerId;
    }
    
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
    
    public function conectToServer($ip, $port)
    {
        if ($this->IsConnected)
        {
            Log::warn('Client: already connected');
            return true;
        }
        
        try {
            $this->socket = new \php\net\Socket($ip, (int)$port);
            $in = $this->socket->getInput();
            $out = $this->socket->getOutput();
            
            $this->IsConnected = true;
            $this->isRunning = true;
            
            Log::info("Client: connected: $ip:$port");
            
            // Читаем приветствие и состояние
            $welcome = $this->readLineFromInput($in);
            $state   = $this->readLineFromInput($in);

            if ($welcome !== null)
            {
                Log::info('Client: received welcome: '. trim($welcome));
                
                /*
                if (preg_match('/WELCOME (.+)/', trim($welcome), $m))
                {
                    $this->playerId = $m[17];
                }
                */
            }
            if ($state !== null)
            {
                Log::info('Client: received state: '.trim($state));
            }
            
            if (preg_match('/WELCOME (.+)/', trim((string)$welcome), $matches))
            {
                $this->playerId = $matches[1];
            }
            
            if ($this->onConnectCallback)
            {
                call_user_func($this->onConnectCallback, trim((string)$welcome), trim((string)$state));
            }
            
            $this->startListening();
            
            return true;
            
        } catch (\Throwable $e) {
            Log::error('Client: connection error: '.$e->getMessage());
            $this->IsConnected = false;
            return false;
        }
    }
    
    
    private function readLineFromInput($in)
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
    
            $chunk = $in->read(1024);
    
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
                if ($line === null) { $this->disconnect(); break; }
                $line = trim($line);
                
                if ($line !== '')
                {
                    Log::info('Client: received - '.$line);
                    //dump($client->onMessageCallback);
                    if ($client->onMessageCallback)
                    {
                        call_user_func($client->onMessageCallback, $line);
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
        $this->isRunning = false;
        
        if ($this->socket)
        {
            try {
                $this->socket->close();
            } catch (\Throwable $e) {
                Log::warn('Client: close socket error', ['err' => $e->getMessage()]);
            }
            $this->socket = null;
        }
        
        $this->IsConnected = false;
        
        if ($this->onDisconnectCallback)
        {
            call_user_func($this->onDisconnectCallback);
        }
        
        Log::info('Client: disconnected');
    }
    
    // Колбэки для событий
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