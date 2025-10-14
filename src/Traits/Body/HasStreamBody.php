<?php

namespace Saloon\Traits\Body;

use Psr\Http\Message\StreamInterface;
use Saloon\Repositories\Body\StreamBodyRepository;

trait HasStreamBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var StreamBodyRepository
     */
    protected $body;

    /**
     * Retrieve the data repository
     *
     * @return StreamBodyRepository
     */
    public function body()
    {
        if (isset($this->body)) {
            return $this->body;
        }
        return $this->body = new StreamBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return StreamInterface|resource|null
     */
    protected function defaultBody()
    {
        return null;
    }
}
