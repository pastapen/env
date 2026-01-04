<?php

namespace Pastapen\Env\Contracts;

interface WriterInterface
{
    public function set(string $key, mixed $value = null): void;

    public function supportsLock(): bool;
}