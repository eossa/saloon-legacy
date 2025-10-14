<?php

namespace Saloon\Contracts\Body;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface BodyRepository
{
    /**
     * Set the raw data in the repository
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function set($value);

    /**
     * Get the raw data in the repository.
     *
     * @return mixed
     */
    public function all();

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Convert the body repository into a stream
     *
     * @return StreamInterface
     */
    public function toStream(StreamFactoryInterface $streamFactory);
}
