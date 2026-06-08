<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Division;

class RamayanaRoleDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['slug' => 'karyawan_ramayana'], ['name' => 'Karyawan Ramayana']);
        Role::firstOrCreate(['slug' => 'pic_ramayana'], ['name' => 'PIC Ramayana']);
        
        Division::firstOrCreate(['name' => 'Sales Marketing']);
    }
}
