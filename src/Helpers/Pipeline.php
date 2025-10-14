<?php

namespace Saloon\Helpers;

use Saloon\Data\Pipe;
use Saloon\Enums\PipeOrder;
use Saloon\Exceptions\DuplicatePipeNameException;

class Pipeline
{
    /**
     * The pipes in the pipeline.
     *
     * @var array<Pipe>
     */
    protected $pipes = [];

    /**
     * Add a pipe to the pipeline
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @param ?string $name
     * @param ?string $order
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function pipe(callable $callable, $name = null, $order = null)
    {
        $pipe = new Pipe($callable, $name, $order);

        if (is_string($name) && $this->pipeExists($name)) {
            throw new DuplicatePipeNameException($name);
        }

        $this->pipes[] = $pipe;

        return $this;
    }

    /**
     * Process the pipeline.
     *
     * @param mixed $payload
     *
     * @return mixed
     */
    public function process($payload)
    {
        foreach ($this->sortPipes() as $pipe) {
            $payload = call_user_func($pipe->callable, $payload);
        }

        return $payload;
    }

    /**
     * Sort the pipes based on the "order" classes
     *
     * @return array<Pipe>
     */
    protected function sortPipes()
    {
        $firstPipes = [];
        $nullPipes = [];
        $lastPipes = [];

        // We'll simply loop through each pipe and add them to their respective
        // arrays based on the order type. We'll then merge the arrays.

        foreach ($this->pipes as $pipe) {
            switch ($pipe->order) {
                case PipeOrder::FIRST:
                    $firstPipes[] = $pipe;
                    break;
                case null:
                    $nullPipes[] = $pipe;
                    break;
                case PipeOrder::LAST:
                    $lastPipes[] = $pipe;
                    break;
            }
        }

        return array_merge($firstPipes, $nullPipes, $lastPipes);
    }

    /**
     * Set the pipes on the pipeline.
     *
     * @param array<Pipe> $pipes
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function setPipes(array $pipes)
    {
        $this->pipes = [];

        // Loop through each of the pipes and manually add each pipe
        // so we can check if the name already exists.

        foreach ($pipes as $pipe) {
            $this->pipe($pipe->callable, $pipe->name, $pipe->order);
        }

        return $this;
    }

    /**
     * Get all the pipes in the pipeline
     *
     * @return array<Pipe>
     */
    public function getPipes()
    {
        return $this->pipes;
    }

    /**
     * Check if a given pipe exists for a name
     *
     * @param string $name
     *
     * @return bool
     */
    protected function pipeExists($name)
    {
        foreach ($this->pipes as $pipe) {
            if ($pipe->name === $name) {
                return true;
            }
        }

        return false;
    }
}
