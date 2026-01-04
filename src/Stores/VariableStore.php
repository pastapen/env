<?php

namespace Pastapen\Env\Stores;

use Pastapen\Env\Contracts\ReaderInterface;
use Pastapen\Env\Contracts\WriterInterface;

class VariableStore implements ReaderInterface, WriterInterface
{
    protected array $values = [];

    public function supportsLock(): bool
    {
        return true;
    }

    public function all(): array
    {
        return $this->values;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function set(string $key, mixed $value = null): void
    {
        $this->values[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }
}