<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Stub;

use Yiisoft\Auth\IdentityInterface;

final class FakeIdentity implements IdentityInterface
{
    public function __construct(private ?string $id)
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
