<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScholarType;

class ScholarTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['scholar_type_name' => 'Ordinary'],
            ['scholar_type_name' => 'Leader'],
        ];

        foreach ($types as $type) {
            ScholarType::create($type);
        }
    }
}
