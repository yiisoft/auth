<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

/**
 * @deprecated Use AuthenticatorInterface and optionally ChallengeInterface.
 */
interface AuthenticationMethodInterface extends AuthenticatorInterface, ChallengeInterface
{
}
