<?php

namespace Pastapen\Env;

use Pastapen\Env\Exceptions\ImmutableEnvironmentException;

class Env
{
    protected array $values = [];

    protected bool $locked = false;

    public function set(string $key, mixed $value): void
    {
        if($this->locked){
            throw new ImmutableEnvironmentException();
        }
        
        $this->values[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function unlock(): void
    {
        $this->locked = false;
    }

    public function all(): array
    {
        return $this->values;
    }
}