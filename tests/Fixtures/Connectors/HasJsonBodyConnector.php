<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class HasJsonBodyConnector extends Connector implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;

    /**
     * @var bool
     */
    public $unique = false;
    /**
     * @var string|null
     */
    protected $url;

    /**
     * Constructor
     *
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return isset($this->url) ? $this->url : apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    protected function defaultHeaders()
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultBody()
    {
        return [
            'name' => 'Gareth',
            'drink' => 'Moonshine',
        ];
    }
}
