<?php

namespace Saloon\Exceptions\Request;

use Exception;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Exceptions\SaloonException;

/**
 * RequestException
 *
 * This exception is thrown when the response from a request is a failed response.
 *
 * @see https://docs.saloon.dev/the-basics/handling-failures
 */
class RequestException extends SaloonException
{
    /**
     * The Saloon Response
     *
     * @var Response
     */
    protected $response;

    /**
     * Maximum length allowed for the body
     *
     * @var int
     */
    protected $maxBodyLength = 200;

    /**
     * Create the RequestException
     *
     * @param ?string $message
     * @param int $code
     * @param ?Exception $previous
     */
    public function __construct(Response $response, $message = null, $code = 0, $previous = null)
    {
        $this->response = $response;

        if (is_null($message)) {
            $status = $this->getStatus();
            $statusMessage = $this->getStatusMessage();
            $statusCodeMessage = isset($statusMessage) ? $statusMessage : 'Unknown Status';
            $rawBody = $response->body();
            $exceptionBodyMessage = mb_strlen($rawBody) > $this->maxBodyLength ? mb_substr($rawBody, 0, $this->maxBodyLength) : $rawBody;

            $message = sprintf('%s (%s) Response: %s', $statusCodeMessage, $status, $exceptionBodyMessage);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the Saloon Response Class.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the pending request.
     *
     * @return PendingRequest
     */
    public function getPendingRequest()
    {
        return $this->getResponse()->getPendingRequest();
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->response->status();
    }

    /**
     * Get the status message
     *
     * @return ?string
     */
    public function getStatusMessage()
    {
        return StatusCodeHelper::getMessage($this->getStatus());
    }
}
