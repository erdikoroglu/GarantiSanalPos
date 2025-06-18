<?php

namespace W3\GarantiSanalPos;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use W3\GarantiSanalPos\Enum\Currency;
use W3\GarantiSanalPos\Enum\TransactionType;
use W3\GarantiSanalPos\Exception\GarantiPosException;
use W3\GarantiSanalPos\Model\PaymentRequest;
use W3\GarantiSanalPos\Model\PaymentResponse;

/**
 * Client class for Garanti Bank Virtual POS integration
 */
class GarantiPosClient
{
    /**
     * @var Config Configuration
     */
    private Config $config;

    /**
     * @var Client HTTP client
     */
    private Client $httpClient;

    /**
     * GarantiPosClient constructor.
     *
     * @param Config $config Configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => true,
        ]);
    }

    /**
     * Make a payment without 3D Secure
     *
     * @param PaymentRequest $request Payment request
     * @return PaymentResponse
     * @throws GarantiPosException
     */
    public function makePayment(PaymentRequest $request): PaymentResponse
    {
        if (!$request->validate()) {
            throw new GarantiPosException('Invalid payment request');
        }

        $xmlData = $this->preparePaymentXml($request);
        
        try {
            $response = $this->httpClient->post($this->config->getApiUrl(), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'data' => $xmlData,
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = $this->parseXmlResponse($responseBody);
            
            return new PaymentResponse($responseData);
        } catch (GuzzleException $e) {
            throw new GarantiPosException('Payment request failed: ' . $e->getMessage(), '', 0, $e);
        }
    }

    /**
     * Initiate 3D Secure payment
     *
     * @param PaymentRequest $request Payment request
     * @param string $callbackUrl Callback URL for 3D Secure
     * @return PaymentResponse
     * @throws GarantiPosException
     */
    public function initiate3DPayment(PaymentRequest $request, string $callbackUrl): PaymentResponse
    {
        if (!$request->validate()) {
            throw new GarantiPosException('Invalid payment request');
        }

        if (empty($callbackUrl)) {
            throw new GarantiPosException('Callback URL is required for 3D Secure payment');
        }

        $secureData = $this->prepare3DSecureData($request, $callbackUrl);
        
        $response = new PaymentResponse();
        $response->setHtmlContent($this->generate3DSecureForm($secureData));
        
        return $response;
    }

    /**
     * Complete 3D Secure payment
     *
     * @param array $postData POST data from 3D Secure callback
     * @return PaymentResponse
     * @throws GarantiPosException
     */
    public function complete3DPayment(array $postData): PaymentResponse
    {
        if (empty($postData['md']) || empty($postData['xid']) || empty($postData['eci']) || empty($postData['cavv'])) {
            throw new GarantiPosException('Invalid 3D Secure callback data');
        }

        // Check 3D authentication status
        $mdStatus = $postData['mdStatus'] ?? '';
        if (!in_array($mdStatus, ['1', '2', '3', '4'])) {
            return $this->createErrorResponse('3D authentication failed', $mdStatus);
        }

        // Create payment request from 3D data
        $request = new PaymentRequest();
        $request->setOrderId($postData['oid'] ?? '')
            ->setTransactionType(TransactionType::SALES);

        $xmlData = $this->prepare3DPaymentXml($request, $postData);
        
        try {
            $response = $this->httpClient->post($this->config->getApiUrl(), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'data' => $xmlData,
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = $this->parseXmlResponse($responseBody);
            
            return new PaymentResponse($responseData);
        } catch (GuzzleException $e) {
            throw new GarantiPosException('3D Secure payment completion failed: ' . $e->getMessage(), '', 0, $e);
        }
    }

    /**
     * Prepare XML for payment request
     *
     * @param PaymentRequest $request Payment request
     * @return string XML data
     */
    private function preparePaymentXml(PaymentRequest $request): string
    {
        $terminalId = $this->config->getTerminalId();
        $hash = $this->generateSecurityHash($request->getOrderId(),$terminalId,$request->getCardNumber(),$request->getAmount(),$request->getCurrency());
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<GVPSRequest>' . PHP_EOL;
        
        // Add mode
        $xml .= '  <Mode>' . ($this->config->isTestMode() ? 'TEST' : 'PROD') . '</Mode>' . PHP_EOL;
        
        // Add version
        $xml .= '  <Version>v0.01</Version>' . PHP_EOL;
        
        // Add terminal info
        $xml .= '  <Terminal>' . PHP_EOL;
        $xml .= '    <ProvUserID>' . $this->config->getUserId() . '</ProvUserID>' . PHP_EOL;
        $xml .= '    <HashData>' . $hash . '</HashData>' . PHP_EOL;
        $xml .= '    <UserID>' . $this->config->getUserId() . '</UserID>' . PHP_EOL;
        $xml .= '    <ID>' . $terminalId . '</ID>' . PHP_EOL;
        $xml .= '    <MerchantID>' . $this->config->getMerchantId() . '</MerchantID>' . PHP_EOL;
        $xml .= '  </Terminal>' . PHP_EOL;
        
        // Add customer info
        $xml .= '  <Customer>' . PHP_EOL;
        $xml .= '    <IPAddress>' . $request->getCustomerIp() . '</IPAddress>' . PHP_EOL;
        $xml .= '    <EmailAddress></EmailAddress>' . PHP_EOL;
        $xml .= '  </Customer>' . PHP_EOL;
        
        // Add card info
        $xml .= '  <Card>' . PHP_EOL;
        $xml .= '    <Number>' . $request->getCardNumber() . '</Number>' . PHP_EOL;
        $xml .= '    <ExpireDate>' . $request->getCardExpireDate() . '</ExpireDate>' . PHP_EOL;
        $xml .= '    <CVV2>' . $request->getCardCvv() . '</CVV2>' . PHP_EOL;
        $xml .= '  </Card>' . PHP_EOL;
        
        // Add order info
        $xml .= '  <Order>' . PHP_EOL;
        $xml .= '    <OrderID>' . $request->getOrderId() . '</OrderID>' . PHP_EOL;
        $xml .= '    <GroupID></GroupID>' . PHP_EOL;
        $xml .= '    <Description></Description>' . PHP_EOL;
        $xml .= '  </Order>' . PHP_EOL;
        
        // Add transaction info
        $xml .= '  <Transaction>' . PHP_EOL;
        $xml .= '    <Type>' . TransactionType::getApiCode($request->getTransactionType()) . '</Type>' . PHP_EOL;
        $xml .= '    <InstallmentCnt>' . $request->getInstallment() . '</InstallmentCnt>' . PHP_EOL;
        $xml .= '    <Amount>' . $request->getFormattedAmount() . '</Amount>' . PHP_EOL;
        $xml .= '    <CurrencyCode>' . $request->getCurrency() . '</CurrencyCode>' . PHP_EOL;
        $xml .= '    <CardholderPresentCode>0</CardholderPresentCode>' . PHP_EOL;
        $xml .= '    <MotoInd>N</MotoInd>' . PHP_EOL;
        $xml .= '  </Transaction>' . PHP_EOL;
        
        $xml .= '</GVPSRequest>';
        
        return $xml;
    }

    /**
     * Prepare data for 3D Secure payment
     *
     * @param PaymentRequest $request Payment request
     * @param string $callbackUrl Callback URL for 3D Secure
     * @return array 3D Secure data
     */
    private function prepare3DSecureData(PaymentRequest $request, string $callbackUrl): array
    {
        $terminalId = $this->config->getTerminalId();
        $securityData = $this->generateSecurityHash($request->getOrderId(),$terminalId,$request->getCardNumber(),$request->getAmount(),$request->getCurrency());
        
        return [
            'mode' => $this->config->isTestMode() ? 'TEST' : 'PROD',
            'secure3dsecuritylevel' => '3D_PAY',
            'apiversion' => 'v0.01',
            'terminalprovuserid' => $this->config->getUserId(),
            'terminaluserid' => $this->config->getUserId(),
            'terminalmerchantid' => $this->config->getMerchantId(),
            'terminalid' => $terminalId,
            'txntype' => TransactionType::getApiCode($request->getTransactionType()),
            'txnamount' => $request->getFormattedAmount(),
            'txncurrencycode' => $request->getCurrency(),
            'txninstallmentcount' => $request->getInstallment(),
            'orderid' => $request->getOrderId(),
            'successurl' => $callbackUrl,
            'errorurl' => $callbackUrl,
            'customeremailaddress' => '',
            'customeripaddress' => $request->getCustomerIp(),
            'secure3dhash' => $securityData,
            'cardnumber' => $request->getCardNumber(),
            'cardexpiredatemonth' => $request->getCardExpireMonth(),
            'cardexpiredateyear' => $request->getCardExpireYear(),
            'cardcvv2' => $request->getCardCvv(),
        ];
    }

    /**
     * Generate HTML form for 3D Secure payment
     *
     * @param array $data 3D Secure data
     * @return string HTML form
     */
    private function generate3DSecureForm(array $data): string
    {
        $html = '<html>' . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '  <title>3D Secure Payment</title>' . PHP_EOL;
        $html .= '</head>' . PHP_EOL;
        $html .= '<body onload="document.getElementById(\'3dform\').submit();">' . PHP_EOL;
        $html .= '  <form id="3dform" method="post" action="' . $this->config->get3DApiUrl() . '">' . PHP_EOL;
        
        foreach ($data as $key => $value) {
            $html .= '    <input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">' . PHP_EOL;
        }
        
        $html .= '    <noscript>' . PHP_EOL;
        $html .= '      <center>Please click the button below to continue:<br><br>' . PHP_EOL;
        $html .= '      <input type="submit" value="Continue">' . PHP_EOL;
        $html .= '    </noscript>' . PHP_EOL;
        $html .= '  </form>' . PHP_EOL;
        $html .= '  <center>Please wait while you are redirected to the 3D Secure page...</center>' . PHP_EOL;
        $html .= '</body>' . PHP_EOL;
        $html .= '</html>';
        
        return $html;
    }

    /**
     * Prepare XML for 3D Secure payment completion
     *
     * @param PaymentRequest $request Payment request
     * @param array $postData POST data from 3D Secure callback
     * @return string XML data
     */
    private function prepare3DPaymentXml(PaymentRequest $request, array $postData): string
    {
        $terminalId = $this->config->getTerminalId();
        $hash = $this->generateSecurityHash($request->getOrderId(),$terminalId,$request->getCardNumber(),$request->getAmount(),$request->getCurrency());
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<GVPSRequest>' . PHP_EOL;
        
        // Add mode
        $xml .= '  <Mode>' . ($this->config->isTestMode() ? 'TEST' : 'PROD') . '</Mode>' . PHP_EOL;
        
        // Add version
        $xml .= '  <Version>v0.01</Version>' . PHP_EOL;
        
        // Add terminal info
        $xml .= '  <Terminal>' . PHP_EOL;
        $xml .= '    <ProvUserID>' . $this->config->getUserId() . '</ProvUserID>' . PHP_EOL;
        $xml .= '    <HashData>' . $hash . '</HashData>' . PHP_EOL;
        $xml .= '    <UserID>' . $this->config->getUserId() . '</UserID>' . PHP_EOL;
        $xml .= '    <ID>' . $terminalId . '</ID>' . PHP_EOL;
        $xml .= '    <MerchantID>' . $this->config->getMerchantId() . '</MerchantID>' . PHP_EOL;
        $xml .= '  </Terminal>' . PHP_EOL;
        
        // Add customer info
        $xml .= '  <Customer>' . PHP_EOL;
        $xml .= '    <IPAddress>' . ($postData['customeripaddress'] ?? '') . '</IPAddress>' . PHP_EOL;
        $xml .= '    <EmailAddress>' . ($postData['customeremailaddress'] ?? '') . '</EmailAddress>' . PHP_EOL;
        $xml .= '  </Customer>' . PHP_EOL;
        
        // Add order info
        $xml .= '  <Order>' . PHP_EOL;
        $xml .= '    <OrderID>' . $request->getOrderId() . '</OrderID>' . PHP_EOL;
        $xml .= '    <GroupID></GroupID>' . PHP_EOL;
        $xml .= '    <Description></Description>' . PHP_EOL;
        $xml .= '  </Order>' . PHP_EOL;
        
        // Add transaction info
        $xml .= '  <Transaction>' . PHP_EOL;
        $xml .= '    <Type>' . TransactionType::getApiCode($request->getTransactionType()) . '</Type>' . PHP_EOL;
        $xml .= '    <InstallmentCnt>' . ($postData['txninstallmentcount'] ?? '0') . '</InstallmentCnt>' . PHP_EOL;
        $xml .= '    <Amount>' . ($postData['txnamount'] ?? '0') . '</Amount>' . PHP_EOL;
        $xml .= '    <CurrencyCode>' . ($postData['txncurrencycode'] ?? Currency::TRY) . '</CurrencyCode>' . PHP_EOL;
        $xml .= '    <CardholderPresentCode>13</CardholderPresentCode>' . PHP_EOL;
        $xml .= '    <MotoInd>N</MotoInd>' . PHP_EOL;
        
        // Add 3D Secure info
        $xml .= '    <Secure3D>' . PHP_EOL;
        $xml .= '      <AuthenticationCode>' . ($postData['cavv'] ?? '') . '</AuthenticationCode>' . PHP_EOL;
        $xml .= '      <SecurityLevel>' . ($postData['eci'] ?? '') . '</SecurityLevel>' . PHP_EOL;
        $xml .= '      <TxnID>' . ($postData['xid'] ?? '') . '</TxnID>' . PHP_EOL;
        $xml .= '      <Md>' . ($postData['md'] ?? '') . '</Md>' . PHP_EOL;
        $xml .= '    </Secure3D>' . PHP_EOL;
        
        $xml .= '  </Transaction>' . PHP_EOL;
        
        $xml .= '</GVPSRequest>';
        
        return $xml;
    }

    /**
     * Parse XML response from Garanti Bank
     *
     * @param string $xml XML response
     * @return array Parsed response data
     * @throws GarantiPosException
     */
    private function parseXmlResponse(string $xml): array
    {
        libxml_use_internal_errors(true);
        
        $doc = simplexml_load_string($xml);
        
        if ($doc === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            $errorMessage = 'Failed to parse XML response';
            if (!empty($errors)) {
                $errorMessage .= ': ' . $errors[0]->message;
            }
            
            throw new GarantiPosException($errorMessage);
        }
        
        $response = [];
        
        // Extract transaction response
        if (isset($doc->Transaction->Response)) {
            $response['ResponseCode'] = (string)$doc->Transaction->Response->Code;
            $response['ResponseMessage'] = (string)$doc->Transaction->Response->Message;
            $response['ErrorMsg'] = (string)$doc->Transaction->Response->ErrorMsg;
            $response['SysErrMsg'] = (string)$doc->Transaction->Response->SysErrMsg;
        }
        
        // Extract transaction details
        if (isset($doc->Transaction->RetrefNum)) {
            $response['TransactionId'] = (string)$doc->Transaction->RetrefNum;
        }
        
        // Extract order details
        if (isset($doc->Order->OrderID)) {
            $response['OrderId'] = (string)$doc->Order->OrderID;
        }
        
        // Extract authorization details
        if (isset($doc->Transaction->AuthCode)) {
            $response['AuthCode'] = (string)$doc->Transaction->AuthCode;
        }
        
        // Extract host reference number
        if (isset($doc->Transaction->HostRefNum)) {
            $response['HostRefNum'] = (string)$doc->Transaction->HostRefNum;
        }
        
        return $response;
    }

    /**
     * Generate security hash for Garanti Bank
     *
     * @param string $orderId Order ID
     * @param string $terminalId Terminal ID
     * @return string Security hash
     */
    private function generateSecurityHash(string $orderId, string $terminalId,string $cardNumber, string $amount,string $currencyCode): string
    {
        $securityData = strtoupper(sha1($this->config->getPassword() . str_pad($terminalId, 9, '0', STR_PAD_LEFT)));

        //$hash = strtoupper(sha1($orderId . $terminalId . $securityData));
        $data = [
            $orderId,$terminalId,$cardNumber,$amount,$currencyCode,$securityData
        ];

        $hash = strtoupper(hash('sha512', implode('', $data)));
        
        return $hash;
    }

    /**
     * Create error response
     *
     * @param string $message Error message
     * @param string $code Error code
     * @return PaymentResponse
     */
    private function createErrorResponse(string $message, string $code = ''): PaymentResponse
    {
        $response = new PaymentResponse();
        $response->setSuccess(false)
            ->setErrorCode($code)
            ->setErrorMessage($message)
            ->setResponseCode($code)
            ->setResponseMessage($message);
        
        return $response;
    }
}