<?php

namespace Saloon\Exceptions;

class UnableToCreateDirectoryException extends SaloonException
{
    /**
     * Constructor
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        parent::__construct(sprintf('Unable to create the directory: %s.', $directory));
    }
}
