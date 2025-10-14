<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class FileDownloadRequest extends Request
{
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
        return '/download-test-pdf';
    }
}
