<?php

namespace Saloon\Http;

use Exception;
use LogicException;
use SimpleXMLElement;
use Saloon\Traits\Macroable;
use InvalidArgumentException;
use Saloon\Helpers\ArrayHelpers;
use Saloon\Helpers\ObjectHelpers;
use Saloon\XmlWrangler\XmlReader;
use Illuminate\Support\Collection;
use Saloon\Contracts\FakeResponse;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Helpers\RequestExceptionHelper;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class Response
{
    use Macroable;

    /**
     * The PSR request
     *
     * @var RequestInterface
     */
    protected $psrRequest;

    /**
     * The PSR response from the sender.
     *
     * @var ResponseInterface
     */
    protected $psrResponse;

    /**
     * The pending request that has all the request properties
     *
     * @var PendingRequest
     */
    protected $pendingRequest;

    /**
     * The original sender exception
     *
     * @var Exception|null
     */
    protected $senderException = null;

    /**
     * The decoded JSON response.
     *
     * @var array<array-key, mixed>
     */
    protected $decodedJson;

    /**
     * The decoded JSON response object.
     *
     * @var mixed
     */
    protected $decodedJsonObject;

    /**
     * The decoded XML response.
     *
     * @var string
     */
    protected $decodedXml;

    /**
     * Denotes if the response has been mocked.
     *
     * @var bool
     */
    protected $mocked = false;

    /**
     * Denotes if the response has been cached.
     *
     * @var bool
     */
    protected $cached = false;

    /**
     * The simulated response payload if the response was simulated.
     *
     * @var FakeResponse|null
     */
    protected $fakeResponse = null;

    /**
     * Create a new response instance.
     *
     * @param ResponseInterface $psrResponse
     * @param PendingRequest $pendingRequest
     * @param RequestInterface $psrRequest
     * @param Exception|null $senderException
     */
    public function __construct(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, Exception $senderException = null)
    {
        $this->psrRequest = $psrRequest;
        $this->psrResponse = $psrResponse;
        $this->pendingRequest = $pendingRequest;
        $this->senderException = $senderException;
    }

    /**
     * Create a new response instance
     *
     * @param ResponseInterface $psrResponse
     * @param PendingRequest $pendingRequest
     * @param RequestInterface $psrRequest
     * @param Exception|null $senderException
     *
     * @return static
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, Exception $senderException = null)
    {
        return new static($psrResponse, $pendingRequest, $psrRequest, $senderException);
    }

    /**
     * Get the pending request that created the response.
     *
     * @return PendingRequest
     */
    public function getPendingRequest()
    {
        return $this->pendingRequest;
    }

    /**
     * Get the connector that sent the request
     *
     * @return Connector
     */
    public function getConnector()
    {
        return $this->pendingRequest->getConnector();
    }

    /**
     * Get the original request that created the response.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the PSR-7 request
     *
     * @return RequestInterface
     */
    public function getPsrRequest()
    {
        return $this->psrRequest;
    }

    /**
     * Create a PSR response from the raw response.
     *
     * @return ResponseInterface
     */
    public function getPsrResponse()
    {
        return $this->psrResponse;
    }

    /**
     * Get the body of the response as string.
     *
     * @return string
     */
    public function body()
    {
        $stream = $this->stream();

        $contents = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $contents;
    }

    /**
     * Get the body as a stream.
     *
     * @return StreamInterface
     */
    public function stream()
    {
        $stream = $this->psrResponse->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $stream;
    }

    /**
     * Get the headers from the response.
     *
     * @return ArrayStoreContract
     */
    public function headers()
    {
        $headers = array_map(static function (array $header) {
            return count($header) === 1 ? $header[0] : $header;
        }, $this->psrResponse->getHeaders());

        return new ArrayStore($headers);
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->psrResponse->getStatusCode();
    }

    /**
     * Get the original sender exception
     *
     * @return Exception|null
     */
    public function getSenderException()
    {
        return $this->senderException;
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param array-key|null $key
     * @param mixed $default
     *
     * @return ($key is null ? array<array-key, mixed> : mixed)
     *
     * @throws Exception
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->decodedJson)) {
            $this->decodedJson = json_decode($this->body() ?: '[]', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }
        }

        if (is_null($key)) {
            return $this->decodedJson;
        }

        return ArrayHelpers::get($this->decodedJson, $key, $default);
    }

    /**
     * Get the JSON decoded body as an array. Provide a key to find a specific item in the JSON.
     *
     * Alias of json()
     *
     * @param array-key|null $key
     * @param mixed $default
     *
     * @return ($key is null ? array<array-key, mixed> : mixed)
     *
     * @throws Exception
     */
    public function toArray($key = null, $default = null)
    {
        return $this->json($key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object or scalar value.
     *
     * @param array-key|null $key
     * @param mixed $default
     *
     * @return ($key is null ? object : mixed)
     *
     * @throws Exception
     */
    public function object($key = null, $default = null)
    {
        if (! isset($this->decodedJsonObject)) {
            $this->decodedJsonObject = json_decode($this->body() ?: '{}', false);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }
        }

        if (is_null($key)) {
            return $this->decodedJsonObject;
        }

        return ObjectHelpers::get($this->decodedJsonObject, (string)$key, $default);
    }

    /**
     * Convert the XML response into a SimpleXMLElement.
     *
     * Suitable for reading small, simple XML responses but not suitable for
     * more advanced XML responses with namespaces and prefixes. Consider
     * using the xmlReader method instead for better compatibility.
     *
     * @see https://www.php.net/manual/en/book.simplexml.php
     *
     * @param mixed ...$arguments
     *
     * @return SimpleXMLElement|bool
     */
    public function xml(...$arguments)
    {
        if (! isset($this->decodedXml)) {
            $this->decodedXml = $this->body();
        }

        return simplexml_load_string($this->decodedXml, ...$arguments);
    }

    /**
     * Load the XML response into a reader
     *
     * Suitable for reading large XML responses and supports a wider range of XML
     * documents. Requires XML Wrangler (composer require saloonphp/xml-wrangler)
     *
     * @see https://github.com/saloonphp/xml-wrangler
     *
     * @return XmlReader
     */
    public function xmlReader()
    {
        return XmlReader::fromSaloonResponse($this);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * Requires Laravel Collections (composer require illuminate/collections)
     * @see https://github.com/illuminate/collections
     *
     * @param array-key|null $key
     *
     * @return Collection<array-key, mixed>
     *
     * @throws Exception
     */
    public function collect($key = null)
    {
        $data = $this->json($key);

        if (is_null($data)) {
            return new Collection();
        }

        if (is_array($data)) {
            return new Collection($data);
        }

        return new Collection([$data]);
    }

    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function dto()
    {
        $request = $this->pendingRequest->getRequest();
        $connector = $this->pendingRequest->getConnector();

        $dataObject = $request->createDtoFromResponse($this) ?: $connector->createDtoFromResponse($this);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($this);
        }

        return $dataObject;
    }

    /**
     * Convert the response into a DTO or throw a LogicException if the response failed
     *
     * @return mixed
     */
    public function dtoOrFail()
    {
        if ($this->failed()) {
            throw new LogicException('Unable to create data transfer object as the response has failed.', 0, $this->toException());
        }

        return $this->dto();
    }

    /**
     * Parse the HTML or XML body into a Symfony DomCrawler instance.
     *
     * Requires Symfony Crawler (composer require symfony/dom-crawler)
     *
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     *
     * @return Crawler
     */
    public function dom()
    {
        return new Crawler($this->body());
    }

    /**
     * Convert the response to a data URL
     *
     * @return string
     */
    public function dataUrl()
    {
        return 'data:'.$this->psrResponse->getHeaderLine('Content-Type').';base64,'.base64_encode($this->body());
    }

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed()
    {
        $pendingRequest = $this->getPendingRequest();

        $requestFailedAccordingToConnector = $pendingRequest->getConnector()->hasRequestFailed($this);
        $requestFailedAccordingToRequest = $pendingRequest->getRequest()->hasRequestFailed($this);

        if ($requestFailedAccordingToRequest !== null || $requestFailedAccordingToConnector !== null) {
            return $requestFailedAccordingToRequest || $requestFailedAccordingToConnector;
        }

        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable($this): (void) $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Determine if the response should throw a request exception.
     *
     * @return bool
     */
    public function shouldThrowRequestException()
    {
        $pendingRequest = $this->getPendingRequest();

        return $pendingRequest->getRequest()->shouldThrowRequestException($this) || $pendingRequest->getConnector()->shouldThrowRequestException($this);
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return ?Exception
     */
    public function toException()
    {
        if (! $this->shouldThrowRequestException()) {
            return null;
        }

        return $this->createException();
    }

    /**
     * Create the request exception
     *
     * @return Exception
     */
    protected function createException()
    {
        $pendingRequest = $this->getPendingRequest();
        $senderException = $this->getSenderException();

        // We'll first check if the user has defined their own exception handlers.
        // We'll prioritise the request over the connector.

        $exception = $pendingRequest->getRequest()->getRequestException($this, $senderException) ?: $pendingRequest->getConnector()->getRequestException($this, $senderException);

        if ($exception instanceof Exception) {
            return $exception;
        }

        // Otherwise, we'll throw our own request.

        return RequestExceptionHelper::create($this, $senderException);
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws Exception
     */
    public function throwException()
    {
        if ($this->shouldThrowRequestException()) {
            throw $this->toException();
        }

        return $this;
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     *
     * @return string|array<array-key, mixed>|null
     */
    public function header($header)
    {
        return $this->headers()->get($header);
    }

    /**
     * Create a temporary resource for the stream.
     *
     * Useful for storing the file. Make sure to close the raw stream after you have used it.
     *
     * @return resource
     */
    public function getRawStream()
    {
        $temporaryResource = fopen('php://temp', 'wb+');

        if ($temporaryResource === false) {
            throw new LogicException('Unable to create a temporary resource for the stream.');
        }

        $this->saveBodyToFile($temporaryResource, false);

        return $temporaryResource;
    }

    /**
     * Save the body to a file
     *
     * @param string|resource $resourceOrPath
     * @param bool $closeResource
     *
     * @return void
     */
    public function saveBodyToFile($resourceOrPath, $closeResource = true)
    {
        if (! is_string($resourceOrPath) && ! is_resource($resourceOrPath)) {
            throw new InvalidArgumentException('The $resourceOrPath argument must be either a file path or a resource.');
        }

        $resource = is_string($resourceOrPath) ? fopen($resourceOrPath, 'wb+') : $resourceOrPath;

        if ($resource === false) {
            throw new LogicException('Unable to open the resource.');
        }

        rewind($resource);

        $stream = $this->stream();

        while (! $stream->eof()) {
            fwrite($resource, $stream->read(1024));
        }

        rewind($resource);

        if ($closeResource === true) {
            fclose($resource);
        }
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close()
    {
        $this->stream()->close();

        return $this;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }

    /**
     * Check if the response has been cached
     *
     * @return bool
     */
    public function isCached()
    {
        return $this->cached;
    }

    /**
     * Check if the response has been mocked
     *
     * @return bool
     */
    public function isMocked()
    {
        return $this->mocked;
    }

    /**
     * Check if the response has been simulated
     *
     * @return bool
     */
    public function isFaked()
    {
        return $this->isMocked() || $this->isCached();
    }

    /**
     * Set if a response has been cached or not.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setCached($value)
    {
        $this->cached = true;

        return $this;
    }

    /**
     * Set if a response has been mocked or not.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setMocked($value)
    {
        $this->mocked = true;

        return $this;
    }

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse)
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Get the simulated response payload if the response was simulated.
     *
     * @return FakeResponse|null
     */
    public function getFakeResponse()
    {
        return $this->fakeResponse;
    }
}
