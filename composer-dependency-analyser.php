<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    ->ignoreErrorsOnPackages(['jetbrains/phpstorm-attributes', 'yiisoft/yii-debug'], [ErrorType::DEV_DEPENDENCY_IN_PROD]);

if (PHP_VERSION_ID < 80200) {
    $config->ignoreUnknownClasses(['SensitiveParameter']);
}

return $config;
