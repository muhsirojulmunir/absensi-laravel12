<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PerformanceEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->subMonth()->month);
        $year = $request->input('year', Carbon::now()->subMonth()->year);

        $currentUser = Auth::user();
        $userRole = $currentUser->role->slug;

        $query = User::where('is_active', true)
            ->whereHas('performanceEvaluations', function($q) use ($month, $year) {
                $q->where('month', $month)->where('year', $year);
            })
            ->with(['performanceEvaluations' => function($q) use ($month, $year) {
                $q->where('month', $month)->where('year', $year);
            }, 'division', 'role']);

        // Access Control
        if ($userRole === 'pic_ramayana') {
            $query->whereHas('role', function($q) {
                $q->where('slug', 'karyawan_ramayana');
            });
        } elseif ($userRole === 'pic') {
            $query->whereHas('role', function($q) {
                $q->where('slug', 'karyawan');
            });
        } elseif (in_array($userRole, ['super-admin', 'hrd'])) {
            // Super admin & HRD can see all employees (karyawan and karyawan_ramayana)
            $query->whereHas('role', function($q) {
                $q->whereIn('slug', ['karyawan', 'karyawan_ramayana']);
            });
        } else {
            abort(403, 'Unauthorized action.');
        }

        $users = $query->get();

        // Group by division for Super Admin/HRD for a cleaner UI
        $groupedUsers = $users->groupBy(function ($user) {
            if ($user->role->slug === 'karyawan_ramayana') {
                return 'Tim Ramayana';
            }
            return 'Staff Kantor & Gudang';
        });

        return view('performance.index', compact('groupedUsers', 'month', 'year'));
    }

    public function show($id, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $evaluation = PerformanceEvaluation::where('user_id', $id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('user.division')
            ->firstOrFail();

        return view('performance.show', compact('evaluation'));
    }
}
