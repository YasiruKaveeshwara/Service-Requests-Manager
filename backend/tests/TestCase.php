<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function beforeRefreshingDatabase()
    {
        $database = DB::connection()->getDatabaseName();

        if ($database !== 'service_requests_test') {
            throw new RuntimeException("Refusing to run tests against [{$database}]. Expected [service_requests_test].");
        }
    }
}
