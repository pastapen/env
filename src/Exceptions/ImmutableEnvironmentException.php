<?php

namespace Pastapen\Env\Exceptions;

use RuntimeException;
use Throwable;

class ImmutableEnvironmentException extends RuntimeException
{
    public function __construct(string $message = "Environment Data is Locked (Immutable). Please unlock if you wish to edit the variables", int $code = 409, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}