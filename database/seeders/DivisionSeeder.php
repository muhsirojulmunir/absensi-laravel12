<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'IT Support'],
            ['name' => 'Human Resources'],
            ['name' => 'Finance'],
            ['name' => 'Operations'],
            ['name' => 'Marketing'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
