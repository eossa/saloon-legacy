<?php

namespace Saloon\Data;

use Psr\Http\Message\UriFactoryInterface;
use Saloon\Contracts\MultipartBodyFactory;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class FactoryCollection
{
    /**
     * @var RequestFactoryInterface
     */
    public $requestFactory;

    /**
     * @var UriFactoryInterface
     */
    public $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    public $streamFactory;

    /**
     * @var ResponseFactoryInterface
     */
    public $responseFactory;

    /**
     * @var MultipartBodyFactory
     */
    public $multipartBodyFactory;

    /**
     * Constructor
     *
     * This class is used to collect all the different PSR and Saloon factories
     * together into one, simple class that can be defined by senders.
     */
    public function __construct(
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory,
        MultipartBodyFactory $multipartBodyFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
        $this->multipartBodyFactory = $multipartBodyFactory;
    }
}
