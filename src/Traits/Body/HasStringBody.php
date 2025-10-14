<?php

namespace Saloon\Traits\Body;

use Saloon\Repositories\Body\StringBodyRepository;

trait HasStringBody
{
    use ChecksForHasBody;

    /**
     * Body Repository
     *
     * @var StringBodyRepository
     */
    protected $body;

    /**
     * Retrieve the data repository
     *
     * @return StringBodyRepository
     */
    public function body()
    {
        if (isset($this->body)) {
            return $this->body;
        }
        return $this->body = new StringBodyRepository($this->defaultBody());
    }

    /**
     * Default body
     *
     * @return string|null
     */
    protected function defaultBody()
    {
        return null;
    }
}
