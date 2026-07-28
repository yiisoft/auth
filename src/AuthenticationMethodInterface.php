<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

/**
 * @deprecated Implement {@see AuthenticatorInterface} and, if a challenge is needed, {@see ChallengeInterface}.
 */
interface AuthenticationMethodInterface extends AuthenticatorInterface, ChallengeInterface {}
