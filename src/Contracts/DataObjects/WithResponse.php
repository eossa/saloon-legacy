<?php

namespace Saloon\Contracts\DataObjects;

use Saloon\Http\Response;

interface WithResponse
{
    /**
     * Set the response on the data object.
     *
     * @return $this
     */
    public function setResponse(Response $response);

    /**
     * Get the response on the data object.
     *
     * @return Response
     */
    public function getResponse();
}
