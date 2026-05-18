<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $rolesData = Role::withCount('users')->withMax('users', 'last_login_at')->get();
        
        $roleNames = $rolesData->pluck('name');
        $roleCounts = $rolesData->pluck('users_count');
        $roleLastLogins = $rolesData->pluck('users_max_last_login_at');

        return view('super-admin.dashboard', compact('totalUsers', 'roleNames', 'roleCounts', 'roleLastLogins'));
    }
}
