<?php

namespace Saloon\Repositories\Body;

use Saloon\Traits\Conditionable;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Traits\Body\CreatesStreamFromString;

class StringBodyRepository implements BodyRepository
{
    use CreatesStreamFromString;
    use Conditionable;

    /**
     * Repository Data
     *
     * @var string|null
     */
    protected $data = null;

    /**
     * Constructor
     *
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function set($value)
    {
        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the repository
     *
     * @return ?string
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert the repository into a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->all() ?: '';
    }
}
