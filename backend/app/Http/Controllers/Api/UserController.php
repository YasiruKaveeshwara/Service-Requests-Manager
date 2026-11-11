<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Requests\ListUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserController
{
    public function index(ListUsersRequest $request): AnonymousResourceCollection
    {
        $role = UserRole::from($request->validated('role') ?? UserRole::Operations->value);

        return UserResource::collection(User::query()->withRole($role)->orderBy('name')->get());
    }
}
