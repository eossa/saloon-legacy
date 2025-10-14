<?php

namespace Saloon\Repositories;

use Saloon\Traits\Conditionable;
use Saloon\Contracts\IntegerStore as IntegerStoreContract;

class IntegerStore implements IntegerStoreContract
{
    use Conditionable;

    /**
     * store Data
     *
     * @var int|null
     */
    protected $data = null;

    /**
     * Constructor
     *
     * @param int|null $value
     */
    public function __construct($value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the store
     *
     * @param int|null $value
     *
     * @return $this
     */
    public function set($value)
    {
        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the store
     *
     * @return int|null
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * Determine if the store is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }
}
