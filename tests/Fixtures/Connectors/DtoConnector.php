<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Data\ApiResponse;

class DtoConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    public function defaultHeaders()
    {
        return [];
    }

    /**
     * Create DTO from Response
     *
     * @return mixed
     */
    public function createDtoFromResponse(Response $response)
    {
        return ApiResponse::fromSaloon($response);
    }
}
