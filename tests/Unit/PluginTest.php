<?php

namespace Saloon\Tests\Unit;

use Saloon\Tests\Fixtures\Requests\SubRequest;
use Saloon\Tests\Fixtures\Requests\UserRequestWithBootPlugin;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testAPluginBootMethodHasAccessToTheRequest()
    {
        $request = new UserRequestWithBootPlugin(1, 2);

        $pendingRequest = connector()->createPendingRequest($request);
        $headers = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('X-Plugin-User-Id', $headers);
        $this->assertEquals(1, $headers['X-Plugin-User-Id']);
        $this->assertArrayHasKey('X-Plugin-Group-Id', $headers);
        $this->assertEquals(2, $headers['X-Plugin-Group-Id']);
    }

    public function testSubRequestDoesNotNeedToUsePlugins()
    {
        $request = new SubRequest(1, 2);

        $pendingRequest = connector()->createPendingRequest($request);
        $headers = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('X-Plugin-User-Id', $headers);
        $this->assertEquals(1, $headers['X-Plugin-User-Id']);
        $this->assertArrayHasKey('X-Plugin-Group-Id', $headers);
        $this->assertEquals(2, $headers['X-Plugin-Group-Id']);
    }
}
