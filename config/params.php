<?php

declare(strict_types=1);

use Yiisoft\Auth\AuthenticatorWithChallengeInterface;
use Yiisoft\Auth\Debug\AuthenticatorWithChallengeInterfaceProxy;
use Yiisoft\Auth\Debug\IdentityCollector;

return [
    'yiisoft/yii-debug' => [
        'collectors.web' => [
            IdentityCollector::class,
        ],
        'trackedServices' => [
            AuthenticatorWithChallengeInterface::class => [AuthenticatorWithChallengeInterfaceProxy::class, IdentityCollector::class],
        ],
    ],
];
