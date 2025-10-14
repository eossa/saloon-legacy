<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Exception;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Exceptions\ConnectorRequestException;

class CustomExceptionConnector extends Connector
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
     * Customise the request exception handler
     *
     * @param Response $response
     * @param Exception|null $senderException
     *
     * @return Exception|null
     */
    public function getRequestException(Response $response, Exception $senderException = null)
    {
        return new ConnectorRequestException($response, 'Oh yee-naw.', 0, $senderException);
    }
}
