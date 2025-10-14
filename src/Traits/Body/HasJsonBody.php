<?php

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\JsonBodyRepository;

trait HasJsonBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var JsonBodyRepository
     */
    protected $body;

    /**
     * Boot the plugin
     *
     * @return void
     */
    public function bootHasJsonBody(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Content-Type', 'application/json');
    }

    /**
     * Retrieve the data repository
     *
     * @return JsonBodyRepository
     */
    public function body()
    {
        if (isset($this->body)) {
            return $this->body;
        }
        return $this->body = new JsonBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return array<string, mixed>
     */
    protected function defaultBody()
    {
        return [];
    }
}
