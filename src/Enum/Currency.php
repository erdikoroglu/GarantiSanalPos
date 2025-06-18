<?php

namespace W3\GarantiSanalPos\Enum;

/**
 * Currency codes for Garanti Bank Virtual POS
 */
class Currency
{
    /**
     * Turkish Lira
     */
    public const TRY = 949;

    /**
     * US Dollar
     */
    public const USD = 840;

    /**
     * Euro
     */
    public const EUR = 978;

    /**
     * British Pound
     */
    public const GBP = 826;

    /**
     * Japanese Yen
     */
    public const JPY = 392;

    /**
     * Russian Ruble
     */
    public const RUB = 643;

    /**
     * Get currency code name by value
     *
     * @param int $value Currency code value
     * @return string|null Currency name or null if not found
     */
    public static function getName(int $value): ?string
    {
        $reflection = new \ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        
        foreach ($constants as $name => $code) {
            if ($code === $value) {
                return $name;
            }
        }
        
        return null;
    }

    /**
     * Check if currency code is valid
     *
     * @param int $value Currency code value
     * @return bool
     */
    public static function isValid(int $value): bool
    {
        $reflection = new \ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        
        return in_array($value, $constants, true);
    }
}