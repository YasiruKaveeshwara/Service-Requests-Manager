<?php

declare(strict_types=1);

namespace App\Enums;

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
        return [self::Approved, self::InProgress, self::Completed];
    }

    /** @return list<string> */
    public static function operationsVisibleValues(): array
    {
        return array_map(static fn(self $status): string => $status->value, self::operationsVisible());
    }
}
