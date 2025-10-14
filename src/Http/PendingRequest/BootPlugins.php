<?php

namespace Saloon\Http\PendingRequest;

use ReflectionException;
use Saloon\Helpers\Helpers;
use Saloon\Http\PendingRequest;

class BootPlugins
{
    /**
     * Boot the plugins
     *
     * @return PendingRequest
     *
     * @throws ReflectionException
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $connectorTraits = Helpers::classUsesRecursive($connector);
        $requestTraits = Helpers::classUsesRecursive($request);

        foreach ($connectorTraits as $connectorTrait) {
            Helpers::bootPlugin($pendingRequest, $connector, $connectorTrait);
        }

        foreach ($requestTraits as $requestTrait) {
            Helpers::bootPlugin($pendingRequest, $request, $requestTrait);
        }

        return $pendingRequest;
    }
}
