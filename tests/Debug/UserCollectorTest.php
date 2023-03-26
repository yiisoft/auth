<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Debug;

use Yiisoft\Auth\Debug\IdentityCollector;
use Yiisoft\Auth\Tests\Stub\FakeIdentity;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Tests\Collector\AbstractCollectorTestCase;

final class UserCollectorTest extends AbstractCollectorTestCase
{
    /**
     * @param IdentityCollector $collector
     */
    protected function collectTestData(CollectorInterface $collector): void
    {
        $collector->collect(null);
        $collector->collect(new FakeIdentity('stub1'));

        $collector->collect(null);
        $collector->collect(new FakeIdentity('stub2'));
        $collector->collect(null);
    }

    protected function getCollector(): CollectorInterface
    {
        return new IdentityCollector();
    }

    protected function checkCollectedData(array $data): void
    {
        parent::checkCollectedData($data);

        $this->assertCount(2, $data);
        $this->assertEquals([
            ['id' => 'stub1', 'class' => FakeIdentity::class],
            ['id' => 'stub2', 'class' => FakeIdentity::class],
        ], $data);
    }

    protected function checkIndexData(array $data): void
    {
        parent::checkIndexData($data);

        $this->assertArrayHasKey('identity', $data);
        $this->assertArrayHasKey('lastId', $data['identity']);
        $this->assertEquals('stub2', $data['identity']['lastId']);
        $this->assertEquals(2, $data['identity']['total']);
    }
}
