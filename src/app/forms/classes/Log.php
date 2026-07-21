<?php
namespace app\forms\classes;

use php\lang\ThreadPool;
use php\framework\Logger;
use app\forms\classes\Log;
use php\time\Time;
use php\lang\System;


class Log
{
    private static $logFile = null;
    private static $initialized = false;
    private static $buildId = '(null)';
    private static $versionId = '(null)';
        
    private static $listeners = [];
    
    private static $buffer = [];
    private static $bufferLimit = 30; //лимит строк в буффере 

    private static $logPool = null;
    private static $flushPending = false;

    public static function onWrite(callable $listener)
    {
        self::$listeners[] = $listener;
    }

    private static function notify(string $text)
    {
        foreach (self::$listeners as $listener)
        {
            $listener($text);
        }
    }    

    public static function setBuildId(string $id)
    {
        self::$buildId = $id;

        if (self::$initialized)
        {
            self::writeHeader();
        }
    }
    
    public static function setVersionId(string $id)
    {
        self::$versionId = $id;

        if (self::$initialized)
        {
            self::writeHeader();
        }
    }
    
    public static function setBuildData(string $buildId, string $versionId)
    {
        self::setBuildId($buildId);
        self::setVersionId($versionId);
        
        Logger::info(self::$versionId . ', ' . self::$buildId);
    }
    
    private static function write(string $text)
    {
        self::ensure();
    
        $time = Time::now()->toString("HH:mm:ss");
        $line = "* [$time] $text\n";
    
        self::$buffer[] = $line;
    
        if (count(self::$buffer) >= self::$bufferLimit)
        {
            self::flush();
        }
    
        self::notify($text);
    }
    
    public static function info(string $text)
    {
        self::write($text);
        
        Logger::debug($text);
    }
    
    public static function warn(string $text)
    {
        self::write($text);
        
        Logger::warn($text);
    }
    
    public static function error(string $text)
    {
        self::write($text);
        
        Logger::error($text);
    }
    
    public static function result(string $text)
    {
        self::write($text);
        
        Logger::debug($text);
    }
    
    public static function command(string $text)
    {
        self::write($text);
    }    
    
    public static function flush()
    {
        if (!self::$logFile || empty(self::$buffer))
        {
            return;
        }
    
        if (self::$flushPending)
        {
            return;
        }
    
        self::$flushPending = true;
    
        $data = implode('', self::$buffer);
        $file = self::$logFile;
    
        self::$buffer = [];
    
        self::$logPool->execute(function () use ($file, $data)
        {
            Logger::info("LOG THREAD START");
        
            file_put_contents($file, $data, FILE_APPEND);
        
            Logger::info("LOG THREAD END");
        
            Log::$flushPending = false;
        });      
    }    

    private static function ensure()
    {
        if (!self::$initialized)
        {
            self::init();
        }
    }

    private static function init()
    {
        if (self::$initialized)
        {
            return;
        }

        $dir = "userdata/logs/";

        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }

        $user = System::getProperty("user.name");
        
        self::$logFile = $dir . "kte_{$user}.log";
        self::$logPool = ThreadPool::create(1, 1, 10000);

        self::writeHeader();
        self::$initialized = true;
    }

    private static function writeHeader()
    {
        if (!self::$logFile)
        {
            return;
        }

        $host = self::getComputerName();
        $user = System::getProperty("user.name") . " (" . $host . ")";

        file_put_contents(
            self::$logFile,
            "PseudoStalker, " . self::$buildId . "\n" .
            "User: " . $user . "\n" .
            "Time: " . Time::now()->toString("dd/MM/yyyy HH:mm:ss") . "\n\n"
        );
    }
     
    private static function getComputerName(): string
    {
        $env = System::getEnv();

        return $env["COMPUTERNAME"] ?? $env["HOSTNAME"] ?? "unknown";
    }
    
    public static function getLogFile(): ?string
    {
        self::flush();
        
        return self::$logFile;
    }    
}
