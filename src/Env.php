<?php

namespace Pastapen\Env;

use InvalidArgumentException;
use Pastapen\Env\Exceptions\ImmutableEnvironmentException;

class Env
{
    protected array $values = [];

    protected bool $locked = false;

    protected bool $caseSensitive = false;

    protected bool $useGetEnv = true;

    protected bool $useEnvVar = true;

    public function __construct(
        bool $caseSensitive = false,
        bool $useGetEnv = true,
        bool $useEnvVar = true,
    )
    {
        $this->caseSensitive = $caseSensitive;
        $this->useGetEnv = $useGetEnv;
        $this->useEnvVar = $useEnvVar;
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

        $values = $this->values[$key] ?? null;

        if(!$values && $this->useEnvVar){
            $values = $_ENV[$key] ?? null;
        }
        if(!$values && $this->useGetEnv){
            $values = getenv($key, true);
        }

        return $values ?? $default;
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

        $exists = array_key_exists($key, $this->values);
        
        if(!$exists && $this->useEnvVar){
            $exists = array_key_exists($key, $_ENV);
        }
        if(!$exists && $this->useGetEnv){
            $exists = getenv($key, true) !== false;
        }

        return $exists;
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