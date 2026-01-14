<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\AuthenticationFailureHandlerInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Status;

final class AuthenticationMiddlewareTest extends TestCase
{
    private ResponseFactoryInterface $responseFactory;

    /** @var AuthenticationMethodInterface|MockObject */
    private AuthenticationMethodInterface $authenticationMethod;

    protected function setUp(): void
    {
        $this->responseFactory = new Psr17Factory();
        $this->authenticationMethod = $this->createMock(AuthenticationMethodInterface::class);
    }

    public function testShouldAuthenticateAndSetAttribute(): void
    {
        $request = new ServerRequest('GET', '/');
        $identity = $this->createMock(IdentityInterface::class);

        $this->authenticationMethod
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($identity);

        $handler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function (ServerRequestInterface $request) use ($identity) {
                    $this->assertEquals($identity, $request->getAttribute(Authentication::class));

                    return $this->responseFactory->createResponse();
                },
            );

        $auth = new Authentication($this->authenticationMethod, $this->responseFactory);
        $auth->process($request, $handler);
    }

    public static function dataShouldSkipCheckForOptionalPath(): array
    {
        return [
            'ascii' => ['/optional'],
            'utf' => ['/опциональный'],
        ];
    }

    #[DataProvider('dataShouldSkipCheckForOptionalPath')]
    public function testShouldSkipCheckForOptionalPath(string $path): void
    {
        $request = new ServerRequest('GET', $path);

        $this->authenticationMethod
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(null);

        $handler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle');

        $auth = (new Authentication($this->authenticationMethod, $this->responseFactory))
            ->withOptionalPatterns([$path]);
        $auth->process($request, $handler);
    }

    public function testShouldNotExecuteHandlerAndReturn401OnAuthenticationFailure(): void
    {
        $request = new ServerRequest('GET', '/');
        $header = 'Authenticated';
        $headerValue = 'false';

        $this->authenticationMethod
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(null);

        $this->authenticationMethod
            ->expects($this->once())
            ->method('challenge')
            ->willReturnCallback(
                static fn(ResponseInterface $response) => $response->withHeader($header, $headerValue),
            );

        $handler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $handler
            ->expects($this->never())
            ->method('handle');

        $auth = new Authentication($this->authenticationMethod, $this->responseFactory);
        $response = $auth->process($request, $handler);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals($headerValue, $response->getHeaderLine($header));
    }

    public function testCustomAuthenticationFailureResponse(): void
    {
        $request = new ServerRequest('GET', '/');
        $header = 'Authenticated';
        $headerValue = 'false';

        $this->authenticationMethod
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(null);

        $this->authenticationMethod
            ->expects($this->once())
            ->method('challenge')
            ->willReturnCallback(
                static fn(ResponseInterface $response) => $response->withHeader($header, $headerValue),
            );

        $handler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $handler
            ->expects($this->never())
            ->method('handle');

        $failureResponse = 'test custom response';

        $auth = new Authentication(
            $this->authenticationMethod,
            $this->responseFactory,
            $this->createAuthenticationFailureHandler($failureResponse),
        );
        $response = $auth->process($request, $handler);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals($headerValue, $response->getHeaderLine($header));
        $this->assertEquals($failureResponse, (string) $response->getBody());
    }

    public function testImmutability(): void
    {
        $original = new Authentication(
            $this->authenticationMethod,
            $this->responseFactory,
        );

        $this->assertNotSame($original, $original->withOptionalPatterns(['test']));
    }

    private function createAuthenticationFailureHandler(string $failureResponse): AuthenticationFailureHandlerInterface
    {
        return new class ($failureResponse, new Psr17Factory()) implements AuthenticationFailureHandlerInterface {
            public function __construct(
                private readonly string $failureResponse,
                private readonly ResponseFactoryInterface $responseFactory,
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = $this->responseFactory->createResponse(Status::UNAUTHORIZED);
                $response
                    ->getBody()
                    ->write($this->failureResponse);
                return $response;
            }
        };
    }
}
