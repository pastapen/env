<?php

namespace Pastapen\Env\Contracts;

interface ReaderInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function all(): array;
}