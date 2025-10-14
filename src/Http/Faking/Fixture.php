<?php

namespace Saloon\Http\Faking;

use Closure;
use Exception;
use Saloon\Exceptions\DirectoryNotFoundException;
use Saloon\Exceptions\UnableToCreateDirectoryException;
use Saloon\MockConfig;
use Saloon\Helpers\Storage;
use Saloon\Helpers\ArrayHelpers;
use Saloon\Data\RecordedResponse;
use Saloon\Helpers\FixtureHelper;
use Saloon\Exceptions\FixtureException;
use Saloon\Exceptions\FixtureMissingException;
use Saloon\Repositories\Body\StringBodyRepository;

class Fixture
{
    /**
     * The extension used by the fixture
     *
     * @var string
     */
    protected static $fixtureExtension = 'json';

    /**
     * The name of the fixture
     *
     * @var string
     */
    protected $name = '';

    /**
     * The storage helper
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Data to merge in the mocked response.
     *
     * @var array<string, mixed>|null
     */
    protected $merge = null;

    /**
     * Closure to modify the returned data with.
     *
     * @var Closure|null
     */
    protected $through = null;

    /**
     * Constructor
     *
     * @param string $name
     * @param Storage|null $storage
     *
     * @throws DirectoryNotFoundException
     * @throws UnableToCreateDirectoryException
     */
    public function __construct($name = '', $storage = null)
    {
        $this->name = $name;
        $this->storage = $storage ?: new Storage(MockConfig::getFixturePath(), true);
    }

    /**
     * Specify data to merge with the mock response data.
     *
     * @param array<string, mixed> $merge
     *
     * @return $this
     */
    public function merge(array $merge = [])
    {
        $this->merge = $merge;

        return $this;
    }

    /**
     * Specify a closure to modify the mock response data with.
     *
     * @return $this
     */
    public function through(Closure $through)
    {
        $this->through = $through;

        return $this;
    }

    /**
     * Attempt to get the mock response from the fixture.
     *
     * @return MockResponse|null
     *
     * @throws Exception
     */
    public function getMockResponse()
    {
        $storage = $this->storage;
        $fixturePath = $this->getFixturePath();

        if ($storage->exists($fixturePath)) {
            $response = RecordedResponse::fromFile($storage->get($fixturePath))->toMockResponse();

            if (is_null($this->merge) && is_null($this->through)) {
                return $response;
            }

            // First, we get the body as an array. If we're dealing with
            // a `StringBodyRepository`, we have to encode it first.
            if (! is_array($body = $response->body()->all())) {
                $body = json_decode($body ?: '[]', true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception(json_last_error_msg());
                }
            }

            // We can then merge the data in the body usingthrough
            // the ArrayHelpers for dot-notation support.
            if (is_array($this->merge)) {
                foreach ($this->merge as $key => $value) {
                    ArrayHelpers::set($body, $key, $value);
                }
            }

            // If specified, we pass the body through a function that
            // may modify the mock response data.
            if (! is_null($this->through)) {
                $body = call_user_func($this->through, $body);
            }

            // We then set the mutated data back in the repository. If we're dealing
            // with a `StringBodyRepository`, we need to encode it back to string.
            $response->body()->set(
                $response->body() instanceof StringBodyRepository
                    ? json_encode($body)
                    : $body
            );

            return $response;
        }

        if (MockConfig::isThrowingOnMissingFixtures() === true) {
            throw new FixtureMissingException($fixturePath);
        }

        return null;
    }

    /**
     * Store data as the fixture.
     *
     * @return $this
     * @throws FixtureException
     * @throws Exception
     */
    public function store(RecordedResponse $recordedResponse)
    {
        $recordedResponse = $this->swapSensitiveHeaders($recordedResponse);
        $recordedResponse = $this->swapSensitiveJson($recordedResponse);
        $recordedResponse = $this->swapSensitiveBodyWithRegex($recordedResponse);
        $recordedResponse = $this->beforeSave($recordedResponse);

        $this->storage->put($this->getFixturePath(), $recordedResponse->toFile());

        return $this;
    }

    /**
     * Get the fixture path
     *
     * @return string
     *
     * @throws FixtureException
     */
    public function getFixturePath()
    {
        $name = $this->name;

        if (empty($name)) {
            $name = $this->defineName();
        }

        if (empty($name)) {
            throw new FixtureException('The fixture must have a name');
        }

        return sprintf('%s.%s', $name, $this::$fixtureExtension);
    }

    /**
     * Define the fixture name
     *
     * @return string
     */
    protected function defineName()
    {
        return '';
    }

    /**
     * Swap any sensitive headers
     *
     * @return RecordedResponse
     */
    protected function swapSensitiveHeaders(RecordedResponse $recordedResponse)
    {
        $sensitiveHeaders = $this->defineSensitiveHeaders();

        if (empty($sensitiveHeaders)) {
            return $recordedResponse;
        }

        $recordedResponse->headers = FixtureHelper::recursivelyReplaceAttributes($recordedResponse->headers, $sensitiveHeaders, false);

        return $recordedResponse;
    }

    /**
     * Swap any sensitive JSON data
     *
     * @return RecordedResponse
     *
     * @throws Exception
     */
    protected function swapSensitiveJson(RecordedResponse $recordedResponse)
    {
        $body = json_decode($recordedResponse->data, true);

        if (empty($body) || json_last_error() !== JSON_ERROR_NONE) {
            return $recordedResponse;
        }

        $sensitiveJsonParameters = $this->defineSensitiveJsonParameters();

        if (empty($sensitiveJsonParameters)) {
            return $recordedResponse;
        }

        $redactedData = FixtureHelper::recursivelyReplaceAttributes($body, $sensitiveJsonParameters);

        $recordedResponse->data = json_encode($redactedData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        return $recordedResponse;
    }

    /**
     * Swap sensitive body with regex patterns
     *
     * @return RecordedResponse
     */
    protected function swapSensitiveBodyWithRegex(RecordedResponse $recordedResponse)
    {
        $sensitiveRegexPatterns = $this->defineSensitiveRegexPatterns();

        if (empty($sensitiveRegexPatterns)) {
            return $recordedResponse;
        }

        $redactedData = FixtureHelper::replaceSensitiveRegexPatterns($recordedResponse->data, $sensitiveRegexPatterns);

        $recordedResponse->data = $redactedData;

        return $recordedResponse;
    }

    /**
     * Swap any sensitive headers
     *
     * @return array<string, string|callable>
     */
    protected function defineSensitiveHeaders()
    {
        return [];
    }

    /**
     * Swap any sensitive JSON parameters
     *
     * @return array<string, string|callable>
     */
    protected function defineSensitiveJsonParameters()
    {
        return [];
    }

    /**
     * Define regex patterns that should be replaced
     *
     * @return array<string, string>
     */
    protected function defineSensitiveRegexPatterns()
    {
        return [];
    }

    /**
     * Hook to use before saving
     *
     * @return RecordedResponse
     */
    protected function beforeSave(RecordedResponse $recordedResponse)
    {
        return $recordedResponse;
    }
}
