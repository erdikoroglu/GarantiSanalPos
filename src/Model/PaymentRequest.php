<?php

namespace W3\GarantiSanalPos\Model;

use W3\GarantiSanalPos\Enum\Currency;
use W3\GarantiSanalPos\Enum\TransactionType;

/**
 * Payment request model for Garanti Bank Virtual POS
 */
class PaymentRequest
{
    /**
     * @var string Order ID
     */
    private string $orderId = '';

    /**
     * @var float Payment amount
     */
    private float $amount = 0.0;

    /**
     * @var int Currency code (see Currency enum)
     */
    private int $currency = Currency::TRY;

    /**
     * @var string Transaction type (see TransactionType enum)
     */
    private string $transactionType = TransactionType::SALES;

    /**
     * @var string Card number
     */
    private string $cardNumber = '';

    /**
     * @var string Card expiration month (MM)
     */
    private string $cardExpireMonth = '';

    /**
     * @var string Card expiration year (YY)
     */
    private string $cardExpireYear = '';

    /**
     * @var string Card CVV/CVC code
     */
    private string $cardCvv = '';

    /**
     * @var string Card holder name
     */
    private string $cardHolderName = '';

    /**
     * @var int|null Installment count (0 for single payment)
     */
    private $installment = null;

    /**
     * @var string Customer IP address
     */
    private string $customerIp = '';

    /**
     * @var array Additional parameters
     */
    private array $additionalParams = [];

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
     * Get order ID
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Set payment amount
     *
     * @param float $amount Payment amount
     * @return $this
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get payment amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get payment amount formatted for API (multiplied by 100)
     *
     * @return string
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount * 100, 0, '', '');
    }

    /**
     * Set currency code
     *
     * @param int $currency Currency code (see Currency enum)
     * @return $this
     * @throws \InvalidArgumentException If currency code is invalid
     */
    public function setCurrency(int $currency): self
    {
        if (!Currency::isValid($currency)) {
            throw new \InvalidArgumentException('Invalid currency code');
        }
        
        $this->currency = $currency;
        return $this;
    }

    /**
     * Get currency code
     *
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * Set transaction type
     *
     * @param string $transactionType Transaction type (see TransactionType enum)
     * @return $this
     * @throws \InvalidArgumentException If transaction type is invalid
     */
    public function setTransactionType(string $transactionType): self
    {
        if (!TransactionType::isValid($transactionType)) {
            throw new \InvalidArgumentException('Invalid transaction type');
        }
        
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * Get transaction type
     *
     * @return string
     */
    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    /**
     * Set card number
     *
     * @param string $cardNumber Card number
     * @return $this
     */
    public function setCardNumber(string $cardNumber): self
    {
        $this->cardNumber = preg_replace('/\D/', '', $cardNumber);
        return $this;
    }

    /**
     * Get card number
     *
     * @return string
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * Set card expiration month
     *
     * @param string $cardExpireMonth Card expiration month (MM)
     * @return $this
     */
    public function setCardExpireMonth(string $cardExpireMonth): self
    {
        $this->cardExpireMonth = str_pad($cardExpireMonth, 2, '0', STR_PAD_LEFT);
        return $this;
    }

    /**
     * Get card expiration month
     *
     * @return string
     */
    public function getCardExpireMonth(): string
    {
        return $this->cardExpireMonth;
    }

    /**
     * Set card expiration year
     *
     * @param string $cardExpireYear Card expiration year (YY or YYYY)
     * @return $this
     */
    public function setCardExpireYear(string $cardExpireYear): self
    {
        // If year is provided as YYYY, convert to YY
        if (strlen($cardExpireYear) === 4) {
            $cardExpireYear = substr($cardExpireYear, 2, 2);
        }
        
        $this->cardExpireYear = $cardExpireYear;
        return $this;
    }

    /**
     * Get card expiration year
     *
     * @return string
     */
    public function getCardExpireYear(): string
    {
        return $this->cardExpireYear;
    }

    /**
     * Get card expiration date in MMYY format
     *
     * @return string
     */
    public function getCardExpireDate(): string
    {
        return $this->cardExpireMonth . $this->cardExpireYear;
    }

    /**
     * Set card CVV/CVC code
     *
     * @param string $cardCvv Card CVV/CVC code
     * @return $this
     */
    public function setCardCvv(string $cardCvv): self
    {
        $this->cardCvv = $cardCvv;
        return $this;
    }

    /**
     * Get card CVV/CVC code
     *
     * @return string
     */
    public function getCardCvv(): string
    {
        return $this->cardCvv;
    }

    /**
     * Set card holder name
     *
     * @param string $cardHolderName Card holder name
     * @return $this
     */
    public function setCardHolderName(string $cardHolderName): self
    {
        $this->cardHolderName = $cardHolderName;
        return $this;
    }

    /**
     * Get card holder name
     *
     * @return string
     */
    public function getCardHolderName(): string
    {
        return $this->cardHolderName;
    }

    /**
     * Set installment count
     *
     * @param int|null $installment Installment count (0 for single payment)
     * @return $this
     */
    public function setInstallment(?int $installment): self
    {
        $this->installment = $installment;
        return $this;
    }

    /**
     * Get installment count
     *
     * @return int
     */
    public function getInstallment(): ?int
    {
        return $this->installment;
    }

    /**
     * Set customer IP address
     *
     * @param string $customerIp Customer IP address
     * @return $this
     */
    public function setCustomerIp(string $customerIp): self
    {
        $this->customerIp = $customerIp;
        return $this;
    }

    /**
     * Get customer IP address
     *
     * @return string
     */
    public function getCustomerIp(): string
    {
        return $this->customerIp;
    }

    /**
     * Set additional parameter
     *
     * @param string $key Parameter key
     * @param mixed $value Parameter value
     * @return $this
     */
    public function setAdditionalParam(string $key, $value): self
    {
        $this->additionalParams[$key] = $value;
        return $this;
    }

    /**
     * Get additional parameter
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed
     */
    public function getAdditionalParam(string $key, $default = null)
    {
        return $this->additionalParams[$key] ?? $default;
    }

    /**
     * Get all additional parameters
     *
     * @return array
     */
    public function getAdditionalParams(): array
    {
        return $this->additionalParams;
    }

    /**
     * Validate payment request
     *
     * @return bool
     */
    public function validate(): bool
    {
        return !empty($this->orderId)
            && $this->amount > 0
            && Currency::isValid($this->currency)
            && TransactionType::isValid($this->transactionType)
            && !empty($this->cardNumber)
            && !empty($this->cardExpireMonth)
            && !empty($this->cardExpireYear)
            && !empty($this->cardCvv);
    }
}