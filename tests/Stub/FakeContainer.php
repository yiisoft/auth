<?php

declare(strict_types=1);

namespace Yiisoft\Auth\Tests\Stub;

use Psr\Container\ContainerInterface;

class FakeContainer implements ContainerInterface
{
    private array $instances;

    public function __construct(array $instances = [])
    {
        $this->instances = $instances;
    }

    public function get($id)
    {
        return $this->instances[$id];
    }

    public function has($id)
    {
        return isset($this->instances[$id]);
    }
}
