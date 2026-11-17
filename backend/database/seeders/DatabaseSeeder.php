<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Populate a complete review dataset with users and workflow examples.
     */
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            DemoRequestSeeder::class,
        ]);
    }
}
