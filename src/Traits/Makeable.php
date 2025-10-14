<?php

namespace Saloon\Traits;

trait Makeable
{
    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     *
     * @return Makeable
     */
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }
}
