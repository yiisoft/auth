<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Auth\Handler\AuthenticationFailureHandlerInterface;
use Yiisoft\Auth\Handler\AuthenticationFailureHandler;
use Yiisoft\Strings\WildcardPattern;

/**
 * Authentication middleware tries to authenticate identity using request data.
 * If identity is found, it is set to request attribute.
 * If identity is not found, failure handler is called.
 */
final class Authentication implements MiddlewareInterface
{
    /**
     * Handler called when authentication fails.
     */
    private AuthenticationFailureHandlerInterface $failureHandler;

    /**
     * @var array Patterns to match to consider the given request URI path optional.
     */
    private array $optionalPatterns = [];

    /**
     * @var WildcardPattern[]
     */
    private array $wildcards = [];

    public function __construct(
        private AuthenticationMethodInterface $authenticationMethod,
        ResponseFactoryInterface $responseFactory,
        ?AuthenticationFailureHandlerInterface $authenticationFailureHandler = null,
    ) {
        $this->failureHandler = $authenticationFailureHandler ?? new AuthenticationFailureHandler(
            $responseFactory,
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $this->authenticationMethod->authenticate($request);
        $request = $request->withAttribute(self::class, $identity);

        if ($identity === null && !$this->isOptional($request)) {
            return $this->authenticationMethod->challenge(
                $this->failureHandler->handle($request),
            );
        }

        return $handler->handle($request);
    }

    public function withOptionalPatterns(array $optional): self
    {
        $new = clone $this;
        $new->optionalPatterns = $optional;
        return $new;
    }

    private function isOptional(ServerRequestInterface $request): bool
    {
        $path = rawurldecode($request->getUri()->getPath());

        foreach ($this->optionalPatterns as $pattern) {
            if ($this->getOptionalPattern($pattern)->match($path)) {
                return true;
            }
        }

        return false;
    }

    private function getOptionalPattern(string $pattern): WildcardPattern
    {
        if (!isset($this->wildcards[$pattern])) {
            $this->wildcards[$pattern] = new WildcardPattern($pattern);
        }

        return $this->wildcards[$pattern];
    }
}
