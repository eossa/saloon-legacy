<?php

namespace Saloon\Tests\Unit;

use Saloon\Helpers\Storage;
use League\Flysystem\Filesystem;
use Saloon\Exceptions\DirectoryNotFoundException;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    public function testItWillThrowAnExceptionIfTheBaseDirectoryDoesNotExist()
    {
        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage('The directory "example" does not exist or is not a valid directory.');

        new Storage('example');
    }

    public function testYouCanCheckIfAFileExists()
    {
        $storage = new Storage('tests');

        $this->assertTrue($storage->exists('../composer.json'));
        $this->assertFalse($storage->missing('../composer.json'));
    }

    public function testYouCanCheckIfAFileIsMissing()
    {
        $storage = new Storage('tests');

        $this->assertFalse($storage->exists('HelloWorld.php'));
        $this->assertTrue($storage->missing('HelloWorld.php'));
    }

    public function testYouCanRetrieveAFileFromStorage()
    {
        $storage = new Storage('tests');

        $file = $storage->get('helpers.php');

        $this->assertEquals(file_get_contents('tests/helpers.php'), $file);
    }

    public function testYouCanPutAFileInStorage()
    {
        $filesystem = new Filesystem(new Local('tests/Fixtures/Saloon/Testing'));
        $content = $filesystem->listContents('/', true);
        foreach ($content as $file) {
            if ($file['type'] === 'dir') {
                $filesystem->deleteDir($file['path']);
            } elseif ($file['type'] === 'file' && $filesystem->has($file['path'])) {
                $filesystem->delete($file['path']);
            }
        }

        $storage = new Storage('tests/Fixtures/Saloon/Testing');

        $this->assertFalse($storage->exists('example.txt'));

        $storage->put('example.txt', 'Hello World');

        $this->assertTrue($storage->exists('example.txt'));

        $this->assertEquals('Hello World', $storage->get('example.txt'));
    }

    public function testItWillCreateAFileWithNestedFolders()
    {
        $filesystem = new Filesystem(new Local('tests/Fixtures/Saloon/Testing'));
        $content = $filesystem->listContents('/', true);
        foreach ($content as $file) {
            if ($file['type'] === 'dir') {
                $filesystem->deleteDir($file['path']);
            } elseif ($file['type'] === 'file' && $filesystem->has($file['path'])) {
                $filesystem->delete($file['path']);
            }
        }

        $path = 'Testing' . DIRECTORY_SEPARATOR . 'some' . DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'directories' . DIRECTORY_SEPARATOR . 'example.txt';

        $storage = new Storage('tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'Saloon');

        $this->assertFalse($storage->exists($path));

        $storage->put($path, 'Hello World');

        $this->assertTrue($storage->exists($path));

        $this->assertEquals('Hello World', $storage->get($path));
    }

    public function testYouCanGetTheBaseDirectoryPathFromTheStorageClass()
    {
        $storage = new Storage('tests/Fixtures/Saloon');

        $this->assertEquals('tests/Fixtures/Saloon', $storage->getBaseDirectory());
    }
}
