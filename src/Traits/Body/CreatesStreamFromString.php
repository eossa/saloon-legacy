<?php

namespace Saloon\Traits\Body;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

trait CreatesStreamFromString
{
    /**
     * Convert the body repository into a stream
     *
     * @param StreamFactoryInterface $streamFactory
     *
     * @return StreamInterface
     */
    public function toStream(StreamFactoryInterface $streamFactory)
    {
        return $streamFactory->createStream((string)$this);
    }
}
