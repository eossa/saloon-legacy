<?php

namespace Saloon\Repositories\Body;

use Exception;
use InvalidArgumentException;
use Saloon\Data\MultipartValue;
use Saloon\Traits\Conditionable;
use Saloon\Helpers\StringHelpers;
use Saloon\Exceptions\BodyException;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Contracts\MultipartBodyFactory;
use Psr\Http\Message\StreamFactoryInterface;

class MultipartBodyRepository implements BodyRepository, MergeableBody
{
    use Conditionable;

    /**
     * Base Repository
     *
     * @var ArrayBodyRepository
     */
    protected $data;

    /**
     * The Multipart Boundary
     *
     * @var string
     */
    protected $boundary;

    /**
     * Multipart Body Factory
     *
     * @var MultipartBodyFactory
     */
    protected $multipartBodyFactory;

    /**
     * Constructor
     *
     * @param array<MultipartValue> $value
     * @param ?string $boundary
     *
     * @throws Exception
     */
    public function __construct(array $value = [], $boundary = null)
    {
        $this->data = new ArrayBodyRepository;
        $this->boundary = is_null($boundary) ? StringHelpers::random(40) : $boundary;

        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param array<MultipartValue> $value
     *
     * @return $this
     */
    public function set($value)
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The value must be an array');
        }

        $this->data->set(
            $this->parseMultipartArray($value)
        );

        return $this;
    }

    /**
     * Merge another array into the repository
     *
     * @param array<MultipartValue> ...$arrays
     *
     * @return $this
     */
    public function merge(array ...$arrays)
    {
        $this->data->merge(...array_map(
            function ($array) {
                return $this->parseMultipartArray($array);
            },
            $arrays
        ));

        return $this;
    }

    /**
     * Add an element to the repository.
     *
     * @param string $name
     * @param StreamInterface|resource|string $contents
     * @param ?string $filename
     * @param array<string, mixed> $headers
     *
     * @return $this
     */
    public function add($name, $contents, $filename = null, array $headers = [])
    {
        $this->attach(new MultipartValue($name, $contents, $filename, $headers));

        return $this;
    }

    /**
     * Attach a multipart file
     *
     * @return $this
     */
    public function attach(MultipartValue $file)
    {
        $this->data->add(null, $file);

        return $this;
    }

    /**
     * Get the raw data in the repository.
     *
     * @return array<MultipartValue>
     */
    public function all()
    {
        return $this->data->all();
    }

    /**
     * Get a specific key of the array
     *
     * @param array-key $key
     * @param mixed $default
     *
     * @return MultipartValue|array<MultipartValue>
     */
    public function get($key, $default = null)
    {
        $values = array_values(array_filter($this->all(), static function (MultipartValue $value) use ($key) {
            return $value->name === $key;
        }));

        if (count($values) === 0) {
            return $default;
        }

        if (count($values) === 1) {
            return $values[0];
        }

        return $values;
    }

    /**
     * Remove an item from the repository.
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        $values = array_filter($this->all(), static function (MultipartValue $value) use ($key) {
            return $value->name !== $key;
        });

        $this->set($values);

        return $this;
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->data->isEmpty();
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->data->isNotEmpty();
    }

    /**
     * Parse a multipart array
     *
     * @param array<string, mixed> $value
     * @return array<MultipartValue>
     */
    protected function parseMultipartArray(array $value)
    {
        $multipartValues = array_filter($value, static function ($item) {
            return $item instanceof MultipartValue;
        });

        if (count($value) !== count($multipartValues)) {
            throw new InvalidArgumentException(sprintf('The value array must only contain %s objects.', MultipartValue::class));
        }

        return array_values($value);
    }

    /**
     * Set the multipart body factory
     *
     * @return MultipartBodyRepository
     */
    public function setMultipartBodyFactory(MultipartBodyFactory $multipartBodyFactory)
    {
        $this->multipartBodyFactory = $multipartBodyFactory;

        return $this;
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Convert the body repository into a stream
     *
     * @param StreamFactoryInterface $streamFactory
     *
     * @return StreamInterface
     *
     * @throws BodyException
     */
    public function toStream(StreamFactoryInterface $streamFactory)
    {
        if (! isset($this->multipartBodyFactory)) {
            throw new BodyException('Unable to create a multipart body stream because the multipart body factory was not set.');
        }

        return $this->multipartBodyFactory->create($streamFactory, $this->all(), $this->getBoundary());
    }
}
