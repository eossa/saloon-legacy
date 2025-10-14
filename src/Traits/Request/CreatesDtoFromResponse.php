<?php

namespace Saloon\Traits\Request;

use Saloon\Http\Response;

trait CreatesDtoFromResponse
{
    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function createDtoFromResponse(Response $response)
    {
        return null;
    }
}
