<?php

namespace Saloon\Contracts;

interface ArrayStore
{
    /**
     * Retrieve all the items.
     *
     * @return array<string, mixed>
     */
    public function all();

    /**
     * Retrieve a single item.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Overwrite the entire repository's contents.
     *
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function set(array $data);

    /**
     * Merge in other arrays.
     *
     * @param array<string, mixed> ...$arrays
     *
     * @return $this
     */
    public function merge(array ...$arrays);

    /**
     * Add an item to the repository.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function add($key, $value);

    /**
     * Remove an item from the store.
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key);

    /**
     * Determine if the store is empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the store is not empty
     *
     * @return bool
     */
    public function isNotEmpty();
}
