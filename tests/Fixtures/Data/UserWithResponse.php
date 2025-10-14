<?php

namespace Saloon\Tests\Fixtures\Data;

use Exception;
use Saloon\Http\Response;
use Saloon\Traits\Responses\HasResponse;
use Saloon\Contracts\DataObjects\WithResponse;

class UserWithResponse implements WithResponse
{
    use HasResponse;

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
    public static function fromResponse(Response $response)
    {
        $data = $response->json();

        return new static($data['name'], $data['actual_name'], $data['twitter']);
    }
}
