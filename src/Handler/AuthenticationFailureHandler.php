<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Status;

/**
 * Default authentication failure handler.
 * Returns 401 Unauthorized response.
 */
final class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(Status::UNAUTHORIZED);
        $response->getBody()->write('Your request was made with invalid credentials.');
        return $response;
    }
}
