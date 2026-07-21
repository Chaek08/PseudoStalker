<?php
namespace app\forms\classes;

class DimasCryptoZlodey
{
    const KEY = 'a7b3d8e5f6c9a1024d7e8b3f9c5d2a1e3f6b8c7d4e9f0213a5d7c6e8b9f0d2c4';
    const IV = '3f7a8c9d4e1b2f6d8a0c5e7f9b3d4a1';

    public static function encryptData($data)
    {
        $key = self::KEY;
        $iv = self::IV;

        $keyLength = strlen($key);
        $ivLength = strlen($iv);
        $len = strlen($data);

        $encrypted = '';

        for ($i = 0; $i < $len; $i++) 
        {
            $byte = ord($data[$i]);
            $keyByte = ord($key[$i % $keyLength]);
            $ivByte = ord($iv[$i % $ivLength]);

            $xorByte = ($byte ^ $keyByte ^ $ivByte) & 0xFF;
            $encrypted .= chr($xorByte);
        }

        return base64_encode($encrypted);
    }

    public static function decryptData($data)
    {
        $key = self::KEY;
        $iv = self::IV;

        $keyLength = strlen($key);
        $ivLength = strlen($iv);
        $data = base64_decode($data);
        $len = strlen($data);

        $decrypted = '';

        for ($i = 0; $i < $len; $i++)
        {
            $byte = ord($data[$i]);
            $keyByte = ord($key[$i % $keyLength]);
            $ivByte = ord($iv[$i % $ivLength]);

            $xorByte = ($byte ^ $keyByte ^ $ivByte) & 0xFF;
            $decrypted .= chr($xorByte);
        }

        return $decrypted;
    }
}






