<?php

namespace Saloon\Tests\Fixtures\Data;

use Exception;
use Saloon\Http\Response;

class User
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $actualName;

    /**
     * @var string
     */
    public $twitter;

    /**
     * @param string $name
     * @param string $actualName
     * @param string $twitter
     */
    public function __construct(
        $name,
        $actualName,
        $twitter
    ) {
        $this->name = $name;
        $this->actualName = $actualName;
        $this->twitter = $twitter;
    }

    /**
     * @return static
     *
     * @throws Exception
     */
    public static function fromSaloon(Response $response)
    {
        $data = $response->json();

        return new static($data['name'], $data['actual_name'], $data['twitter']);
    }
}
