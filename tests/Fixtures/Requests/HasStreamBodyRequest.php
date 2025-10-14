<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasStreamBody;

class HasStreamBodyRequest extends Request implements HasBody
{
    use HasStreamBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::GET;

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
     * @return mixed
     */
    protected function defaultBody()
    {
        $temp = fopen('php://memory', 'rw');

        fwrite($temp, 'Howdy, Partner');

        rewind($temp);

        return $temp;
    }
}
