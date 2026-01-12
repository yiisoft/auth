<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

/**
 * HTTP cookie authentication method.
 *
 * @see https://tools.ietf.org/html/rfc6265
 */
final class HttpCookie implements AuthenticationMethodInterface
{
    private string $cookieName = 'access-token';
    private ?string $tokenType = null;

    public function __construct(
        private IdentityWithTokenRepositoryInterface $identityRepository,
    ) {}

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $authToken = $this->getAuthenticationToken($request);

        if ($authToken === null) {
            return null;
        }

        return $this->identityRepository->findIdentityByToken($authToken, $this->tokenType);
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @psalm-immutable
     */
    public function withCookieName(string $cookieName): self
    {
        $new = clone $this;
        $new->cookieName = $cookieName;
        return $new;
    }

    /**
     * @psalm-immutable
     */
    public function withTokenType(?string $type): self
    {
        $new = clone $this;
        $new->tokenType = $type;
        return $new;
    }

    private function getAuthenticationToken(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();

        return $cookies[$this->cookieName] ?? null;
    }
}
