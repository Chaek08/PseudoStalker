<?php
namespace app\forms\classes;

use php\gui\framework\behaviour\custom\AbstractBehaviour;
use Throwable;
use action\Media;
use php\lang\Thread;

class DimaAsyncHackEbatNaxyi
{
    private static $channels = [];

    public static function playSfxSound(string $path, string $channel)
    {
        $client = app()->form('Client');

        if (!$GLOBALS['AllSounds'] || $client->MainMenu->visible)
        {
            return;
        }

        if (!in_array($channel, self::$channels, true)) 
        {
            self::$channels[] = $channel;
        }

        (new Thread(function () use ($path, $channel) {
            Media::open($path, true, $channel);
        }))->start();
    }

    public static function pauseAllSfx()
    {
        foreach (self::$channels as $channel)
        {
            try
            {
                Media::pause($channel);
            }
            catch (\Throwable $e)
            {
            }
        }

        self::$channels = [];
    }   
}