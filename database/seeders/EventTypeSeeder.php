<?php

use Illuminate\Database\Seeder;
use App\Models\EventType;

class EventTypeSeeder extends Seeder
{
    public function run()
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