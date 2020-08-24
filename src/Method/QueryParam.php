<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

/**
 * QueryParam supports the authentication based on the access token passed through a query parameter.
 */
final class QueryParam implements AuthenticationMethodInterface
{
    private const TOKEN_PARAMETER_NAME = 'access-token';
    /**
     * @var string the parameter name for passing the access token
     */
    private string $tokenParameterName = self::TOKEN_PARAMETER_NAME;

    private IdentityRepositoryInterface $identityRepository;

    public function __construct(IdentityRepositoryInterface $identityRepository)
    {
        $this->identityRepository = $identityRepository;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $accessToken = $request->getQueryParams()[$this->tokenParameterName] ?? null;
        if (is_string($accessToken)) {
            return $this->identityRepository->findIdentityByToken($accessToken, get_class($this));
        }

        return null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function withTokenParameterName(string $name): self
    {
        $new = clone $this;
        $new->tokenParameterName = $name;
        return $new;
    }
}
