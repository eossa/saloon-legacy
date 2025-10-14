<?php

namespace Saloon\Traits\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresAuth
{
    /**
     * Throw an exception if an authenticator is not on the request while it is booting.
     *
     * @return void
     *
     * @throws MissingAuthenticatorException
     */
    public function bootRequiresAuth(PendingRequest $pendingSaloonRequest)
    {
        $authenticator = $pendingSaloonRequest->getAuthenticator();

        if (! $authenticator instanceof Authenticator) {
            throw new MissingAuthenticatorException($this->getRequiresAuthMessage($pendingSaloonRequest));
        }
    }

    /**
     * Default message.
     *
     * @return string
     */
    protected function getRequiresAuthMessage(PendingRequest $pendingRequest)
    {
        return sprintf('The "%s" request requires authentication.', get_class($pendingRequest->getRequest()));
    }
}
