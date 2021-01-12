<?php

declare(strict_types=1);

namespace Yiisoft\Auth;

/**
 * Identity repository is identity storage that is able to retrieve identity given and ID.
 */
interface IdentityRepositoryInterface
{
    public function findIdentity(string $id): ?IdentityInterface;
}
