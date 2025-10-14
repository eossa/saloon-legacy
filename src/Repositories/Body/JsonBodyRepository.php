<?php

namespace Saloon\Repositories\Body;

use Saloon\Traits\Body\CreatesStreamFromString;

class JsonBodyRepository extends ArrayBodyRepository
{
    use CreatesStreamFromString;

    /**
     * JSON encoding flags
     *
     * Use a Bitmask to separate other flags. For example: JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
     *
     * @var int
     */
    protected $jsonFlags = 0;

    /**
     * Set the JSON encoding flags
     *
     * Must be a bitmask like: ->setJsonFlags(JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
     *
     * @param int $flags
     *
     * @return $this
     */
    public function setJsonFlags($flags)
    {
        $this->jsonFlags = $flags;

        return $this;
    }

    /**
     * Get the JSON encoding flags
     *
     * @return int
     */
    public function getJsonFlags()
    {
        return $this->jsonFlags;
    }

    /**
     * Convert the body repository into a string.
     *
     * @return string
     */
    public function __toString()
    {
        $json = json_encode($this->all(), $this->getJsonFlags());
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json = false;
        }

        return $json === false ? '' : $json;
    }
}
