<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Auth\AuthInterface;
use Yiisoft\Auth\Handler\AuthenticationFailureHandler;
use Yiisoft\Strings\StringHelper;

final class Auth implements MiddlewareInterface
{
    public const REQUEST_NAME = 'auth_user';

    private string $requestName = self::REQUEST_NAME;
    private AuthInterface $authenticator;
    private RequestHandlerInterface $authenticationFailureHandler;
    private array $optional = [];

    public function __construct(
        AuthInterface $authenticator,
        ResponseFactoryInterface $responseFactory,
        RequestHandlerInterface $authenticationFailureHandler = null
    ) {
        $this->authenticator = $authenticator;
        $this->authenticationFailureHandler = $authenticationFailureHandler ?? new AuthenticationFailureHandler(
            $responseFactory
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $this->authenticator->authenticate($request);
        $request = $request->withAttribute($this->requestName, $identity);

        if ($identity === null && !$this->isOptional($request)) {
            return $this->authenticator->challenge(
                $this->authenticationFailureHandler->handle($request)
            );
        }

        return $handler->handle($request);
    }

    public function setRequestName(string $name): void
    {
        $this->requestName = $name;
    }

    public function setOptional(array $optional): void
    {
        $this->optional = $optional;
    }

    /**
     * Checks, whether authentication is optional for the given action.
     */
    private function isOptional(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        foreach ($this->optional as $pattern) {
            if (StringHelper::matchWildcard($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
