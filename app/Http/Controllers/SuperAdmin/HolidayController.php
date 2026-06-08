<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Division;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('division')->orderBy('date', 'desc')->get();
        $divisions = Division::all();
        
        return view('super-admin.holidays.index', compact('holidays', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'description' => 'required|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $startDate = \Carbon\Carbon::parse($request->date);
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : $startDate->copy();

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $holiday = Holiday::create([
                'date' => $currentDate->format('Y-m-d'),
                'description' => $request->description,
                'division_id' => $request->division_id,
            ]);

            // Create Attendance placeholder records for relevant employees
            $usersQuery = \App\Models\User::whereHas('role', function ($q) {
                $q->where('slug', 'karyawan');
            })->where('is_active', true);

            if ($holiday->division_id) {
                $usersQuery->where('division_id', $holiday->division_id);
            }

            $users = $usersQuery->get();

            foreach ($users as $user) {
                $attendance = \App\Models\Attendance::firstOrNew([
                    'user_id' => $user->id,
                    'date' => $holiday->date,
                ]);

                // Only overwrite if it doesn't exist or is a placeholder/empty
                if (!$attendance->exists || ($attendance->check_in == null && $attendance->check_out == null)) {
                    $attendance->status = 'Libur - ' . $holiday->description;
                    $attendance->save();
                }
            }

            $currentDate->addDay();
        }

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $message = $totalDays > 1
            ? "Hari libur berhasil ditambahkan untuk {$totalDays} hari ({$startDate->translatedFormat('d M Y')} - {$endDate->translatedFormat('d M Y')})."
            : 'Hari libur berhasil ditambahkan.';

        return redirect()->route('super-admin.holidays.index')->with('success', $message);
    }

    public function destroy(Holiday $holiday)
    {
        // Remove placeholder attendance records
        \App\Models\Attendance::whereDate('date', $holiday->date)
            ->where('status', 'like', 'Libur - %')
            ->whereNull('check_in')
            ->whereNull('check_out')
            ->delete();

        $holiday->delete();

        return redirect()->route('super-admin.holidays.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
