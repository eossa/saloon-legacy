<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStringBody;
use Saloon\Contracts\Body\HasBody as HasBodyContract;

class HasStringBodyRequest extends Request implements HasBodyContract
{
    use HasStringBody;

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
     * @return string|null
     */
    protected function defaultBody()
    {
        return 'name: Sam';
    }
}
