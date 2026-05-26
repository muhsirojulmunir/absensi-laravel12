<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->latest()->paginate(10);

        // Calculate monthly stats globally
        $statsQuery = LeaveRequest::whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year);

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();

        return view('pic.leave-approvals.index', compact(
            'leaveRequests',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id()
        ]);

        return back()->with('success', 'Pengajuan izin disetujui.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id()
        ]);

        return back()->with('success', 'Pengajuan izin ditolak.');
    }
}
