<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;

/**
 * Composite allows multiple authentication methods at the same time.
 *
 * The authentication methods contained by Composite are configured via {@see setAuthMethods()},
 * which is a list of supported authentication class configurations.
 */
final class Composite implements AuthenticationMethodInterface
{
    /**
     * @var AuthenticationMethodInterface[]
     */
    private array $authenticationMethods;

    public function __construct(array $methods)
    {
        $this->authenticationMethods = $methods;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        foreach ($this->authenticationMethods as $i => $authenticationMethod) {
            $identity = $authenticationMethod->authenticate($request);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->authenticationMethods as $method) {
            $response = $method->challenge($response);
        }
        return $response;
    }
}
