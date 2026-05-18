<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'PIC', 'slug' => 'pic'],
            ['name' => 'HRD', 'slug' => 'hrd'],
            ['name' => 'Karyawan', 'slug' => 'karyawan'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
