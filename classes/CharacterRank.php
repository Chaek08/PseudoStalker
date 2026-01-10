<?php
namespace app\forms\classes;

class CharacterRank
{
    private static $rank = [];
    private static $baseRank = [];

    public static function init(string $character, int $baseValue): void
    {
        if (!isset(self::$rank[$character]))
        {
            self::$rank[$character] = $baseValue;
            self::$baseRank[$character] = $baseValue;
        }
    }

    public static function get(string $character): int
    {
        return self::$rank[$character] ?? 0;
    }

    public static function getBase(string $character): int
    {
        return self::$baseRank[$character] ?? 0;
    }

    public static function set(string $character, int $value, bool $isBase = false): void
    {
        self::$rank[$character] = $value;

        if ($isBase)
        {
            self::$baseRank[$character] = $value;
        }
    }

    public static function add(string $character, int $value): void
    {
        self::$rank[$character] = self::get($character) + $value;
    }

    public static function reset(string $character): void
    {
        self::$rank[$character] = self::getBase($character);
    }
}