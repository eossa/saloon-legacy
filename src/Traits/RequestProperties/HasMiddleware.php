<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Helpers\MiddlewarePipeline;

trait HasMiddleware
{
    /**
     * Middleware Pipeline
     *
     * @var MiddlewarePipeline
     */
    protected $middlewarePipeline;

    /**
     * Access the middleware pipeline
     *
     * @return MiddlewarePipeline
     */
    public function middleware()
    {
        if (isset($this->middlewarePipeline)) {
            return $this->middlewarePipeline;
        }
        return $this->middlewarePipeline = new MiddlewarePipeline();
    }
}
