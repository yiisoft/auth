<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use function is_null;

/**
 * HTTP Cookie authentication method.
 *
 * @see https://tools.ietf.org/html/rfc6265
 */
final class HttpCookie implements AuthenticationMethodInterface
{
    private string $cookieName = 'access-token';
    private ?string $tokenType = null;

    public function __construct(
        private IdentityWithTokenRepositoryInterface $identityRepository
    ) {
    }

    public function authenticate(ServerRequestInterface $request): IdentityInterface|null
    {
        $authToken = $this->getAuthenticationToken($request);

        if (is_null($authToken)) {
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
     * @param string|null $type Identity token type
     *
     * @return $this
     *
     * @psalm-immutable
     */
    public function withTokenType(?string $type): self
    {
        $new = clone $this;
        $new->tokenType = $type;
        return $new;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getAuthenticationToken(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();

        if (empty($cookies)) {
            return null;
        }

        return $cookies[$this->cookieName] ?? null;
    }
}
