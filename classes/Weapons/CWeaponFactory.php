<?php
namespace app\forms\classes\Weapons;

final class CWeaponFactory
{
    public static function create(string $type, $owner): ?CWeapon
    {
        switch ($type)
        {
            case 'Pm':   return new CWeapon_PM($owner);
            case 'AK74': return new CWeapon_AK74($owner);
            default:     return null;
        }
    }
}