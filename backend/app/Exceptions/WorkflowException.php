<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class WorkflowException extends RuntimeException
{
    public static function because(string $message): self
    {
        return new self($message);
    }
}
