<?php
namespace app\forms\classes;

use action\Animation;
use app\forms\Client;
use php\framework\Logger;
use app\forms\PseudoDebug;
use php\time\Time;
use php\lang\System;
use app\forms\classes\Log;

class Debug
{
    public const FATAL    = 'Fatal Error';
    public const ASSERT   = 'Assertion Failed';
    public const API      = 'API Failure';
    public const INTERNAL = 'Internal Error';

    private static $handling = false;
    private static $queue = [];
    
    private static $client;

    public static function setClient(Client $client)
    {
        self::$client = $client;
    }    
    public static function fatal(string $message, string $file = null, int $line = null)
    {
        self::backend(self::FATAL, $message, $file, $line); 
    }

    public static function fail(string $message, string $file = null, int $line = null)
    {
        self::backend(self::ASSERT, $message, $file, $line);
    }

    public static function apiFail(string $api, string $message, string $file = null, int $line = null)
    {
        self::backend(self::API, "$api failed: $message", $file, $line);
    }

    public static function internal(string $message, string $file = null, int $line = null)
    {
        self::backend(self::INTERNAL, $message, $file, $line);
    }

    private static function backend(string $type, string $message, ?string $file, ?int $line)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        //07.06.2026: теперь можно не передавать файл и номер строки при вызове, функция сама их вытягивает
        if ($file === null && isset($trace[1]['file']))
        {
            $file = $trace[1]['file'];
        }
    
        if ($line === null && isset($trace[1]['line']))
        {
            $line = $trace[1]['line'];
        }
    
        self::$queue[] = [$type, $message, $file, $line, $trace];
    
        self::processQueue();
    }

    private static function processQueue()
    {
        if (self::$handling)
        {
            return;
        }
    
        $item = array_shift(self::$queue);
    
        if (!$item)
        {
            return;
        }
    
        self::$handling = true;
    
        [$type, $message, $file, $line, $trace] = $item;
    
        self::logCrash($type, $message, $file, $line, $trace);
    
        if (!self::$client)
        {
            Logger::error("Debug client not initialized");
            self::$handling = false;
            return;
        }
    
        self::showWindow($type, $message, $file, $line, $trace);
    }
    
    public static function next()
    {
        self::$handling = false;
    
        self::processQueue();
    }    

    private static function logCrash(string $type, string $message, ?string $file, ?int $line, ?array $trace = null)
    {
        Log::crash($type);
        Log::crash("File: " . ($file ?? "unknown"));
        Log::crash("Line: " . ($line ?? 0));
        Log::crash("Reason: " . $message);
    
        if ($trace)
        {
            Log::trace("Stack trace:");
            foreach (explode("\n", self::formatTrace($trace)) as $line)
            {
                $line = trim($line);
                if ($line !== "")
                {
                    Log::trace($line);
                }
            }
        }
    }

    private static function formatTrace(array $trace): string
    {
        $out = [];
        $i = 0;

        foreach ($trace as $frame)
        {
            if (!isset($frame["file"]))
            {
                continue; 
            }
                

            $func =
                ($frame["class"] ?? "") .
                ($frame["type"] ?? "") .
                ($frame["function"] ?? "");

            $out[] = sprintf(
                "#%d %s(%s): %s()",
                $i++,
                $frame["file"],
                $frame["line"] ?? "?",
                $func ?: "global"
            );
        }

        return implode("\n", $out);
    }
    
    private static function showWindow(string $type, string $message, ?string $file, ?int $line,array $trace)
    {
        $wnd = self::$client->PseudoDebug;
    
        $overlay = self::$client->overlay;
        
        $overlay->visible = true;
        $overlay->toFront();
            
        //self::$client->overlay->visible = true;
        //self::$client->overlay->toFront();
    
        $wnd->visible = true;
        $wnd->toFront();
    
        $wnd->content->setData($type, $message, $file ?? 'unknown', $line ?? 0, self::formatTrace($trace));
    
        $wnd->content->popEffect();
    }      
}
