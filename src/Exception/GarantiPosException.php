<?php

namespace W3\GarantiSanalPos\Exception;

/**
 * Exception class for Garanti Bank Virtual POS errors
 */
class GarantiPosException extends \Exception
{
    /**
     * @var string Error code from Garanti Bank
     */
    private string $errorCode;

    /**
     * GarantiPosException constructor.
     *
     * @param string $message Error message
     * @param string $errorCode Error code
     * @param int $code Exception code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = "", string $errorCode = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * Get error code from Garanti Bank
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}