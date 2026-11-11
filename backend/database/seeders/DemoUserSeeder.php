<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $maya = User::factory()->manager()->create([
            'name' => 'Maya Manager',
            'email' => 'manager@quantix.test',
        ]);
        $marcus = User::factory()->manager()->create([
            'name' => 'Marcus Manager',
            'email' => 'manager2@quantix.test',
        ]);

        User::factory()->employee()->reportsTo($maya)->create([
            'name' => 'Ethan Employee',
            'email' => 'employee1@quantix.test',
        ]);
        User::factory()->employee()->reportsTo($maya)->create([
            'name' => 'Emma Employee',
            'email' => 'employee2@quantix.test',
        ]);
        User::factory()->employee()->reportsTo($marcus)->create([
            'name' => 'Ella Employee',
            'email' => 'employee3@quantix.test',
        ]);

        User::factory()->operations()->create([
            'name' => 'Olivia Ops',
            'email' => 'ops1@quantix.test',
        ]);
        User::factory()->operations()->create([
            'name' => 'Oscar Ops',
            'email' => 'ops2@quantix.test',
        ]);
    }
}
