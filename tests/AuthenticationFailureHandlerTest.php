<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests;


use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Auth\Handler\AuthenticationFailureHandler;
use PHPUnit\Framework\TestCase;

final class AuthenticationFailureHandlerTest extends TestCase
{
    public function testShouldReturnCode401(): void
    {
        $response = $this->createHandler()->handle($this->createRequest());
        $this->assertEquals(Status::UNAUTHORIZED, $response->getStatusCode());
    }

    public function testShouldReturnCorrectErrorInBody(): void
    {
        $response = $this->createHandler()->handle($this->createRequest());
        $this->assertEquals('Your request was made with invalid credentials.', (string)$response->getBody());
    }

    private function createHandler(): AuthenticationFailureHandler
    {
        return new AuthenticationFailureHandler(new Psr17Factory());
    }

    private function createRequest(string $uri = '/'): ServerRequestInterface
    {
        return new ServerRequest(Method::GET, $uri);
    }
}
