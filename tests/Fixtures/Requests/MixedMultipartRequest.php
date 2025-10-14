<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasMultipartBody;

class MixedMultipartRequest extends Request implements HasBody
{
    use HasMultipartBody;

    /**
     * @var string
     */
    protected $method = Method::POST;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/mixed-multipart';
    }
}
