<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Senders\GuzzleSender;
use Saloon\Tests\Fixtures\Senders\ArraySender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\ArraySenderConnector;
use Saloon\Tests\Fixtures\Connectors\ArraySenderDefaultMethodConnector;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    public function testTheDefaultSenderOnAllConnectorsIsTheGuzzleSender()
    {
        $connector = new TestConnector();
        $sender = $connector->sender();

        $this->assertInstanceOf(GuzzleSender::class, $sender);

        // Test the same instance is re-used
        $this->assertSame($sender, $connector->sender());
    }

    public function testYouCanOverwriteTheSenderOnAConnectorUsingTheProperty()
    {
        $connector = new ArraySenderConnector();
        $sender = $connector->sender();

        $this->assertInstanceOf(ArraySender::class, $sender);
        $this->assertSame($sender, $connector->sender());

        // Test using the connector with the custom sender
        $request = new UserRequest();
        $response = $connector->send($request);

        $this->assertEquals(['X-Fake' => true], $response->headers()->all());
        $this->assertEquals('Default', $response->body());
    }

    public function testYouCanOverwriteTheSenderOnAConnectorUsingTheDefaultSenderMethod()
    {
        $connector = new ArraySenderDefaultMethodConnector();
        $sender = $connector->sender();

        $this->assertInstanceOf(ArraySender::class, $sender);
        $this->assertSame($sender, $connector->sender());

        // Test using the connector with the custom sender
        $request = new UserRequest();
        $response = $connector->send($request);

        $this->assertEquals(['X-Fake' => true], $response->headers()->all());
        $this->assertEquals('Default', $response->body());
    }

    public function testItWillThrowAnExceptionIfTheSenderDoesNotImplementTheSenderInterface()
    {
        $connector = new ArraySenderConnector();
        $connector->setDefaultSender(UserRequest::class);

        $this->markTestSkipped('In PHP 5.6, TypeError doesn\'t exist');
        $connector->sender();
    }
}
