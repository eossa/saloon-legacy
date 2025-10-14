<?php

namespace Saloon\Http;

class BaseResource
{
    /**
     * @var Connector
     */
    protected $connector;

    /**
     * Constructor
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }
}
