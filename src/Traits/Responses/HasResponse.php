<?php

namespace Saloon\Traits\Responses;

use Saloon\Http\Response;

trait HasResponse
{
    /**
     * The original response.
     *
     * @var Response
     */
    protected $response;

    /**
     * Set the response on the data object.
     *
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response on the data object.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
