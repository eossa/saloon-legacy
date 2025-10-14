<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class PagedSuperheroRequest extends Request
{
    /**
     * @var string
     */
    protected $method = Method::GET;

    /**
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/superheroes/per-page';
    }
}
