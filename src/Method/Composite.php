<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\AuthenticatorWithChallengeInterface;
use Yiisoft\Auth\IdentityInterface;
use RuntimeException;

/**
 * Composite allows multiple authentication methods at the same time.
 *
 * @psalm-suppress DeprecatedInterface
 */
final class Composite implements AuthenticationMethodInterface, AuthenticatorWithChallengeInterface
{
    /**
     * @param AuthenticatorInterface[] $methods
     */
    public function __construct(
        private readonly array $methods,
    ) {}

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        foreach ($this->methods as $method) {
            if (!$method instanceof AuthenticatorInterface) {
                throw new RuntimeException('Authentication method must be an instance of ' . AuthenticatorInterface::class . '.');
            }

            $identity = $method->authenticate($request);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->methods as $method) {
            if ($method instanceof AuthenticatorWithChallengeInterface) {
                $response = $method->challenge($response);
            }
        }
        return $response;
    }
}
