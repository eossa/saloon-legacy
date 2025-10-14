<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class BootAuthenticatorRequest extends Request implements HasBody
{
    use HasJsonBody;

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
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }


    /**
     * @return void
     */
    public function boot(PendingRequest $pendingRequest)
    {
        $pendingRequest->withTokenAuth('howdy-partner');
    }
}
