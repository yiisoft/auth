<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ServerRequestInterface;

/**
 * The interface that should be implemented by individual authentication methods.
 */
interface AuthenticatorInterface
{
    /**
     * Authenticates the identity based on information available from request.
     *
     * @param ServerRequestInterface $request Request to get identity information from.
     *
     * @return IdentityInterface|null An instance of identity or null if there is no match.
     */
    public function authenticate(ServerRequestInterface $request): ?IdentityInterface;
}
