<?php

namespace Saloon\Tests\Fixtures\Data;

use Exception;
use Saloon\Http\Response;

class ApiResponse
{
    /**
     * @var array
     */
    public $data;

    /**
     * @param array $data
     */
    public function __construct(
        array $data
    ) {
        $this->data = $data;
    }

    /**
     * @return static
     *
     * @throws Exception
     */
    public static function fromSaloon(Response $response)
    {
        return new static($response->json());
    }
}
