<?php

namespace Saloon\Tests\Unit;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Auth\NullAuthenticator;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\ArraySenderConnector;
use Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;

class AuthenticatesRequestsTest extends TestCase
{
    public function testYouCanAddBasicAuthToARequest()
    {
        $request = new UserRequest();
        $request->withBasicAuth('Sammyjo20', 'Cowboy1');

        $pendingRequest = connector()->createPendingRequest($request);
        $headers = $pendingRequest->headers()->all();

        $this->assertInternalType('array', $headers);
        $this->assertEquals('Basic ' . base64_encode('Sammyjo20:Cowboy1'), $headers['Authorization']);
    }

    public function testYouCanAttachAnAuthorizationTokenToARequest()
    {
        $request = UserRequest::make()->withTokenAuth('Sammyjo20');

        $pendingRequest = connector()->createPendingRequest($request);
        $headers = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('Bearer Sammyjo20', $headers['Authorization']);
    }

    public function testYouCanAddDigestAuthToARequest()
    {
        $this->expectException(SaloonException::class);
        $this->expectExceptionMessage('The DigestAuthenticator is only supported when using the GuzzleSender.');

        $request = new UserRequest();
        $request->withDigestAuth('Sammyjo20', 'Cowboy1', 'Howdy');

        $pendingRequest = connector()->createPendingRequest($request);
        $config = $pendingRequest->config()->all();

        $this->assertInternalType('array', $config['auth']);
        $this->assertEquals('Sammyjo20', $config['auth'][0]);
        $this->assertEquals('Cowboy1', $config['auth'][1]);
        $this->assertEquals('Howdy', $config['auth'][2]);

        // We'll now test trying to use the `withDigestAuth` on the array sender
        $arraySenderConnector = new ArraySenderConnector();
        $arraySenderConnector->send($request);
    }

    public function testYouCanAddATokenToAQueryParameter()
    {
        $request = UserRequest::make()->withQueryAuth('token', 'Sammyjo20');

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertArrayHasKey('token', $query);
        $this->assertEquals('Sammyjo20', $query['token']);
    }

    public function testYouCanAddAHeaderToARequest()
    {
        $request = UserRequest::make()->withHeaderAuth('Sammyjo20', 'X-Authorization');

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('X-Authorization', $query);
        $this->assertEquals('Sammyjo20', $query['X-Authorization']);
    }

    public function testYouCanAddACertificateToARequest()
    {
        $certPath = __DIR__ . '/certificate.cer';

        $requestA = UserRequest::make()->withCertificateAuth($certPath);

        $pendingRequestA = connector()->createPendingRequest($requestA);
        $configA = $pendingRequestA->config()->all();

        $this->assertEquals([
            RequestOptions::CERT => $certPath,
        ], $configA);

        // Test with password
        $requestB = UserRequest::make()->withCertificateAuth($certPath, 'example');

        $pendingRequestB = connector()->createPendingRequest($requestB);
        $configB = $pendingRequestB->config()->all();

        $this->assertEquals([
            RequestOptions::CERT => [$certPath, 'example'],
        ], $configB);
    }

    public function testYouCanUseMultipleAuthenticatorsAtTheSameTimeUsingTheDefaultAuthMethod()
    {
        $request = UserRequest::make()->authenticate(new MultiAuthenticator(
            new TokenAuthenticator('example'),
            new HeaderAuthenticator('api-key', 'X-API-Key')
        ));

        $pendingRequest = connector()->createPendingRequest($request);

        $headers = $pendingRequest->headers()->all();

        $this->assertEquals([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer example',
            'X-API-Key' => 'api-key',
        ], $headers);
    }

    public function testYouCanUseANullAuthenticatorToDisableDefaultAuthenticationEntirely()
    {
        $connector = new DefaultAuthenticatorConnector();
        $request = new UserRequest();

        $request->authenticate(new NullAuthenticator());

        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals(['Accept' => 'application/json'], $pendingRequest->headers()->all());
    }
}
