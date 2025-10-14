<?php

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\FormBodyRepository;

trait HasFormBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var FormBodyRepository
     */
    protected $body;

    /**
     * Boot the HasFormBody trait
     *
     * @return void
     */
    public function bootHasFormBody(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Content-Type', 'application/x-www-form-urlencoded');
    }

    /**
     * Retrieve the data repository
     *
     * @return FormBodyRepository
     */
    public function body()
    {
        if (isset($this->body)) {
            return $this->body;
        }
        return $this->body = new FormBodyRepository($this->defaultBody());
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
