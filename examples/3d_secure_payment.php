<?php

require_once __DIR__ . '/../vendor/autoload.php';

use W3\GarantiSanalPos\Config;
use W3\GarantiSanalPos\Enum\Currency;
use W3\GarantiSanalPos\Enum\TransactionType;
use W3\GarantiSanalPos\GarantiPosClient;
use W3\GarantiSanalPos\Model\PaymentRequest;
use W3\GarantiSanalPos\Exception\GarantiPosException;

// Create configuration
$config = new Config([
    'merchantId' => 'YOUR_MERCHANT_ID',
    'terminalId' => 'YOUR_TERMINAL_ID',
    'userId' => 'YOUR_USER_ID',
    'password' => 'YOUR_PASSWORD',
    'mode' => 'TEST', // Use 'PROD' for production
]);

// Create client
$client = new GarantiPosClient($config);

// Create a unique order ID
$orderId = 'ORDER_' . time();

// Create payment request
$request = new PaymentRequest();
$request->setOrderId($orderId)
    ->setAmount(100.50) // TL cinsinden
    ->setCurrency(Currency::TRY)
    ->setCardNumber('4242424242424242')
    ->setCardExpireMonth('12')
    ->setCardExpireYear('2025')
    ->setCardCvv('123')
    ->setCardHolderName('John Doe')
    ->setInstallment(0) // Tek çekim
    ->setCustomerIp($_SERVER['REMOTE_ADDR']);

try {
    // Initiate 3D Secure payment
    $response = $client->initiate3DPayment($request, 'https://example.com/callback.php');
    
    // Output HTML form for 3D Secure redirect
    echo $response->getHtmlContent();
} catch (GarantiPosException $e) {
    echo "Error: " . $e->getMessage();
    if ($e->getErrorCode()) {
        echo " (Code: " . $e->getErrorCode() . ")";
    }
}