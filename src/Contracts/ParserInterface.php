<?php

namespace Pastapen\Env\Contracts;

interface ParserInterface
{
    public function fromFile(string $filename): static;

    public function parse(): array;
}