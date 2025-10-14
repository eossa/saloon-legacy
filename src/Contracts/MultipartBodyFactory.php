<?php

namespace Saloon\Contracts;

use Saloon\Data\MultipartValue;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface MultipartBodyFactory
{
    /**
     * Create a multipart body
     *
     * @param StreamFactoryInterface $streamFactory
     * @param array<MultipartValue> $multipartValues
     * @param string $boundary
     *
     * @return StreamInterface
     */
    public function create(StreamFactoryInterface $streamFactory, array $multipartValues, $boundary);
}
