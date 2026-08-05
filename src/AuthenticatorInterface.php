<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticates an identity using information available in a request.
 *
 * Implement this interface only if the authenticator does not need to add a challenge to the response upon
 * authentication failure. Otherwise, implement {@see AuthenticatorWithChallengeInterface} instead.
 */
interface AuthenticatorInterface
{
    /**
     * @return IdentityInterface|null An identity or null if there is no match.
     */
    public function authenticate(ServerRequestInterface $request): ?IdentityInterface;
}
