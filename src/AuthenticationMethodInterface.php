<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles authentication failure.
 * This is not a PSR-15 request handler.
 */
interface AuthenticationFailureHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
