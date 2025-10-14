<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class TestConnector extends Connector
{
    use AcceptsJson;

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
}
