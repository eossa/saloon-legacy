<?php

namespace Saloon\Data;

class Pipe
{
    /**
     * The callable inside the pipe
     *
     * @var callable(mixed $payload): (mixed)
     */
    public $callable;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $order;

    /**
     * Constructor
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @param ?string $name
     * @param ?string $order
     */
    public function __construct(
        callable $callable,
        $name = null,
        $order = null
    ) {
        $this->callable = $callable;
        $this->name = $name;
        $this->order = $order;
    }
}
