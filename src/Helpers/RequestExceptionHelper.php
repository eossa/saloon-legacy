<?php

namespace Saloon\Helpers;

use Exception;
use Saloon\Http\Response;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeoutException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;
use Saloon\Exceptions\Request\Statuses\PaymentRequiredException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\MethodNotAllowedException;
use Saloon\Exceptions\Request\Statuses\ServiceUnavailableException;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;

class RequestExceptionHelper
{
    /**
     * Create the request exception from a response
     *
     * @param Exception|null $previous
     *
     * @return RequestException
     */
    public static function create(Response $response, Exception $previous = null)
    {
        $status = $response->status();

        switch (true) {
            // Built-in exceptions
            case $status === 401:
                $requestException = UnauthorizedException::class;
                break;
            case $status === 402:
                $requestException = PaymentRequiredException::class;
                break;
            case $status === 403:
                $requestException = ForbiddenException::class;
                break;
            case $status === 404:
                $requestException = NotFoundException::class;
                break;
            case $status === 405:
                $requestException = MethodNotAllowedException::class;
                break;
            case $status === 408:
                $requestException = RequestTimeOutException::class;
                break;
            case $status === 422:
                $requestException = UnprocessableEntityException::class;
                break;
            case $status === 429:
                $requestException = TooManyRequestsException::class;
                break;
            case $status === 500:
                $requestException = InternalServerErrorException::class;
                break;
            case $status === 503:
                $requestException = ServiceUnavailableException::class;
                break;
            case $status === 504:
                $requestException = GatewayTimeoutException::class;
                break;

            // Fall-back exceptions
            case $response->serverError():
                $requestException = ServerException::class;
                break;
            case $response->clientError():
                $requestException = ClientException::class;
                break;
            default:
                $requestException = RequestException::class;
                break;
        }

        return new $requestException($response, null, 0, $previous);
    }
}
