<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasHeaders
{
    /**
     * Request Headers
     *
     * @var ArrayStoreContract
     */
    protected $headers;

    /**
     * Access the headers
     *
     * @return ArrayStoreContract
     */
    public function headers()
    {
        if (isset($this->headers)) {
            return $this->headers;
        }
        return $this->headers = new ArrayStore($this->defaultHeaders());
    }

    /**
     * Default Request Headers
     *
     * @return array<string, mixed>
     */
    protected function defaultHeaders()
    {
        return [];
    }
}
