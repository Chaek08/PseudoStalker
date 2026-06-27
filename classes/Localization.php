<?php

namespace app\forms\classes;

use app\forms\classes\Debug;

class Localization {
    private static $translations = [];
    private static $language;
    
    private static $languages = [];    
    
    private static $directory = './gamedata/config/locales/';
    
    private static function loadLanguages()
    {
        if (!empty(self::$languages))
        {
            return;
        }
    
        if (!is_dir(self::$directory))
        {
            return;
        }
    
        foreach (scandir(self::$directory) as $file)
        {
            if (substr($file, -5) !== '.json')
            {
                continue;
            }
    
            $path = self::$directory . $file;
    
            $data = json_decode(file_get_contents($path), true);
    
            if (!isset($data['_meta']))
            {
                continue;
            }
    
            $code = $data['_meta']['code'] ?? null;
    
            if ($code)
            {
                self::$languages[$code] = $data['_meta'];
            }
        }
    }  

    public static function setLanguage($language)
    {
        self::loadLanguages();
        
        if (!self::isValidLanguage($language))
        {
            Debug::fatal("Unknown language: $language");
            return;
        }
        
        self::$language = $language;

        $filename = self::$directory . $language . '.json';

        if (file_exists($filename))
        {
            self::$translations = json_decode(file_get_contents($filename), true);
        }
        else
        {
            Debug::fatal("Localization file not found\n$filename");
        }
    }

    public static function get($key)
    {
        return self::$translations[$key] ?? $key;
    }

    public static function getCurrentLanguage()
    {
        return self::$language;
    }

    public static function getDisplayLanguage()
    {
        self::loadLanguages();
    
        return self::$languages[self::$language]['name'] ?? self::$language;
    }

    public static function getDisplayLanguages()
    {
        self::loadLanguages();
    
        return array_column(self::$languages, 'name');
    }

    public static function getLanguageCode($displayLanguage)
    {
        self::loadLanguages();
    
        foreach (self::$languages as $code => $meta)
        {
            if ($meta['name'] === $displayLanguage)
            {
                return $code;
            }
        }
    
        return null;
    }

    public static function isValidLanguage($language)
    {
        self::loadLanguages();
    
        return isset(self::$languages[$language]);
    }
}