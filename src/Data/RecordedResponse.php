<?php

namespace Saloon\Data;

use Exception;
use JsonSerializable;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockResponse;

class RecordedResponse implements JsonSerializable
{
    /**
     * @var int
     */
    public $statusCode;

    /**
     * @var mixed[]
     */
    public $headers;

    /**
     * @var mixed
     */
    public $data;

    /**
     * Constructor
     *
     * @param int $statusCode
     * @param array<string, mixed> $headers
     * @param mixed $data
     */
    public function __construct(
        $statusCode,
        array $headers = [],
        $data = null
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->data = $data;
    }

    /**
     * Create an instance from file contents
     *
     * @param string $contents
     *
     * @return $this
     *
     * @throws Exception
     */
    public static function fromFile($contents)
    {
        /**
         * @param array{
         *     statusCode: int,
         *     headers: array<string, mixed>,
         *     data: mixed,
         * } $fileData
         */
        $fileData = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        $data = $fileData['data'];

        if (isset($fileData['encoding']) && $fileData['encoding'] === 'base64') {
            $data = base64_decode($data);
        }

        return new static(
            $fileData['statusCode'],
            $fileData['headers'],
            $data
        );
    }

    /**
     * Create an instance from a Response
     *
     * @return $this
     */
    public static function fromResponse(Response $response)
    {
        return new static(
            $response->status(),
            $response->headers()->all(),
            $response->body()
        );
    }

    /**
     * Encode the instance to be stored as a file
     *
     * @return string
     *
     * @throws Exception
     */
    public function toFile()
    {
        $data = json_encode($this, JSON_PRETTY_PRINT);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }
        return $data;
    }

    /**
     * Create a mock response from the fixture
     *
     * @return MockResponse
     */
    public function toMockResponse()
    {
        return new MockResponse($this->data, $this->statusCode, $this->headers);
    }

    /**
     * Define the JSON object if this class is converted into JSON
     *
     * @return array{
     *     statusCode: int,
     *     headers: array<string, mixed>,
     *     data: mixed,
     * }
     */
    public function jsonSerialize()
    {
        $response = [
            'statusCode' => $this->statusCode,
            'headers' => $this->headers,
            'data' => $this->data,
        ];

        if ($this->checkIfEncodingIsInvalid($response['data'])) {
            $response['data'] = base64_encode($response['data']);
            $response['encoding'] = 'base64';
        }

        return $response;
    }

    /**
     * @param array|string $value
     *
     * @return bool
     */
    private function checkIfEncodingIsInvalid($value)
    {
        if (is_string($value)) {
            return mb_check_encoding($value, 'UTF-8') === false;
        }
        if (is_array($value)) {
            return array_reduce($value, function ($carry, $item) {
                return $carry || $this->checkIfEncodingIsInvalid($item);
            }, false);
        }
        return true;
    }
}
