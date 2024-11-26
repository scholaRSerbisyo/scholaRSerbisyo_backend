<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class UserRoles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['role_name' => 'admin', 'role_description' => 'Events handlers'],
            ['role_name' => 'scholar', 'role_description' => 'Events attendees'],
        ];

        foreach ($types as $type) {
            Role::create($type);
        }
    }
}
