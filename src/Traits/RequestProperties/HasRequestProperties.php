<?php

namespace Saloon\Traits\RequestProperties;

trait HasRequestProperties
{
    use HasHeaders;
    use HasQuery;
    use HasConfig;
    use HasMiddleware;
    use HasDelay;
}
