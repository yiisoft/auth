<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Method;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

use function is_string;

/**
 * QueryParameter supports the authentication based on the access token passed through a query parameter.
 */
final class QueryParameter implements AuthenticatorInterface
{
    private string $parameterName = 'access-token';
    private ?string $tokenType = null;

    public function __construct(private IdentityWithTokenRepositoryInterface $identityRepository) {}

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $accessToken = $request->getQueryParams()[$this->parameterName] ?? null;
        if (is_string($accessToken)) {
            return $this->identityRepository->findIdentityByToken($accessToken, $this->tokenType);
        }

        return null;
    }

    /**
     * @param string $name The parameter name for passing the access token.
     *
     * @return $this
     *
     * @psalm-immutable
     */
    public function withParameterName(string $name): self
    {
        $new = clone $this;
        $new->parameterName = $name;
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
}
