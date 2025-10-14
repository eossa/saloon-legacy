<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Data\MultipartValue;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasMultipartBody;

class HasMultipartBodyRequest extends Request implements HasBody
{
    use HasMultipartBody;

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
     * Default Body
     *
     * @return MultipartValue[]
     */
    protected function defaultBody()
    {
        return [
            new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        ];
    }
}
