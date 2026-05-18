<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $today = Carbon::today();

        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'check_in' => '08:00:00',
                'check_out' => '17:00:00',
                'date' => $today,
                'status' => 'Hadir',
            ]);
        }
    }
}
