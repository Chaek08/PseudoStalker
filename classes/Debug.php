<?php
namespace app\forms\classes;

use php\framework\Logger;
use app\forms\PseudoDebug;
use php\time\Time;
use php\lang\System;
use app\forms\classes\Log;

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
        self::backend(self::API, "$api failed: $message", $file, $line);
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
        {
            return;
        }

        self::$handling = true;

        while (!$thisPaused = self::$paused)
        {
            $item = array_shift(self::$queue);
            if (!$item)
                break;

            [$type, $message, $file, $line, $trace] = $item;

            self::logCrash($type, $message, $file, $line, $trace);

            $wnd = new PseudoDebug();
            $wnd->setData(
                $type,
                $message,
                $file ?? "unknown",
                $line ?? 0
            );

            $wnd->showAndWait();
            $result = $wnd->getResult();

            if ($result === "STOP" && $type === self::FATAL)
            {
                exit(1);
            }

            if ($result === "DEBUG")
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

    public static function continueAfterStop()
    {
        if (!self::$paused)
        {
            return;
        }

        self::$paused = false;
        self::processQueue();
    }
}
