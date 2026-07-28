<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticates an identity using information available in a request.
 */
interface AuthenticatorInterface
{
    /**
     * @return IdentityInterface|null An identity or null if there is no match.
     */
    public function authenticate(ServerRequestInterface $request): ?IdentityInterface;
}
