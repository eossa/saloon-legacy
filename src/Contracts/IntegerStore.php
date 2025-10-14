<?php

namespace Saloon\Contracts;

interface IntegerStore
{
    /**
     * Set a value inside the repository
     *
     * @param int|null $value
     *
     * @return $this
     */
    public function set($value);

    /**
     * Retrieve all in the repository
     *
     * @return int|null
     */
    public function get();

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
}
