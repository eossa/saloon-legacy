<?php

namespace Saloon\Traits\Body;

use Exception;
use Saloon\Data\MultipartValue;
use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\MultipartBodyRepository;

trait HasMultipartBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var MultipartBodyRepository
     */
    protected $body;

    /**
     * Boot the HasMultipartBody trait
     *
     * @return void
     *
     * @throws Exception
     */
    public function bootHasMultipartBody(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Content-Type', 'multipart/form-data; boundary=' . $this->body()->getBoundary());
    }

    /**
     * Retrieve the data repository
     *
     * @return MultipartBodyRepository
     *
     * @throws Exception
     */
    public function body()
    {
        if (isset($this->body)) {
            return $this->body;
        }
        return $this->body = new MultipartBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return array<MultipartValue>
     */
    protected function defaultBody()
    {
        return [];
    }
}
