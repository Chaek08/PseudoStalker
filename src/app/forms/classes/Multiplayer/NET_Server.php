<?php
namespace app\forms\classes\Multiplayer;

use php\gui\UXDialog;
use Throwable;
use php\net\ServerSocket;
use php\lang\Thread;
use php\framework\Logger;
use app\forms\classes\Debug;
use app\forms\classes\Log;

class NET_Server 
{
    protected  $IsRunning = false;
    protected  $serverThread;
    protected  $clients = [];
    protected  $clientRoles = [];
    protected  $playerPositions = [
        'actor' => ['x' => 0.0, 'y' => 0.0],
        'enemy' => ['x' => 0.0, 'y' => 0.0],
    ];
    
    protected $form;    

    public function addClient(string $cid, $client): void { $this->clients[$cid] = $client; }
    public function removeClient(string $cid): void { unset($this->clients[$cid], $this->clientRoles[$cid]); }
    public function setClientRole(string $cid, string $role): void { $this->clientRoles[$cid] = $role; }
    public function getClientRole(string $cid): ?string { return $this->clientRoles[$cid] ?? null; }
    public function getClients(): array { return $this->clients; }

    public function updatePlayerPos(string $playerId, float $x, float $y): void
    {
        if (!isset($this->playerPositions[$playerId])) $this->playerPositions[$playerId] = ['x'=>0.0,'y'=>0.0];
        $this->playerPositions[$playerId]['x'] = $x;
        $this->playerPositions[$playerId]['y'] = $y;
    }
    public function getSnapshotLine(): string
    {
        $p1 = $this->playerPositions['actor'] ?? ['x'=>0.0,'y'=>0.0];
        $p2 = $this->playerPositions['enemy'] ?? ['x'=>0.0,'y'=>0.0];
        return sprintf("STATE actor %.3f %.3f enemy %.3f %.3f\n", $p1['x'], $p1['y'], $p2['x'], $p2['y']);
    }

    public function __construct()
    {
        //$this->form = $form;
        
        $this->IsRunning = false;
        
        Log::info("[gastrit system] server class loaded");
    }

    public function startServer($host, $port)
    {
        if ($this->IsRunning)
        {
            Log::warn('Server: already running');
            return true;
        }

        Log::info('Server: start', ['host' => $host, 'port' => $port]);
        $this->IsRunning = true;
        try {
            $serverInstance = $this;
            $form = $this->form;
                    
            $this->serverThread = new Thread(function () use ($host, $port, $serverInstance) {
                $socket = null;
                try {
                    $socket = new ServerSocket();
                    Log::info('Server: trying bind', ['host' => $host, 'port' => $port]);
                    $socket->bind($host, (int)$port);

                    //$serverInstance->IsRunning = true;
                    Log::info('Server: bound & listening', ['host' => $host, 'port' => $port]);

                    while (true)
                    {
                        $client = $socket->accept();
                        
                        if (!$client)
                        {
                            continue;
                        }

                        $cid = spl_object_hash($client);
                        $serverInstance->addClient($cid, $client);
                        
                        $count = count($serverInstance->getClients());
                        
                        if ($count == 1)
                        {
                            $role = "actor";                      
                        }
                        elseif ($count == 2)
                        {
                            $role = "enemy";                             
                        }
                        else
                        {
                            $client->close();
                            continue;
                        }
                        
                        $serverInstance->setClientRole($cid, $role);
                        
                        Log::info('Server: client accepted', [
                            'id' => $cid,
                            'role' => $serverInstance->getClientRole($cid)//clientRoles[$cid]
                        ]);

                        $out = $client->getOutput();
                        $out->write("WELCOME " . $serverInstance->getClientRole($cid) . "\n");
                        $out->write($serverInstance->getSnapshotLine());
                        $out->flush();

                        $serverInstance->handleClientAsync($client);
                    }
                } catch (\Throwable $e) {
                    Log::error('Server: accept/bind loop error -- msg: ' . $e->getMessage());
                } finally {
                    Log::info('Server: main thread cleanup');
                    try {
                        if ($socket)
                        {
                            $socket->close();
                        }
                    } catch (\Throwable $e) {
                        Log::warn('Server: close server socket err: '.$e->getMessage());
                    }
                    //$serverInstance->IsRunning = false;
                }
            });

            $this->serverThread->start();
            return true;
        } catch (\Throwable $e) {
            Log::error('Server: start failed. '.$e->getMessage());
            \php\gui\UXDialog::showAndWait("Не удалось запустить сервер:\n" . $e->getMessage(), 'ERROR');
            $this->IsRunning = false;
            return false;
        }
    }
  
    public function handleClientAsync($client)
    {
        $serverInstance = $this;

        $clientThread = new Thread(function () use ($client, $serverInstance) {
            $cid = spl_object_hash($client);
            $input = $client->getInput();
            $output = $client->getOutput();

            try {
                while (!$client->isClosed()) //$serverInstance->IsRunning && 
                { 
                    $line = $serverInstance->readLineFromInput($input);
                    if ($line === null) break;

                    $line = trim($line);
                    //Log::info('Server: received from client | id = ' . $cid . ' | msg: '.$line);
                    
                    $parts = explode(' ', $line);
                    if (count($parts) >= 4 && $parts[0] === 'POS')
                    {
                        $playerId = $parts[1];
                        $x = (float)$parts[2];
                        $y = (float)$parts[3];

                        $serverInstance->updatePlayerPos($playerId, $x, $y);

                        $serverInstance->broadcastToOthers($cid, "POS $playerId $x $y\n");
                    }
                    elseif ($parts[0] === 'PING')
                    {
                        $output->write("PONG\n");
                        $output->flush();
                    }
                    else 
                    {
                        Log::info('Server: unknown message');
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Server: client handler error [id $cid] err: ".$e->getMessage());
            } finally {
                $serverInstance->removeClient($cid);
                try { $client->close(); } catch (\Throwable $e) {}
                Log::info('Server: client disconnected id: '.$cid);
            }
        });

        $clientThread->start();
    }

    public function readLineFromInput($in, $maxLen = 8192)
    {
        $buf = '';
        while (true)
        {
            $chunk = $in->read(1);
            if ($chunk === null || $chunk === '')
            {
                return $buf === '' ? null : $buf;
            }
            $ch = $chunk;
            if ($ch === "\n")
            {
                return $buf;
            }
            if ($ch !== "\r")
            {
                $buf .= $ch;
            }
            if (strlen($buf) >= $maxLen)
            {
                return $buf;
            }
        }
    }

    public function broadcastToOthers($senderCid, $message)
    {
        foreach ($this->clients as $cid => $client)
        {
            if ($cid !== $senderCid && !$client->isClosed())
            {
                try {
                    $out = $client->getOutput();
                    $out->write($message);
                    $out->flush();
                    Log::info("SEND TO " . $this->getClientRole($cid));
                } catch (\Throwable $e) {
                    Log::warn("Server: broadcast error ['to' => $cid, 'err' => $e->getMessage()]");
                }
            }
        }
    }

    public function stopServer()
    {
        if (!$this->IsRunning) return;

        $this->IsRunning = false;

        foreach ($this->clients as $client)
        {
            try { $client->close(); } catch (\Throwable $e) {}
        }
        $this->clients = [];

        Log::info('Server: stopped');
    }
    
    protected function callForm($formName)
    {
        if (!$this->form) return null;
    
        if (method_exists($this->form, $formName))
        {
            return $this->form->$formName();
        }
    
        return null;
    }  
}