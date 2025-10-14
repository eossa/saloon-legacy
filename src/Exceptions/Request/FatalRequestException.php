<?php

namespace Saloon\Exceptions\Request;

use Exception;
use Saloon\Http\PendingRequest;
use Saloon\Exceptions\SaloonException;

/**
 * FatalRequestException
 *
 * This exception is thrown when the sender encountered a problem before the API
 * was able to respond. For example: An issue with connecting to the API or
 * an SSL error.
 *
 * @see https://docs.saloon.dev/the-basics/handling-failures
 */
class FatalRequestException extends SaloonException
{
    /**
     * The PendingRequest
     *
     * @var PendingRequest
     */
    protected $pendingSaloonRequest;

    /**
     * Constructor
     */
    public function __construct(Exception $originalException, PendingRequest $pendingRequest)
    {
        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);

        $this->pendingSaloonRequest = $pendingRequest;
    }

    /**
     * Get the PendingRequest that caused the exception.
     *
     * @return PendingRequest
     */
    public function getPendingRequest()
    {
        return $this->pendingSaloonRequest;
    }
}
