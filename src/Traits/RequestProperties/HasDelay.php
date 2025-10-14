<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\IntegerStore;

trait HasDelay
{
    /**
     * Request Delay
     *
     * @var IntegerStore
     */
    protected $delay;

    /**
     * Delay repository
     *
     * @return IntegerStore
     */
    public function delay()
    {
        if (isset($this->delay)) {
            return $this->delay;
        }
        return $this->delay = new IntegerStore($this->defaultDelay());
    }

    /**
     * Default Delay
     *
     * @return int|null
     */
    protected function defaultDelay()
    {
        return null;
    }
}
