<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Roles used by policies, visibility scopes, and workflow authorization.
 */
enum UserRole: string
{
    case Employee = 'employee';
    case Manager = 'manager';
    case Operations = 'operations';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
