<?php

namespace Saloon\Data;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class MultipartValue
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int|StreamInterface|resource|string
     */
    public $value;

    /**
     * @var string|null
     */
    public $filename;

    /**
     * @var mixed[]
     */
    public $headers;

    /**
     * Constructor
     *
     * @param string $name
     * @param StreamInterface|resource|string|int $value
     * @param ?string $filename
     * @param array<string, mixed> $headers
     */
    public function __construct(
        $name,
        $value,
        $filename = null,
        array $headers = []
    ) {
        if (! $value instanceof StreamInterface && ! is_resource($value) && ! is_string($value) && ! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('The value property must be either a %s, resource, string or numeric.', StreamInterface::class));
        }
        $this->name = $name;
        $this->value = $value;
        $this->filename = $filename;
        $this->headers = $headers;
    }
}
