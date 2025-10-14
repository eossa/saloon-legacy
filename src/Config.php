<?php

namespace Saloon;

use Saloon\Enums\PipeOrder;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Exceptions\StrayRequestException;

final class Config
{
    /**
     * Default Sender
     *
     * @var class-string<Sender>
     */
    public static $defaultSender = GuzzleSender::class;

    /**
     * Default TLS Method (v1.2)
     *
     * @var int
     */
    public static $defaultTlsMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

    /**
     * Default timeout (in seconds) for establishing a connection.
     *
     * @var int
     */
    public static $defaultConnectionTimeout = 10;

    /**
     * Default timeout (in seconds) for making requests
     *
     * @var int
     */
    public static $defaultRequestTimeout = 30;

    /**
     * Resolve the sender with a callback
     *
     * @var callable|null
     */
    private static $senderResolver = null;

    /**
     * Global Middleware Pipeline
     *
     * @var MiddlewarePipeline|null
     */
    private static $globalMiddlewarePipeline = null;

    /**
     * Write a custom sender resolver
     *
     * @param callable|null $senderResolver
     *
     * @return void
     */
    public static function setSenderResolver(callable $senderResolver = null)
    {
        self::$senderResolver = $senderResolver;
    }

    /**
     * Create a new default sender
     *
     * @return Sender
     */
    public static function getDefaultSender()
    {
        $senderResolver = self::$senderResolver;

        return is_callable($senderResolver) ? $senderResolver() : new self::$defaultSender;
    }

    /**
     * Update global middleware
     *
     * @return MiddlewarePipeline
     */
    public static function globalMiddleware()
    {
        if (isset(self::$globalMiddlewarePipeline)) {
            return self::$globalMiddlewarePipeline;
        }
        return self::$globalMiddlewarePipeline = new MiddlewarePipeline();
    }

    /**
     * Reset global middleware
     *
     * @return void
     */
    public static function clearGlobalMiddleware()
    {
        self::$globalMiddlewarePipeline = null;
    }

    /**
     * Throw an exception if a request without a MockClient is made.
     *
     * @return void
     *
     * @throws DuplicatePipeNameException
     */
    public static function preventStrayRequests()
    {
        self::globalMiddleware()->onRequest(static function (PendingRequest $pendingRequest) {
            if (! $pendingRequest->hasMockClient()) {
                throw new StrayRequestException;
            }
        }, null, PipeOrder::LAST);
    }
}
