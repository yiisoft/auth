<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

/**
 * HttpHeader supports HTTP authentication through HTTP Headers.
 *
 * The default implementation of HttpHeader uses the {@see \Yiisoft\Auth\IdentityRepositoryInterface::findIdentityByToken()}
 * and passes the value of the `X-Api-Key` header. This implementation is used mainly for authenticating API clients.
 */
class HttpHeader implements AuthenticationMethodInterface
{
    /**
     * @var string The HTTP header name.
     */
    protected string $headerName = 'X-Api-Key';

    /**
     * @var string A pattern to use to extract the HTTP authentication value.
     */
    protected string $pattern = '/(.*)/';

    protected IdentityRepositoryInterface $identityRepository;

    public function __construct(IdentityRepositoryInterface $identityRepository)
    {
        $this->identityRepository = $identityRepository;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $authToken = $this->getAuthenticationToken($request);
        if ($authToken !== null) {
            return $this->identityRepository->findIdentityByToken($authToken, static::class);
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function withHeaderName(string $name): self
    {
        $new = clone $this;
        $new->headerName = $name;
        return $new;
    }

    /**
     * @param string $pattern A pattern to use to extract the HTTP authentication value.
     * @return self
     */
    public function withPattern(string $pattern): self
    {
        $new = clone $this;
        $new->pattern = $pattern;
        return $new;
    }

    protected function getAuthenticationToken(ServerRequestInterface $request): ?string
    {
        $authHeaders = $request->getHeader($this->headerName);
        $authHeader = \reset($authHeaders);
        if (!empty($authHeader)) {
            if (preg_match($this->pattern, $authHeader, $matches)) {
                $authHeader = $matches[1];
            } else {
                return null;
            }
            return $authHeader;
        }
        return null;
    }
}
