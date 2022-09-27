<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Stub;

use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

final class FakeIdentityRepository implements IdentityWithTokenRepositoryInterface
{
    private array $callParams = [];

    public function __construct(private ?IdentityInterface $returnIdentity)
    {
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
            'type' => $type,
        ];

        return $this->returnIdentity;
    }

    public function getCallParams(): array
    {
        return $this->callParams;
    }
}
