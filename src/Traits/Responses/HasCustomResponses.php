<?php

namespace Saloon\Traits\Responses;

use Saloon\Http\Response;

trait HasCustomResponses
{
    /**
     * Specify a default response.
     *
     * When null or an empty string, the response on the sender will be used.
     *
     * @var class-string<Response>|null
     */
    protected $response = null;

    /**
     * Resolve the custom response class
     *
     * @return class-string<Response>|null
     */
    public function resolveResponseClass()
    {
        return isset($this->response) ? $this->response : null;
    }
}
