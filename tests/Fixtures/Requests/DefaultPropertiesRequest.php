<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultPropertiesRequest extends Request
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
    protected $connector = TestConnector::class;

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
    protected function defaultHeaders()
    {
        return [
            'X-Favourite-Artist' => 'Luke Combs',
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultQuery()
    {
        return [
            'format' => 'json',
        ];
    }

    /**
     * @return mixed
     */
    protected function defaultData()
    {
        return [
            'song' => 'Call Me',
        ];
    }

    /**
     * @return true[]
     */
    protected function defaultConfig()
    {
        return [
            'debug' => true,
        ];
    }
}
