<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\HasJsonBody;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

class ReplaceConfigRequest extends Request
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected $connector = HeaderConnector::class;

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
     * @return array
     */
    public function defaultConfig()
    {
        return [
            'debug' => false,
        ];
    }

    /**
     * @return array{foo: string}
     */
    public function defaultData()
    {
        return [
            'foo' => 'bar',
        ];
    }
}
