<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::withRole('karyawan')
            ->where('id', '!=', Auth::id())
            ->with(['role', 'attendances' => function ($query) {
                $query->whereDate('date', now());
            }, 'leaveRequests' => function ($query) {
                $query->latest();
            }])
            ->get();

        return view('pic.employees.index', compact('employees'));
    }

    public function show(User $user)
    {
        // Ensure we are viewing a 'karyawan', not another PIC/Admin unless intended
        if ($user->role->slug !== 'karyawan') {
            abort(404);
        }

        return view('pic.employees.show', compact('user'));
    }
}
