<?php

declare(strict_types=1);

namespace Yiisoft\Request\Body\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Auth\AuthenticationFailureHandler;
use Yiisoft\Auth\AuthenticationFailureHandlerInterface;

use function dirname;

final class ConfigTest extends TestCase
{
    public function testAuthenticationFailureHandler(): void
    {
        $container = $this->createContainer();

        $failureHandler = $container->get(AuthenticationFailureHandlerInterface::class);
        $this->assertInstanceOf(AuthenticationFailureHandler::class, $failureHandler);
    }

    private function createContainer(): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions([
                ResponseFactoryInterface::class => Psr17Factory::class,
                ...$this->getContainerDefinitions(),
            ])
        );
    }

    private function getContainerDefinitions(): array
    {
        return require dirname(__DIR__) . '/config/di-web.php';
    }
}
