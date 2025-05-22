<?php

namespace App\Helpers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{
    /**
     * Encrypt a value
     *
     * @param mixed $value
     * @return string|null
     */
    public static function encrypt($value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return Crypt::encryptString((string) $value);
    }
    
    /**
     * Decrypt a value
     *
     * @param string|null $encryptedValue
     * @return string|null
     */
    public static function decrypt(?string $encryptedValue): ?string
    {
        if ($encryptedValue === null) {
            return null;
        }
        
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (DecryptException $e) {
            report($e);
            return null;
        }
    }
    
    /**
     * Hash a value (one way encryption)
     *
     * @param string $value
     * @return string
     */
    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }
    
    /**
     * Check if a hashed value matches a plain value
     *
     * @param string $plainValue
     * @param string $hashedValue
     * @return bool
     */
    public static function hashEquals(string $plainValue, string $hashedValue): bool
    {
        return hash_equals($hashedValue, self::hash($plainValue));
    }
    
    /**
     * Mask a string by showing only first and last n characters
     * 
     * @param string $value
     * @param int $showFirst
     * @param int $showLast
     * @return string
     */
    public static function mask(string $value, int $showFirst = 2, int $showLast = 2): string
    {
        $length = mb_strlen($value);
        
        if ($length <= ($showFirst + $showLast)) {
            return str_repeat('*', $length);
        }
        
        $firstPart = mb_substr($value, 0, $showFirst);
        $lastPart = mb_substr($value, -$showLast, $showLast);
        $maskLength = $length - $showFirst - $showLast;
        
        return $firstPart . str_repeat('*', $maskLength) . $lastPart;
    }
}
