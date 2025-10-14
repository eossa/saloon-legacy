<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Data\MultipartValue;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Body\HasMultipartBody;

class HasMultipartBodyConnector extends Connector implements HasBody
{
    use AcceptsJson;
    use HasMultipartBody;

    /**
     * @var bool
     */
    public $unique = false;
    /**
     * @var string|null
     */
    private $url;

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
     * @return array
     */
    protected function defaultBody()
    {
        return [
            new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
            new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
        ];
    }
}
