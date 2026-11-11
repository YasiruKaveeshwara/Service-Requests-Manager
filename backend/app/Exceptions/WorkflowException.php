<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\RequestStatus;
use RuntimeException;

final class WorkflowException extends RuntimeException
{
    public static function because(string $message): self
    {
        return new self($message);
    }

    public static function invalidTransition(RequestStatus $from, RequestStatus $to): self
    {
        if ($from->isFinal()) {
            return new self(sprintf('This request is %s and can no longer be changed.', strtolower($from->label())));
        }

        return new self(sprintf('A request cannot move from "%s" to "%s".', $from->label(), $to->label()));
    }
}
