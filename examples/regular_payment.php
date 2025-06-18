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
    // Make payment without 3D Secure
    $response = $client->makePayment($request);
    
    if ($response->isSuccess()) {
        // Payment successful
        echo "<h1>Payment Successful</h1>";
        echo "<p>Transaction ID: " . $response->getTransactionId() . "</p>";
        echo "<p>Order ID: " . $response->getOrderId() . "</p>";
        echo "<p>Auth Code: " . $response->getAuthCode() . "</p>";
        
        // Here you would typically:
        // 1. Update your order status in the database
        // 2. Send confirmation email to customer
        // 3. Redirect to a thank you page
        
    } else {
        // Payment failed
        echo "<h1>Payment Failed</h1>";
        echo "<p>Error: " . $response->getErrorMessage() . "</p>";
        echo "<p>Error Code: " . $response->getErrorCode() . "</p>";
        
        // Here you would typically:
        // 1. Log the error
        // 2. Update your order status in the database
        // 3. Redirect to a payment failed page
    }
} catch (GarantiPosException $e) {
    // Exception occurred
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    if ($e->getErrorCode()) {
        echo "<p>Error Code: " . $e->getErrorCode() . "</p>";
    }
    
    // Here you would typically:
    // 1. Log the exception
    // 2. Notify administrators
    // 3. Show a user-friendly error page
}

// For debugging purposes, you might want to see the raw response data
echo "<h2>Debug Information</h2>";
echo "<pre>";
print_r($response->getRawData());
echo "</pre>";