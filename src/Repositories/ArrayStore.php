<?php

namespace Saloon\Repositories;

use Saloon\Helpers\Helpers;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class ArrayStore implements ArrayStoreContract
{
    use Conditionable;

    /**
     * The repository's store
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieve all the items.
     *
     * @return array<string, mixed>
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Retrieve a single item.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $items = $this->all();
        return isset($items[$key]) ? $items[$key] : $default;
    }

    /**
     * Overwrite the entire repository.
     *
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function set(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Merge in other arrays.
     *
     * @param array<string, mixed> ...$arrays
     *
     * @return $this
     */
    public function merge(array ...$arrays)
    {
        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Add an item to the repository.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function add($key, $value)
    {
        $this->data[$key] = Helpers::value($value);

        return $this;
    }

    /**
     * Remove an item from the store.
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Determine if the store is empty
     *
     * @return bool
     *
     * @phpstan-assert-if-false non-empty-array $this->data
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     *
     * @return bool
     *
     * @phpstan-assert-if-true non-empty-array $this->data
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }
}
