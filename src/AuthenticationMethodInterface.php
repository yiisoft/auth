<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

/**
 * @deprecated Implement {@see AuthenticatorInterface} or, if a challenge is needed,
 * {@see AuthenticatorWithChallengeInterface}.
 */
interface AuthenticationMethodInterface extends AuthenticatorWithChallengeInterface {}
