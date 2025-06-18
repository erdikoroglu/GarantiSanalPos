<?php

namespace W3\GarantiSanalPos\Enum;

/**
 * Transaction types for Garanti Bank Virtual POS
 */
class TransactionType
{
    /**
     * Sales transaction
     */
    public const SALES = 'sales';

    /**
     * Void transaction (cancel)
     */
    public const VOID = 'void';

    /**
     * Refund transaction
     */
    public const REFUND = 'refund';

    /**
     * Pre-authorization transaction
     */
    public const PREAUTH = 'preauth';

    /**
     * Post-authorization transaction
     */
    public const POSTAUTH = 'postauth';

    /**
     * Installment sales transaction
     */
    public const INSTALLMENT_SALES = 'installmentsales';

    /**
     * Get transaction type code for API
     *
     * @param string $type Transaction type
     * @return string API transaction type code
     */
    public static function getApiCode(string $type): string
    {
        $codes = [
            self::SALES => 'sales',
            self::VOID => 'void',
            self::REFUND => 'refund',
            self::PREAUTH => 'preauth',
            self::POSTAUTH => 'postauth',
            self::INSTALLMENT_SALES => 'sales',
        ];

        return $codes[$type] ?? 'sales';
    }

    /**
     * Check if transaction type is valid
     *
     * @param string $type Transaction type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        $reflection = new \ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        
        return in_array($type, $constants, true);
    }
}