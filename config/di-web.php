<?php

declare(strict_types=1);

use Yiisoft\Auth\AuthenticationFailureHandler;
use Yiisoft\Auth\AuthenticationFailureHandlerInterface;

/**
 * @var array $params
 */

return [
    AuthenticationFailureHandlerInterface::class => AuthenticationFailureHandler::class,
];
