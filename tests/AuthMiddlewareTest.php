<?php

namespace Yiisoft\Auth\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Auth\AuthInterface;
use Yiisoft\Auth\Middleware\Auth;
use Yiisoft\Auth\IdentityInterface;
use PHPUnit\Framework\TestCase;

final class AuthMiddlewareTest extends TestCase
{
    private ResponseFactoryInterface $responseFactory;

    /** @var AuthInterface|MockObject */
    private AuthInterface $authenticator;

    protected function setUp(): void
    {
        $this->responseFactory = new Psr17Factory();
        $this->authenticator = $this->createMock(AuthInterface::class);
    }

    public function testShouldAuthenticateAndSetAttribute(): void
    {
        $request = new ServerRequest('GET', '/');
        $identity = $this->createMock(IdentityInterface::class);
        $identityAttribute = 'identity';

        $this->authenticator
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($identity);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function (ServerRequestInterface $request) use ($identityAttribute, $identity) {
                    $this->assertEquals($identity, $request->getAttribute($identityAttribute));

                    return $this->responseFactory->createResponse();
                }
            );

        $auth = new Auth($this->responseFactory, $this->authenticator);
        $auth->setRequestName($identityAttribute);
        $auth->process($request, $handler);
    }

    public function testShouldSkipCheckForOptionalPath(): void
    {
        $path = '/optional';
        $request = new ServerRequest('GET', $path);

        $this->authenticator
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle');

        $auth = new Auth($this->responseFactory, $this->authenticator);
        $auth->setOptional([$path]);
        $auth->process($request, $handler);
    }

    public function testShouldNotExecuteHandlerAndReturn401OnAuthenticationFailure(): void
    {
        $request = new ServerRequest('GET', '/');
        $header = 'Authenticated';
        $headerValue = 'false';

        $this->authenticator
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(null);

        $this->authenticator
            ->expects($this->once())
            ->method('challenge')
            ->willReturnCallback(
                static function (ResponseInterface $response) use ($header, $headerValue) {
                    return $response->withHeader($header, $headerValue);
                }
            );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->never())
            ->method('handle');

        $auth = new Auth($this->responseFactory, $this->authenticator);
        $response = $auth->process($request, $handler);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals($headerValue, $response->getHeaderLine($header));
    }
}
