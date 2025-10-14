<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class WithBootConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return apiUrl();
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $this->addHeader('X-Connector-Boot-Header', 'Howdy!');
        $this->addHeader('X-Connector-Request-Class', get_class($request));
    }
}
