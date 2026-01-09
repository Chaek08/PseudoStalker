<?php
namespace app\forms\classes;

use app\forms\PseudoDebug;
use php\gui\UXApplication;
use ErrorException;
use Error;
use app\forms\classes\DebugBackend;
use php\framework\Logger;
use php\time\Time;
use php\lang\System;
use php\lang\Logger;

class Debug
{
    public const FATAL    = '*** Fatal Error ***';
    public const ASSERT   = '*** Assertion Failed ***';
    public const API      = '*** API Failure ***';
    public const INTERNAL = '*** Internal Error ***';

    private static $handling = false;
    private static $queue = [];
    private static $paused = false;

    
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
        self::backend(
            self::API,
            "$api failed: $message",
            $file,
            $line
        );
    }

    public static function internal(string $message, string $file = null, int $line = null)
    {
        self::backend(self::INTERNAL, $message, $file, $line);
    }

    private static function backend(string $type, string $message, ?string $file, ?int $line)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        self::$queue[] = [$type, $message, $file, $line, $trace];
    
        if ($type === self::FATAL || $type === self::ASSERT)
        {
            self::$paused = false;
        }
    
        self::processQueue();
    }
    
    private static function processQueue()
    {
        if (self::$handling)
            return;
    
        self::$handling = true;
    
        while (true)
        {
            if (self::$paused)
                break;
    
            $item = array_shift(self::$queue);
            if (!$item)
                break;
    
            [$type, $message, $file, $line, $trace] = $item;
    
            self::log($type, $message, $file, $line, $trace);
    
            $wnd = new PseudoDebug();
            $wnd->setData(
                $type,
                $message,
                $file ?? 'unknown',
                $line ?? 0
            );
    
            $wnd->showAndWait();
            $result = $wnd->getResult();
    
            if ($result === 'STOP')
            {
                if ($type === self::FATAL)
                {
                    exit(1); // нахуq
                }
                
                continue;
            }
    
            if ($result === 'DEBUG')
            {
                self::$handling = false;
                throw new \Exception("Debug break");
            }
    
            if ($type === self::FATAL)
            {
                exit(1);
            }
        }
    
        self::$handling = false;
    }

    private static function log(string $type, string $message, ?string $file, ?int $line, ?array $trace = null)
    {
        $text =
            "\n" .
            "$type\n" .
            "File: $file\n" .
            "Line: $line\n" .
            "Reason: $message\n";
            
        if ($trace)
        {
            $text .= "\nStack trace:\n";
            $text .= self::formatTrace($trace) . "\n";
        }            

        echo $text . "\n";
        
        //TODO: Полноценные логи, сейчас выводится только Error
        //сделать логи +- в сталкерском формате
/*
        $logDir = 'userdata/logs/';
    
        if (!is_dir($logDir))
        {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . 'kte_' . System::getProperty('user.name') . '.log';
    
        file_put_contents(
            $logFile,
            '[' . Time::now()->toString('dd/MM/YYYY HH:mm:ss') . '] ' . $text . "\n",
            FILE_APPEND
        );
*/
    }
    
    private static function formatTrace(array $trace): string
    {
        $out = [];
        $i = 0;
    
        foreach ($trace as $frame)
        {
            if (!isset($frame['file']))
                continue;
    
            $func =
                ($frame['class'] ?? '') .
                ($frame['type'] ?? '') .
                ($frame['function'] ?? '');
    
            $out[] = sprintf(
                "#%d %s(%s): %s()",
                $i++,
                $frame['file'],
                $frame['line'] ?? '?',
                $func ?: 'global'
            );
        }
    
        return implode("\n", $out);
    }
    
    public static function continueAfterStop()
    {
        if (!self::$paused)
            return;
    
        self::$paused = false;
        self::processQueue();
    }

}