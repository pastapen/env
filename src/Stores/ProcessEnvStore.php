<?php

namespace Pastapen\Env\Stores;

use Pastapen\Env\Contracts\ReaderInterface;
use Pastapen\Env\Contracts\WriterInterface;

class ProcessEnvStore implements ReaderInterface, WriterInterface
{
    public function supportsLock(): bool
    {
        return false;
    }

    public function all(): array
    {
        return getenv();
    }

    public function has(string $key): bool
    {
        return getenv($key, true) !== false || getenv($key) !== false;
    }

    public function set(string $key, mixed $value = null): void
    {
        putenv("$key=$value");
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = getenv($key, true);

        if ($value !== false) {
            return $value;
        }

        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}