<?php

declare(strict_types=1);

use App\Models\User;

it('allows only operations users to view user lists', function () {
    expect(User::factory()->operations()->create()->can('viewAny', User::class))->toBeTrue()
        ->and(User::factory()->create()->can('viewAny', User::class))->toBeFalse()
        ->and(User::factory()->manager()->create()->can('viewAny', User::class))->toBeFalse();
});
