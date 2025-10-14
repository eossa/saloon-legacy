<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Tests\Fixtures\Responses\CustomResponse;

class CustomResponseConnector extends Connector
{
    /**
     * @var string|null
     */
    protected $response = CustomResponse::class;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return apiUrl();
    }
}
