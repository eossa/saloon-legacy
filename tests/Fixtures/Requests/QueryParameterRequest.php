<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class QueryParameterRequest extends Request
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
     * @var string
     */
    public $endpoint;

    /**
     * Constructor
     *
     * @param string $endpoint
     */
    public function __construct($endpoint = '/user')
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return int[]
     */
    protected function defaultQuery()
    {
        return [
            'per_page' => 100,
        ];
    }
}
