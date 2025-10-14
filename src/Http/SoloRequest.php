<?php

namespace Saloon\Http;

use Saloon\Traits\Request\HasConnector;
use Saloon\Http\Connectors\NullConnector;

abstract class SoloRequest extends Request
{
    use HasConnector;

    /**
     * Create a new connector instance.
     *
     * @return Connector
     */
    protected function resolveConnector()
    {
        return new NullConnector;
    }
}
