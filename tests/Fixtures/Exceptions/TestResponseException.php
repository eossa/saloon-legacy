<?php

namespace Saloon\Tests\Fixtures\Exceptions;

use Exception;
use Saloon\Http\PendingRequest;

class TestResponseException extends Exception
{
    /**
     * Pending Request
     *
     * @var PendingRequest
     */
    protected $pendingRequest;

    /**
     * Constructor
     *
     * @param string $message
     * @param PendingRequest $pendingRequest
     */
    public function __construct($message, PendingRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;

        parent::__construct($message);
    }

    /**
     * Get the pending request
     *
     * @return PendingRequest
     */
    public function getPendingRequest()
    {
        return $this->pendingRequest;
    }
}
