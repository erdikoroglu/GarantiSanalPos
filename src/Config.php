<?php

namespace W3\GarantiSanalPos;

/**
 * Configuration class for Garanti Bank Virtual POS integration
 */
class Config
{
    /**
     * @var string Merchant ID provided by Garanti Bank
     */
    private string $merchantId;
    private bool $recurring = false;


    private bool $debugMode = false;
    private const DEBUG_API_URL = "https://garantibbvapos.com.tr/destek/postback.aspx";
    private string $callbackUrl;

    /**
     * @var string Terminal ID provided by Garanti Bank
     */
    private string $terminalId;

    /**
     * @var string User ID provided by Garanti Bank
     */
    private string $userId;

    /**
     * @var string Password provided by Garanti Bank
     */
    private string $password;

    /**
     * @var string Mode of operation (TEST or PROD)
     */
    private string $mode;

    private string $storeKey;

    /**
     * @var string API endpoint for TEST mode
     */
    private const TEST_API_URL = 'https://sanalposprovtest.garantibbva.com.tr/VPServlet';

    /**
     * @var string API endpoint for PROD mode
     */
    private const PROD_API_URL = 'https://sanalposprov.garantibbva.com.tr/VPServlet';

    /**
     * @var string 3D Secure API endpoint for TEST mode
     */
    private const TEST_3D_API_URL = 'https://sanalposprovtest.garantibbva.com.tr/servlet/gt3dengine';

    /**
     * @var string 3D Secure API endpoint for PROD mode
     */
    private const PROD_3D_API_URL = 'https://sanalposprov.garantibbva.com.tr/servlet/gt3dengine';

    /**
     * Config constructor.
     *
     * @param array $config Configuration parameters
     */
    public function __construct(array $config)
    {
        $this->merchantId = $config['merchantId'] ?? env('GARANTI_MERCHANT_ID');
        $this->terminalId = $config['terminalId'] ?? env('GARANTI_TERMINAL_ID');
        $this->userId = $config['userId'] ?? env('GARANTI_USER_ID');
        $this->password = $config['password'] ?? env('GARANTI_USER_PASSWORD');
        $this->mode = $config['mode'] ?? env('GARANTI_MODE');
        $this->debugMode = $config['debugMode'] ?? env('GARANTI_DEBUG_MODE');
        $this->storeKey = $config['storeKey'] ?? env('GARANTI_STORE_KEY');
        $this->callbackUrl = $config['callbackUrl'] ?? env('GARANTI_CALLBACK_URL');
        $this->recurring = $config['recurring'] ?? false;

    }

    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * Get Merchant ID
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * Get Terminal ID
     *
     * @return string
     */
    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    /**
     * Get User ID
     *
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get API URL based on mode
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        if ($this->debugMode) {
            return self::DEBUG_API_URL;
        }
        return $this->mode === 'PROD' ? self::PROD_API_URL : self::TEST_API_URL;
    }

    public function getStoreKey()
    {
        return $this->storeKey;
    }

    public function getDebugApiUrl()
    {
        return self::DEBUG_API_URL;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }
    /**
     * Get 3D Secure API URL based on mode
     *
     * @return string
     */
    public function get3DApiUrl(): string
    {
        if ($this->debugMode) {
            return self::DEBUG_API_URL;
        }
        return $this->mode === 'PROD' ? self::PROD_3D_API_URL : self::TEST_3D_API_URL;
    }

    /**
     * Check if mode is TEST
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->mode === 'TEST';
    }
}