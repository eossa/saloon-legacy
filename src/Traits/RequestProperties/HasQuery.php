<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasQuery
{
    /**
     * Request Query Parameters
     *
     * @var ArrayStoreContract
     */
    protected $query;

    /**
     * Access the query parameters
     *
     * @return ArrayStoreContract
     */
    public function query()
    {
        if (isset($this->query)) {
            return $this->query;
        }
        return $this->query = new ArrayStore($this->defaultQuery());
    }

    /**
     * Default Query Parameters
     *
     * @return array<string, mixed>
     */
    protected function defaultQuery()
    {
        return [];
    }
}
