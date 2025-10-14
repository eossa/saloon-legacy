<?php

namespace Saloon\Helpers;

use Saloon\Exceptions\DirectoryNotFoundException;
use Saloon\Exceptions\UnableToCreateFileException;
use Saloon\Exceptions\UnableToCreateDirectoryException;

/**
 * @internal
 */
class Storage
{
    /**
     * The base directory to access the files.
     *
     * @var string
     */
    protected $baseDirectory;

    /**
     * Constructor
     *
     * @param string $baseDirectory
     * @param bool $createMissingBaseDirectory
     *
     * @throws DirectoryNotFoundException
     * @throws UnableToCreateDirectoryException
     */
    public function __construct($baseDirectory, $createMissingBaseDirectory = false)
    {
        if (! is_dir($baseDirectory)) {
            if ($createMissingBaseDirectory) {
                $this->createDirectory($baseDirectory);
            } else {
                throw new DirectoryNotFoundException($baseDirectory);
            }
        }

        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Get the base directory
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Combine the base directory with a path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function buildPath($path)
    {
        $trimRules = DIRECTORY_SEPARATOR . ' ';

        return rtrim($this->baseDirectory, $trimRules) . DIRECTORY_SEPARATOR . ltrim($path, $trimRules);
    }

    /**
     * Check if the file exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($this->buildPath($path));
    }

    /**
     * Check if the file is missing
     *
     * @param string $path
     *
     * @return bool
     */
    public function missing($path)
    {
        return ! $this->exists($path);
    }

    /**
     * Retrieve an item from storage
     *
     * @param string $path
     *
     * @return bool|string
     */
    public function get($path)
    {
        return file_get_contents($this->buildPath($path));
    }

    /**
     * Put an item in storage
     *
     * @param string $path
     * @param string $contents
     *
     * @return $this
     *
     * @throws UnableToCreateDirectoryException
     * @throws UnableToCreateFileException
     */
    public function put($path, $contents)
    {
        $fullPath = $this->buildPath($path);

        $directoryWithoutFilename = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR, $fullPath, -1));

        if (empty($directoryWithoutFilename) === false && is_dir($directoryWithoutFilename) === false) {
            $this->createDirectory($directoryWithoutFilename);
        }

        $createdFile = file_put_contents($fullPath, $contents);

        if ($createdFile === false) {
            throw new UnableToCreateFileException($fullPath);
        }

        return $this;
    }

    /**
     * Create a directory
     *
     * @param string $directory
     *
     * @return bool
     *
     * @throws UnableToCreateDirectoryException
     */
    public function createDirectory($directory)
    {
        $createdDirectory = mkdir($directory, 0777, true);

        if ($createdDirectory === false && is_dir($directory) === false) {
            throw new UnableToCreateDirectoryException($directory);
        }

        return true;
    }
}
