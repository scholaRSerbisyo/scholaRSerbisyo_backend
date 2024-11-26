<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventType;


class EventTypeSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'CSO', 'description' => 'Events for all scholars'],
            ['name' => 'School', 'description' => 'Events specific to a school'],
            ['name' => 'Barangay', 'description' => 'Events specific to a barangay'],
        ];

        foreach ($types as $type) {
            EventType::create($type);
        }
    }
}
