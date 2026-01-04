<?php

namespace Pastapen\Env\Stores;

use Pastapen\Env\Contracts\ReaderInterface;
use Pastapen\Env\Contracts\WriterInterface;

class SuperGlobalEnvStore implements ReaderInterface, WriterInterface
{
    public function supportsLock(): bool
    {
        return false;
    }

    public function all(): array
    {
        return $_ENV;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_ENV);
    }

    public function set(string $key, mixed $value = null): void
    {
        $_ENV[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $_ENV[$key] : $default;
    }
}