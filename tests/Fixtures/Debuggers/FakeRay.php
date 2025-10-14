<?php

namespace Saloon\Tests\Fixtures\Debuggers;

use Spatie\Ray\Client;
use Spatie\Ray\Request;

class FakeRay extends Client
{
    /**
     * @var array
     */
    protected $sentRequests = [];

    /**
     * @return bool
     */
    public function serverIsAvailable()
    {
        return true;
    }

    /**
     * @return void
     */
    public function send(Request $request)
    {
        $requestProperties = $request->toArray();

        $this->sentRequests[] = $requestProperties;
    }

    /**
     * @return array
     */
    public function getSentRequests()
    {
        return $this->sentRequests;
    }
}
