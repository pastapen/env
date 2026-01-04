<?php

namespace Pastapen\Env;

use InvalidArgumentException;
use Pastapen\Env\Exceptions\ImmutableEnvironmentException;

class Env
{
    protected array $values = [];

    protected bool $locked = false;

    protected bool $caseSensitive = false;

    public function __construct(bool $caseSensitive = false)
    {
        $this->caseSensitive = $caseSensitive;
    }

    public function set(string $key, mixed $value): void
    {
        if($this->locked){
            throw new ImmutableEnvironmentException();
        }

        $key = $this->normalizeKey($key);
        
        $this->values[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->normalizeKey($key);

        return $this->values[$key] ?? $default;
    }

    protected function normalizeKey(string $key): string
    {
        $key = trim($key);

        if(empty($key)){
            throw new InvalidArgumentException("Environment key can't be empty");
        }

        if(!$this->caseSensitive){
            $key = strtoupper($key);
        }

        return $key;
    }

    public function has(string $key): bool
    {
        $key = $this->normalizeKey($key);

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