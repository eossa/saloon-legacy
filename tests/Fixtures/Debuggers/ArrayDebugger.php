<?php

namespace Saloon\Tests\Fixtures\Debuggers;

use Saloon\Debugging\DebugData;
use Saloon\Debugging\Drivers\DebuggingDriver;

class ArrayDebugger extends DebuggingDriver
{

    /**
     * @var array
     */
    protected $requests = [];


    /**
     * @var array
     */
    protected $responses = [];


    /**
     * @return string
     */
    public function name()
    {
        return 'array';
    }


    /**
     * @param DebugData $data
     * @return void
     */
    public function send(DebugData $data)
    {
        if ($data->wasNotSent()) {
            $this->requests[] = $this->formatData($data);
        }

        if ($data->wasSent()) {
            $this->responses[] = $this->formatData($data);
        }
    }

    /**
     * Get request
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Get response
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Determines if the debugging driver can be used
     *
     * E.g if it has the correct dependencies
     *
     * @return bool
     */
    public function hasDependencies()
    {
        return true;
    }
}
