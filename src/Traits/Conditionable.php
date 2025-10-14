<?php

namespace Saloon\Traits;

use Closure;
use Saloon\Helpers\Helpers;

trait Conditionable
{
    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @param Closure(): (mixed)|mixed $value
     * @param callable($this, mixed): (void) $callback
     * @param callable($this, mixed): (void)|null $default
     * @return $this
     */
    public function when($value, callable $callback, callable $default = null)
    {
        $value = Helpers::value($value, $this);

        if ($value) {
            $callback($this, $value);

            return $this;
        }

        if ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @param Closure(): (mixed)|mixed $value
     * @param callable($this, mixed): (void) $callback
     * @param callable($this, mixed): (void)|null $default
     * @return $this
     */
    public function unless($value, callable $callback, callable $default = null)
    {
        $value = Helpers::value($value, $this);

        if (! $value) {
            $callback($this, $value);

            return $this;
        }

        if ($default) {
            $default($this, $value);
        }

        return $this;
    }
}
