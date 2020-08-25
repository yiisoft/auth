<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

/**
 * QueryParameter supports the authentication based on the access token passed through a query parameter.
 */
final class QueryParameter implements AuthenticationMethodInterface
{
    private string $parameterName = 'access-token';

    private IdentityRepositoryInterface $identityRepository;

    public function __construct(IdentityRepositoryInterface $identityRepository)
    {
        $this->identityRepository = $identityRepository;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $accessToken = $request->getQueryParams()[$this->parameterName] ?? null;
        if (\is_string($accessToken)) {
            return $this->identityRepository->findIdentityByToken($accessToken, self::class);
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @param string $name The parameter name for passing the access token.
     * @return self
     */
    public function withParameterName(string $name): self
    {
        $new = clone $this;
        $new->parameterName = $name;
        return $new;
    }
}
