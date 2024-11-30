<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['admin_type_name' => 'developer'],
            ['admin_type_name' => 'CSO'],
            ['admin_type_name' => 'School'],
            ['admin_type_name' => 'Community'],
        ];

        foreach ($types as $type) {
            AdminType::create($type);
        }
    }
}
