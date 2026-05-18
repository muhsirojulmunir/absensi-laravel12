<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->leaveRequests()->latest();

        // Custom Date Filtering
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $leaveRequests = $query->paginate(5)->appends($request->all());

        // Monthly Stats Reset
        $statsQuery = $user->leaveRequests()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();

        return view('karyawan.leave-requests.index', compact(
            'leaveRequests', 
            'totalCount', 
            'pendingCount', 
            'approvedCount', 
            'rejectedCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Libur,Sakit,Izin Penting,Lainnya',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        Auth::user()->leaveRequests()->create([
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('karyawan.leave-requests.index')->with('success', 'Pengajuan izin berhasil dibuat.');
    }
}
