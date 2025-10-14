<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Sender;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Senders\ArraySender;

class ArraySenderDefaultMethodConnector extends Connector
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
     * Default Sender
     *
     * @return Sender
     */
    protected function defaultSender()
    {
        return new ArraySender();
    }
}
