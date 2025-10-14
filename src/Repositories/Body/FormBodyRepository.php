<?php

namespace Saloon\Repositories\Body;

use Saloon\Traits\Body\CreatesStreamFromString;

class FormBodyRepository extends ArrayBodyRepository
{
    use CreatesStreamFromString;

    /**
     * Convert into a string.
     *
     * @return string
     */
    public function __toString()
    {
        return http_build_query($this->all());
    }
}
