<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;

class QueryParameterConnectorRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
     */
    protected $connector = QueryParameterConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }

    /**
     * @return string[]
     */
    protected function defaultQuery()
    {
        return [
            'include' => 'user',
        ];
    }
}
