<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;

class UserResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'manager_id' => $user->manager_id,
            'manager' => $this->relation('manager', self::class),
        ];
    }
}
