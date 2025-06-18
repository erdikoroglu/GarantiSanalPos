<?php

namespace W3\GarantiSanalPos\Tests;

use W3\GarantiSanalPos\Config;
use W3\GarantiSanalPos\Enum\Currency;
use W3\GarantiSanalPos\Enum\TransactionType;
use W3\GarantiSanalPos\GarantiPosClient;
use W3\GarantiSanalPos\Model\PaymentRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test case for GarantiPosClient
 */
class GarantiPosClientTest extends TestCase
{
    /**
     * Test configuration
     *
     * @var array
     */
    private array $testConfig = [
        'merchantId' => '123456789',
        'terminalId' => '12345678',
        'userId' => 'TESTUSER',
        'password' => 'TESTPASS',
        'mode' => 'TEST',
    ];

    /**
     * Test payment request
     *
     * @var array
     */
    private array $testPaymentData = [
        'orderId' => 'TEST123456',
        'amount' => 100.50,
        'cardNumber' => '4242424242424242',
        'cardExpireMonth' => '12',
        'cardExpireYear' => '25',
        'cardCvv' => '123',
        'cardHolderName' => 'John Doe',
    ];

    /**
     * Test creating a payment request
     */
    public function testCreatePaymentRequest()
    {
        $request = $this->createPaymentRequest();
        
        $this->assertEquals($this->testPaymentData['orderId'], $request->getOrderId());
        $this->assertEquals($this->testPaymentData['amount'], $request->getAmount());
        $this->assertEquals($this->testPaymentData['cardNumber'], $request->getCardNumber());
        $this->assertEquals($this->testPaymentData['cardExpireMonth'], $request->getCardExpireMonth());
        $this->assertEquals($this->testPaymentData['cardExpireYear'], $request->getCardExpireYear());
        $this->assertEquals($this->testPaymentData['cardCvv'], $request->getCardCvv());
        $this->assertEquals($this->testPaymentData['cardHolderName'], $request->getCardHolderName());
        $this->assertEquals(Currency::TRY, $request->getCurrency());
        $this->assertEquals(TransactionType::SALES, $request->getTransactionType());
        $this->assertEquals(0, $request->getInstallment());
        $this->assertTrue($request->validate());
    }

    /**
     * Test creating a client
     */
    public function testCreateClient()
    {
        $config = new Config($this->testConfig);
        $client = new GarantiPosClient($config);
        
        $this->assertInstanceOf(GarantiPosClient::class, $client);
    }

    /**
     * Test initiating a 3D Secure payment
     */
    public function testInitiate3DPayment()
    {
        $config = new Config($this->testConfig);
        $client = new GarantiPosClient($config);
        $request = $this->createPaymentRequest();
        
        $response = $client->initiate3DPayment($request, 'https://example.com/callback');
        
        $this->assertNotEmpty($response->getHtmlContent());
        $this->assertStringContainsString('<form id="3dform" method="post"', $response->getHtmlContent());
        $this->assertStringContainsString('name="cardnumber" value="' . $this->testPaymentData['cardNumber'] . '"', $response->getHtmlContent());
    }

    /**
     * Create a test payment request
     *
     * @return PaymentRequest
     */
    private function createPaymentRequest(): PaymentRequest
    {
        $request = new PaymentRequest();
        $request->setOrderId($this->testPaymentData['orderId'])
            ->setAmount($this->testPaymentData['amount'])
            ->setCurrency(Currency::TRY)
            ->setTransactionType(TransactionType::SALES)
            ->setCardNumber($this->testPaymentData['cardNumber'])
            ->setCardExpireMonth($this->testPaymentData['cardExpireMonth'])
            ->setCardExpireYear($this->testPaymentData['cardExpireYear'])
            ->setCardCvv($this->testPaymentData['cardCvv'])
            ->setCardHolderName($this->testPaymentData['cardHolderName'])
            ->setInstallment(0)
            ->setCustomerIp('127.0.0.1');
        
        return $request;
    }
}