<?php

namespace Saloon\Helpers;

use Closure;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\VarDumper;

class Debugger
{
    /**
     * Application "Die" handler.
     *
     * Only used for Saloon tests
     *
     * @var ?Closure
     */
    public static $dieHandler = null;

    /**
     * Debug a request with Symfony Var Dumper
     *
     * @return void
     */
    public static function symfonyRequestDebugger(PendingRequest $pendingRequest, RequestInterface $psrRequest)
    {
        $headers = [];

        foreach ($psrRequest->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }

        $className = explode('\\', get_class($pendingRequest->getRequest()));
        $label = end($className);

        VarDumper::dump([
            'connector' => get_class($pendingRequest->getConnector()),
            'request' => get_class($pendingRequest->getRequest()),
            'method' => $psrRequest->getMethod(),
            'uri' => (string)$psrRequest->getUri(),
            'headers' => $headers,
            'body' => (string)$psrRequest->getBody(),
        ], 'Saloon Request (' . $label . ') ->');
    }

    /**
     * Debug a response with Symfony Var Dumper
     *
     * @return void
     */
    public static function symfonyResponseDebugger(Response $response, ResponseInterface $psrResponse)
    {
        $headers = [];

        foreach ($psrResponse->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }

        $className = explode('\\', get_class($response->getRequest()));
        $label = end($className);

        VarDumper::dump([
            'status' => $response->status(),
            'headers' => $headers,
            'body' => $response->body(),
        ], 'Saloon Response (' . $label . ') ->');
    }

    /**
     * Kill the application
     *
     * This is a method as it can be easily mocked during tests
     *
     * @return void
     */
    public static function dieApp()
    {
        $handler = isset(self::$dieHandler) ? self::$dieHandler : static function () {
            return exit(1);
        };

        $handler();
    }
}
