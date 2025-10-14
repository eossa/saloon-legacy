<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends Connector
{
    use AcceptsJson;

    /**
     * @var string|null
     */
    public $url;

    /**
     * Constructor
     *
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        if (is_null($this->url)) {
            $this->url = apiUrl();
        }
    }

    /**
     * @return string
     */
    public function resolveBaseUrl()
    {
        return $this->url;
    }

    /**
     * @return string[]
     */
    protected function defaultQuery()
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
