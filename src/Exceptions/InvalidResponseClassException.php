<?php

namespace Saloon\Exceptions;

use Saloon\Http\Response;

class InvalidResponseClassException extends SaloonException
{
    /**
     * Constructor
     *
     * @param ?string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(isset($message) ? $message : sprintf('The provided response must exist and implement the %s contract.', Response::class));
    }
}
