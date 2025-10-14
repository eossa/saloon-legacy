<?php

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Implements all the PSR-17 interfaces.
 *
 * Note: in consuming code it is recommended to require the implemented interfaces
 * and inject the instance of this class multiple times.
 */
final class HttpFactory implements
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface
{
    /**
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        StreamInterface $stream,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        if ($size === null) {
            $size = $stream->getSize();
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * @param string $content
     * @return StreamInterface
     */
    public function createStream($content = '')
    {
        return Utils::streamFor($content);
    }

    /**
     * @param string $file
     * @param string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile($file, $mode = 'r')
    {
        try {
            $resource = Utils::tryFopen($file, $mode);
        } catch (\RuntimeException $e) {
            if ('' === $mode || false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true)) {
                throw new \InvalidArgumentException(sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
            }

            throw $e;
        }

        return Utils::streamFor($resource);
    }

    /**
     * @param $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource)
    {
        return Utils::streamFor($resource);
    }

    /**
     * @param string $method
     * @param $uri
     * @param array $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest($method, $uri, array $serverParams = [])
    {
        if (empty($method)) {
            if (!empty($serverParams['REQUEST_METHOD'])) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new \InvalidArgumentException('Cannot determine HTTP method');
            }
        }

        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse($code = 200, $reasonPhrase = '')
    {
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }

    /**
     * @param string $method
     * @param $uri
     * @return RequestInterface
     */
    public function createRequest($method, $uri)
    {
        return new Request($method, $uri);
    }

    /**
     * @param string $uri
     * @return UriInterface
     */
    public function createUri($uri = '')
    {
        return new Uri($uri);
    }
}
