<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\RequestStatus;
use RuntimeException;

/**
 * Domain exception for a request operation that violates workflow rules.
 */
final class WorkflowException extends RuntimeException
{
    public static function because(string $message): self
    {
        return new self($message);
    }

    public static function invalidTransition(RequestStatus $from, RequestStatus $to): self
    {
        // Final states use a clearer message because no destination is valid.
        if ($from->isFinal()) {
            return new self(sprintf('This request is %s and can no longer be changed.', strtolower($from->label())));
        }

        return new self(sprintf('A request cannot move from "%s" to "%s".', $from->label(), $to->label()));
    }
}
