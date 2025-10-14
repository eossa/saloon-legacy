<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\WithBootConnector;

class UserRequestWithBoot extends Request
{
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
    protected $connector = WithBootConnector::class;
    protected $farewell = 'Ride on, cowboy.';

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
     * @return void
     */
    public function boot(Request $request)
    {
        $this->addHeader('X-Request-Boot-Header', 'Yee-haw!');
        $this->addHeader('X-Request-Boot-With-Data', $request->farewell);
    }


    /**
     * @param string $farewell
     */
    public function __construct($farewell = 'Ride on, cowboy.')
    {
        $this->farewell = $farewell;
    }
}
