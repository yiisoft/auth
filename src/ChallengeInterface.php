<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

use Psr\Http\Message\ResponseInterface;

/**
 * The interface that should be implemented by response upon authentication failure.
 */
interface ChallengeInterface
{
    /**
     * Adds challenge to response upon authentication failure.
     * For example, some appropriate HTTP headers may be added.
     *
     * @param ResponseInterface $response Response to modify.
     *
     * @return ResponseInterface Modified response.
     */
    public function challenge(ResponseInterface $response): ResponseInterface;
}
