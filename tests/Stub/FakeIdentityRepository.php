<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Stub;

use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityInterface;

final class FakeIdentityRepository implements IdentityRepositoryInterface
{
    private ?IdentityInterface $returnIdentity;
    private array $callParams = [];

    public function __construct(?IdentityInterface $returnIdentity)
    {
        $this->returnIdentity = $returnIdentity;
    }

    public function findIdentity(string $id): ?IdentityInterface
    {
        $this->callParams['find'] = $id;

        return $this->returnIdentity;
    }

    public function findIdentityByToken(string $token, string $type = null): ?IdentityInterface
    {
        $this->callParams['findIdentityByToken'] = [
            'token' => $token,
            'type' => $type
        ];

        return $this->returnIdentity;
    }

    public function getCallParams(): array
    {
        return $this->callParams;
    }
}
