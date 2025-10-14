<?php

namespace Saloon\Tests\Fixtures\Debuggers;

use Saloon\Debugging\DebugData;
use Saloon\Debugging\Drivers\DebuggingDriver;

class MissingDependencyDebugger extends DebuggingDriver
{

    /**
     * @return string
     */
    public function name()
    {
        return 'missingDependency';
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
        return false;
    }


    /**
     * @return void
     */
    public function send(DebugData $data)
    {
        //
    }
}
