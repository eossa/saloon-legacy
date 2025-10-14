<?php

namespace Saloon\Exceptions;

use Exception;

class InvalidStateException extends SaloonException
{
    /**
     * @param string|null $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(isset($message) ? $message : 'Invalid state.', $code, $previous);
    }
}
