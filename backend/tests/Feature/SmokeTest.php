<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('boots the app and answers the framework health check', function () {
    $this->get('/up')->assertOk();
});

it('runs against the dedicated test database', function () {
    expect(DB::connection()->getDatabaseName())->toBe('quantix_test');
});
