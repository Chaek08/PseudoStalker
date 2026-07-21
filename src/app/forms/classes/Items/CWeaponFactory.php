<?php
namespace app\forms\classes\Items;

final class CWeaponFactory
{
    public static function create(string $type, $owner): ?CWeapon
    {
        switch ($type)
        {
            case 'wpn_pm':   return new CWeapon_PM($owner);
            case 'wpn_ak74': return new CWeapon_AK74($owner);
            default:     return null;
        }
    }
}