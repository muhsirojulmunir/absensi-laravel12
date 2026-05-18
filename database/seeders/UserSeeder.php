<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $divisionId = Division::inRandomOrder()->first()->id;
            
            User::create([
                'name' => $role->name . ' JMN',
                'username' => str_replace(' ', '', strtolower($role->name)),
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'division_id' => $divisionId,
                'position' => 'Staff ' . $role->name,
                'employee_id' => 'EMP-' . strtoupper($role->slug) . '-01',
                'phone' => '08123456789',
                'address' => 'Jakarta, Indonesia',
            ]);
        }
    }
}
