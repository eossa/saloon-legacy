<?php

namespace Saloon\Repositories\Body;

use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;

class StreamBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * The stream body
     *
     * @var StreamInterface|resource|null
     */
    protected $stream = null;

    /**
     * Constructor
     *
     * @param StreamInterface|resource|null $value
     */
    public function __construct($value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param StreamInterface|resource|null $value
     * @return $this
     */
    public function set($value)
    {
        if (isset($value) && ! $value instanceof StreamInterface && ! is_resource($value)) {
            throw new InvalidArgumentException('The value must a resource or be an instance of ' . StreamInterface::class);
        }

        $this->stream = $value;

        return $this;
    }

    /**
     * Retrieve the stream from the repository
     *
     * @return resource|StreamInterface|null
     */
    public function all()
    {
        return $this->stream;
    }

    /**
     * Retrieve the stream from the repository
     *
     * Alias of "all" method.
     *
     * @return StreamInterface|resource|null
     */
    public function get()
    {
        return $this->all();
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->stream);
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert the body repository into a stream
     *
     * @return StreamInterface
     */
    public function toStream(StreamFactoryInterface $streamFactory)
    {
        $stream = $this->stream;

        return $stream instanceof StreamInterface ? $stream : $streamFactory->createStreamFromResource($stream);
    }
}
