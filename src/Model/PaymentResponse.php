<?php

namespace W3\GarantiSanalPos\Model;

/**
 * Payment response model for Garanti Bank Virtual POS
 */
class PaymentResponse
{
    /**
     * @var bool Success status
     */
    private bool $success = false;

    /**
     * @var string Error code
     */
    private string $errorCode = '';

    /**
     * @var string Error message
     */
    private string $errorMessage = '';

    /**
     * @var string Transaction ID
     */
    private string $transactionId = '';

    /**
     * @var string Order ID
     */
    private string $orderId = '';

    /**
     * @var string Response code
     */
    private string $responseCode = '';

    /**
     * @var string Response message
     */
    private string $responseMessage = '';

    /**
     * @var string Authorization code
     */
    private string $authCode = '';

    /**
     * @var string Host reference number
     */
    private string $hostRefNum = '';

    /**
     * @var string HTML content for 3D Secure
     */
    private string $htmlContent = '';

    /**
     * @var array Raw response data
     */
    private array $rawData = [];

    /**
     * PaymentResponse constructor.
     *
     * @param array $data Response data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->parseResponse($data);
        }
    }

    /**
     * Parse response data
     *
     * @param array $data Response data
     * @return void
     */
    public function parseResponse(array $data): void
    {
        $this->rawData = $data;

        // Store original response data
        $this->responseCode = $data['ResponseCode'] ?? '';
        $this->responseMessage = $data['ResponseMessage'] ?? '';
        $this->transactionId = $data['TransactionId'] ?? '';
        $this->orderId = $data['OrderId'] ?? '';
        $this->authCode = $data['AuthCode'] ?? '';
        $this->hostRefNum = $data['HostRefNum'] ?? '';
        $this->htmlContent = $data['HtmlContent'] ?? '';

        // Determine success based on response code
        // Garanti Bank uses "00" as success code
        $this->success = ($this->responseCode === '00');

        if (!$this->success) {
            $this->errorCode = $this->responseCode;
            $this->errorMessage = $this->responseMessage;
        }
    }

    /**
     * Set HTML content for 3D Secure
     *
     * @param string $htmlContent HTML content
     * @return $this
     */
    public function setHtmlContent(string $htmlContent): self
    {
        $this->htmlContent = $htmlContent;
        return $this;
    }

    /**
     * Get HTML content for 3D Secure
     *
     * @return string
     */
    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    /**
     * Check if response is successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set success status
     *
     * @param bool $success Success status
     * @return $this
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Get error code
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Set error code
     *
     * @param string $errorCode Error code
     * @return $this
     */
    public function setErrorCode(string $errorCode): self
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Set error message
     *
     * @param string $errorMessage Error message
     * @return $this
     */
    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Get transaction ID
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Set transaction ID
     *
     * @param string $transactionId Transaction ID
     * @return $this
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Get order ID
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Set order ID
     *
     * @param string $orderId Order ID
     * @return $this
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode(): string
    {
        return $this->responseCode;
    }

    /**
     * Set response code
     *
     * @param string $responseCode Response code
     * @return $this
     */
    public function setResponseCode(string $responseCode): self
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * Get response message
     *
     * @return string
     */
    public function getResponseMessage(): string
    {
        return $this->responseMessage;
    }

    /**
     * Set response message
     *
     * @param string $responseMessage Response message
     * @return $this
     */
    public function setResponseMessage(string $responseMessage): self
    {
        $this->responseMessage = $responseMessage;
        return $this;
    }

    /**
     * Get authorization code
     *
     * @return string
     */
    public function getAuthCode(): string
    {
        return $this->authCode;
    }

    /**
     * Set authorization code
     *
     * @param string $authCode Authorization code
     * @return $this
     */
    public function setAuthCode(string $authCode): self
    {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * Get host reference number
     *
     * @return string
     */
    public function getHostRefNum(): string
    {
        return $this->hostRefNum;
    }

    /**
     * Set host reference number
     *
     * @param string $hostRefNum Host reference number
     * @return $this
     */
    public function setHostRefNum(string $hostRefNum): self
    {
        $this->hostRefNum = $hostRefNum;
        return $this;
    }

    /**
     * Get raw response data
     *
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * Get specific raw data value
     *
     * @param string $key Data key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getRawDataValue(string $key, $default = null)
    {
        return $this->rawData[$key] ?? $default;
    }
}