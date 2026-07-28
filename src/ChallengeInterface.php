<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ResponseInterface;

/**
 * Adds an authentication challenge to a response.
 */
interface ChallengeInterface
{
    /**
     * For example, an implementation may add appropriate HTTP headers.
     */
    public function challenge(ResponseInterface $response): ResponseInterface;
}
