<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ResponseInterface;

/**
 * Authenticates an identity using information available in a request and adds an authentication challenge
 * to a response upon failure.
 */
interface AuthenticatorWithChallengeInterface extends AuthenticatorInterface
{
    /**
     * Adds an authentication challenge to the response.
     *
     * @param ResponseInterface $response Response to modify.
     *
     * @return ResponseInterface Modified response.
     */
    public function challenge(ResponseInterface $response): ResponseInterface;
}
