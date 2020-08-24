<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Stub;

use Yiisoft\Auth\IdentityInterface;

class FakeIdentity implements IdentityInterface
{
    private ?string $id;

    public function __construct(?string $id)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
