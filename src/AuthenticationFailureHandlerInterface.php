<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * `AuthenticationFailureHandlerInterface` produces a response when there is a failure authenticating an identity.
 */
interface AuthenticationFailureHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
