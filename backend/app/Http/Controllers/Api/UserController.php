<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Requests\ListUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Lists users needed by workflow forms, currently operations assignees.
 */
final class UserController
{
    /** Return users filtered by the requested role and sorted by name. */
    public function index(ListUsersRequest $request): AnonymousResourceCollection
    {
        $role = UserRole::from($request->validated('role') ?? UserRole::Operations->value);

        return UserResource::collection(User::query()->withRole($role)->orderBy('name')->get());
    }
}
