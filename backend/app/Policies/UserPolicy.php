<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Authorization boundary for user-directory access.
 */
final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Operations);
    }
}
