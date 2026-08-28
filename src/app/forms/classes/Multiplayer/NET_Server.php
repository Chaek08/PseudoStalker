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
    public  $IsRunning = false;
    protected $serverThread;
    protected $serverSocket;
    protected  $clients = [];
    protected  $clientRoles = [];
    protected $playerNicknames = [];    
    protected  $playerPositions = [
        'actor' => ['x' => 0.0, 'y' => 0.0],
        'enemy' => ['x' => 0.0, 'y' => 0.0],
    ];
    
    protected $form;    

    public function addClient(string $cid, $client): void { $this->clients[$cid] = $client; }
    
    public function removeClient(string $cid): void
    {
        $role = $this->clientRoles[$cid] ?? null;
    
        unset($this->clients[$cid], $this->clientRoles[$cid]);
    
        if ($role !== null)
        {
            unset($this->playerNicknames[$role]);
        }
    }
    
    public function setClientRole(string $cid, string $role): void { $this->clientRoles[$cid] = $role; }
    public function getClientRole(string $cid): ?string { return $this->clientRoles[$cid] ?? null; }
    public function getClients(): array { return $this->clients; }
        
    public function setPlayerNickname(string $playerId, string $nickname): void
    {
        $this->playerNicknames[$playerId] = $nickname;
    }
    
    public function getPlayerNickname(string $playerId): ?string
    {
        return $this->playerNicknames[$playerId] ?? null;
    }
    
    public function getNicknamesSnapshot(): string
    {
        $lines = [];
    
        foreach ($this->playerNicknames as $playerId => $nickname)
        {
            $lines[] = "NICK {$playerId} {$nickname}";
        }
    
        return implode("\n", $lines) . ($lines ? "\n" : '');
    } 
    
    public function broadcastNicknames(): void
    {
        $snapshot = $this->getNicknamesSnapshot();
    
        if ($snapshot === '')
        {
            return;
        }
    
        foreach ($this->clients as $cid => $client)
        {
            if ($client->isClosed())
            {
                continue;
            }
    
            try
            {
                $out = $client->getOutput();
                $out->write($snapshot);
                $out->flush();
            }
            catch (\Throwable $e)
            {
                Log::warn("Server: nickname broadcast error [{$cid}] ".$e->getMessage());
            }
        }
    }  

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
    
        try
        {
            $serverInstance = $this;
    
            $this->serverThread = new Thread(function () use ($host, $port, $serverInstance)
            {
                try
                {
                    $socket = new ServerSocket();
    
                    $serverInstance->setServerSocket($socket);
    
                    Log::info('Server: trying bind', ['host' => $host, 'port' => $port]);
    
                    $socket->bind($host, (int)$port);
    
                    Log::info('Server: bound & listening', ['host' => $host, 'port' => $port]);
    
                    while ($serverInstance->IsRunning)
                    {
                        try
                        {
                            $client = $socket->accept();
                        }
                        catch (\Throwable $e)
                        {
                            if (!$serverInstance->IsRunning)
                            {
                                break;
                            }
    
                            Log::error('Server: accept error: '.$e->getMessage());
    
                            break;
                        }
    
                        if (!$client)
                        {
                            continue;
                        }
    
                        if (!$serverInstance->IsRunning)
                        {
                            try {
                                $client->close();
                            } catch (\Throwable $e) {}
    
                            break;
                        }
    
                        $cid = spl_object_hash($client);
    
                        $serverInstance->addClient($cid, $client);
    
                        $count = count($serverInstance->getClients());
    
                        if ($count == 1)
                        {
                            $role = 'actor';
                        }
                        elseif ($count == 2)
                        {
                            $role = 'enemy';
                        }
                        else
                        {
                            $serverInstance->removeClient($cid);
    
                            try {
                                $client->close();
                            } catch (\Throwable $e) {}
    
                            continue;
                        }
    
                        $serverInstance->setClientRole($cid, $role);
    
                        Log::info('Server: client accepted', ['id' => $cid, 'role' => $role]);
    
                        try
                        {
                            $out = $client->getOutput();
    
                            $out->write("WELCOME ".$role."\n");
    
                            $out->write($serverInstance->getSnapshotLine());
                            $out->write($serverInstance->getNicknamesSnapshot());
    
                            $out->flush();
                        }
                        catch (\Throwable $e)
                        {
                            Log::warn('Server: welcome error: '.$e->getMessage());
    
                            $serverInstance->removeClient($cid);
    
                            try {
                                $client->close();
                            } catch (\Throwable $e) {}
    
                            continue;
                        }
    
                        $serverInstance->handleClientAsync($client);
                    }
                }
                catch (\Throwable $e)
                {
                    if ($serverInstance->IsRunning)
                    {
                        Log::error('Server: main loop error -- '.$e->getMessage());
                    }
                }
                finally
                {
                    $serverInstance->IsRunning = false;
                    $serverInstance->clearServerSocket();
    
                    Log::info('Server: main thread cleanup');
                }
            });
    
            $this->serverThread->start();
    
            return true;
        }
        catch (\Throwable $e)
        {
            Log::error('Server: start failed. '.$e->getMessage());
    
            UXDialog::showAndWait("Не удалось запустить сервер:\n".$e->getMessage(), 'ERROR');
    
            $this->IsRunning = false;
    
            return false;
        }
    }
    
    public function sendToClient($cid, $message)
    {
        if (!isset($this->clients[$cid]))
        {
            return false;
        }
    
        $client = $this->clients[$cid];
    
        if ($client->isClosed())
        {
            return false;
        }
    
        try
        {
            $out = $client->getOutput();
            $out->write($message);
            $out->flush();
    
            return true;
        }
        catch (\Throwable $e)
        {
            Log::warn("Server: send error [id $cid] " . $e->getMessage());
    
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
                    elseif ($parts[0] === 'SHOT')
                    {
                        $playerId = $parts[1];
                    
                        $serverInstance->broadcastToOthers($cid, "SHOT $playerId\n");
                    }
                    elseif ($parts[0] === 'RELOAD')
                    {
                        $playerId = $parts[1];
                    
                        $serverInstance->broadcastToOthers($cid, "RELOAD $playerId\n");
                    }
                    elseif ($parts[0] === 'WEAPON' && count($parts) >= 3)
                    {
                        $playerId = $parts[1];
                        $weaponType = $parts[2];
                    
                        $serverInstance->broadcastToOthers($cid, "WEAPON {$playerId} {$weaponType}\n");
                    }                            
                    elseif ($parts[0] === 'NICK' && count($parts) >= 3)
                    {
                        $playerId = $parts[1];
                    
                        $nickname = trim(substr($line, strlen("NICK {$playerId} ")));
                    
                        if ($nickname === '')
                        {
                            continue;
                        }

                        $serverInstance->setPlayerNickname($playerId, $nickname);
                    
                        //Log::info("SERVER GOT NICK: {$playerId} = {$nickname}");
                    
                        $serverInstance->broadcastNicknames();
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
                    //Log::info("SEND TO " . $this->getClientRole($cid));
                } catch (\Throwable $e) {
                    Log::warn("Server: broadcast error ['to' => $cid, 'err' => $e->getMessage()]");
                }
            }
        }
    }

    public function stopServer()
    {
        if (!$this->IsRunning)
        {
            return;
        }
    
        Log::info('Server: stopping');
    
        $this->IsRunning = false;

        foreach ($this->clients as $cid => $client)
        {
            try
            {
                $client->close();
            }
            catch (\Throwable $e)
            {
            }
        }
    
        $this->clients = [];
        $this->clientRoles = [];
    
        if ($this->serverSocket)
        {
            try
            {
                $this->serverSocket->close();
            }
            catch (\Throwable $e)
            {
            }
    
            $this->serverSocket = null;
        }
    
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
        
    public function setServerSocket($socket): void
    {
        $this->serverSocket = $socket;
    }
    
    public function getServerSocket()
    {
        return $this->serverSocket;
    }
    
    public function clearServerSocket(): void
    {
        $this->serverSocket = null;
    }    
}