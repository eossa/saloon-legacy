<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;

class MissingMethodRequest extends Request
{
    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }
}
