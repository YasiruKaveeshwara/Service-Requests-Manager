<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

it('creates an employee by default', function () {
    expect(User::factory()->create()->role)->toBe(UserRole::Employee);
});

it('sets a manager through reportsTo', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();

    expect($employee->manager_id)->toBe($manager->id);
});

it('loads manager and direct report relationships', function () {
    $manager = User::factory()->manager()->create();
    User::factory()->count(2)->reportsTo($manager)->create();

    $manager->load('directReports.manager');

    expect($manager->directReports)->toHaveCount(2)
        ->and($manager->directReports->first()->manager->is($manager))->toBeTrue();
});

it('casts roles and filters by role', function () {
    User::factory()->manager()->create();
    User::factory()->employee()->create();

    expect(User::firstWhere('role', UserRole::Manager->value)->role)->toBe(UserRole::Manager)
        ->and(User::query()->withRole(UserRole::Manager)->count())->toBe(1);
});

it('hashes factory passwords', function () {
    $user = User::factory()->create();

    expect($user->password)->not->toBe('password');
});
