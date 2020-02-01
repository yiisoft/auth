<?php

namespace Yiisoft\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * AuthInterface is the interface that should be implemented by auth method classes.
 */
interface AuthInterface
{
    /**
     * Authenticates the current user.
     * @param ServerRequestInterface $request
     * @return null|IdentityInterface
     */
    public function authenticate(ServerRequestInterface $request): ?IdentityInterface;

    /**
     * Generates challenges upon authentication failure.
     * For example, some appropriate HTTP headers may be generated.
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function challenge(ResponseInterface $response): ResponseInterface;
}
