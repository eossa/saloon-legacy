<?php

namespace Saloon\Tests\Fixtures\Plugins;

use Saloon\Http\PendingRequest;

trait AuthenticatorPlugin
{
    /**
     * @return void
     */
    public function bootAuthenticatorPlugin(PendingRequest $pendingRequest)
    {
        $pendingRequest->withTokenAuth('plugin-auth');
    }
}
