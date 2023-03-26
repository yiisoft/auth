<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Debug;

use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Collector\CollectorTrait;
use Yiisoft\Yii\Debug\Collector\IndexCollectorInterface;

final class IdentityCollector implements CollectorInterface, IndexCollectorInterface
{
    use CollectorTrait;

    private array $identities = [];

    public function getCollected(): array
    {
        return $this->identities;
    }

    public function collect(?IdentityInterface $identity): void
    {
        if (!$this->isActive()) {
            return;
        }

        if ($identity === null) {
            return;
        }

        $this->identities[] = [
            'id' => $identity->getId(),
            'class' => $identity::class,
        ];
    }

    private function reset(): void
    {
        $this->identities = [];
    }

    public function getIndexData(): array
    {
        $lastIdentity = end($this->identities);
        return [
            'identity' => [
                'lastId' => is_array($lastIdentity) ? $lastIdentity['id'] : null,
                'total' => count($this->identities),
            ],
        ];
    }
}
