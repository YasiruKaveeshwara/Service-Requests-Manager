<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * States a service request may occupy during its lifecycle.
 *
 * The transition map below is the single source of truth for workflow
 * validation; controllers and policies should not duplicate these rules.
 */
enum RequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Returned = 'returned';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        // Terminal states intentionally return an empty list so that both the
        // service layer and the UI can determine when a request is immutable.
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::Approved, self::Rejected, self::Returned, self::Cancelled],
            self::Returned => [self::Submitted, self::Cancelled],
            self::Approved => [self::InProgress],
            self::InProgress => [self::Completed],
            self::Rejected, self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Returned], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In progress',
            default => ucfirst($this->value),
        };
    }

    /** @return list<self> */
    public static function operationsVisible(): array
    {
        // Operations users only work on requests that passed managerial review.
        return [self::Approved, self::InProgress, self::Completed];
    }

    /** @return list<string> */
    public static function operationsVisibleValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::operationsVisible());
    }
}
