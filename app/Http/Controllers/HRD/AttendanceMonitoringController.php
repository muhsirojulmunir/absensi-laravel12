<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $attendances = Attendance::with(['user.division'])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereDate('date', $date)
            ->latest()
            ->get();

        return view('hrd.attendance.index', compact('attendances', 'date'));
    }

    public function recap(Request $request)
    {
        return view('hrd.attendance.recap', $this->buildRecapData($request));
    }

    public function buildRecapData(Request $request): array
    {
        $divisionId = $request->get('division_id');
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $customRange = $request->get('custom_date_range');
        if ($customRange) {
            if (str_contains($customRange, ' to ')) {
                $dates = explode(' to ', $customRange);
            } elseif (str_contains($customRange, ' - ')) {
                $dates = explode(' - ', $customRange);
            } else {
                $dates = [$customRange, $customRange];
            }

            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = Carbon::parse($dates[1] ?? $dates[0])->endOfDay();

            if ($endDate->isAfter(Carbon::today()->endOfDay())) {
                $endDate = Carbon::today()->endOfDay();
            }
            if ($startDate->isAfter(Carbon::today()->endOfDay())) {
                $startDate = Carbon::today()->startOfDay();
            }
        }

        $query = User::withRole('karyawan')->where('is_active', true)
            ->with(['division', 'attendances' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }, 'leaveRequests' => function($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['approved', 'pending'])
                  ->where(function($query) use ($startDate, $endDate) {
                      $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function($sub) use ($startDate, $endDate) {
                                $sub->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                  });
            }]);

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        $users = $query->get();
        $divisions = \App\Models\Division::all();

        // Get all holidays in this period to determine off days
        // We get holidays for the whole month/period + some buffer for weekly calculation
        $startWeek = $startDate->copy()->startOfWeek();
        $endWeek = $endDate->copy()->endOfWeek();
        $holidays = \App\Models\Holiday::whereBetween('date', [$startWeek, $endWeek])->get();

        foreach ($users as $user) {
            $user->hadir_count = $user->attendances->where('status', 'Hadir')->count();
            $user->terlambat_count = $user->attendances->where('status', 'Terlambat')->count();
            $user->pulang_cepat_count = $user->attendances->where('is_pulang_cepat', true)->count();
            
            // Collect all "Excuse" dates for the period
            $excuseDates = []; // date => type
            foreach ($user->leaveRequests as $lr) {
                $current = $lr->start_date->copy();
                while ($current->lte($lr->end_date)) {
                    $excuseDates[$current->toDateString()] = $lr->type;
                    $current->addDay();
                }
            }
            
            // Map attendances for quick lookup (ensure key is Y-m-d)
            $attendanceDates = $user->attendances->mapWithKeys(function ($item) {
                $d = \Carbon\Carbon::parse($item->date)->toDateString();
                return [$d => $item->status];
            })->toArray();
            
            $izinDetails = [];
            $liburDetails = [];
            
            // Loop setiap hari dalam periode terpilih
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if ($currentDate->isAfter(\Carbon\Carbon::today())) {
                    break;
                }
                $dateStr = $currentDate->toDateString();
                
                // Atur bahasa ke Indonesia
                \Carbon\Carbon::setLocale('id');
                $formattedDate = $currentDate->translatedFormat('l, d M Y');
                
                if (isset($attendanceDates[$dateStr])) {
                    $status = $attendanceDates[$dateStr];
                    if (in_array($status, ['Izin', 'Sakit'])) {
                        $izinDetails[] = "- $formattedDate ($status)";
                    } elseif (str_starts_with((string)$status, 'Libur')) {
                        $liburDetails[] = "- $formattedDate ($status)";
                    }
                } elseif (isset($excuseDates[$dateStr])) {
                    $type = $excuseDates[$dateStr];
                    if (in_array($type, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit', 'Lainnya'])) {
                        $izinDetails[] = "- $formattedDate ($type)";
                    } elseif (in_array($type, ['Libur', 'Libur (Day Off)'])) {
                        $liburDetails[] = "- $formattedDate ($type)";
                    }
                } else {
                    // Jika tidak ada absensi dan tidak ada pengajuan izin/libur
                    // Cek apakah hari ini weekend (Sabtu/Minggu) dan user adalah Staff Kantor
                    if ($user->division && str_contains(strtolower($user->division->name), 'staff kantor') && $currentDate->isWeekend()) {
                        $liburDetails[] = "- $formattedDate (Libur Akhir Pekan)";
                    } 
                    // Cek apakah hari ini ada libur nasional yang diset Super Admin
                    else {
                        $holiday = $holidays->first(function($h) use ($dateStr) {
                            return \Carbon\Carbon::parse($h->date)->toDateString() === $dateStr;
                        });
                        if ($holiday) {
                            $liburDetails[] = "- $formattedDate (Libur: " . $holiday->name . ")";
                        }
                    }
                }
                
                $currentDate->addDay();
            }

            $user->izin_count = count($izinDetails);
            $user->libur_count = count($liburDetails);
            $user->izin_details = implode("\n", $izinDetails);
            $user->libur_details = implode("\n", $liburDetails);

            // Calculate Meal Allowance: Rp 35.000 per hari masuk (Hadir/Terlambat)
            $mealRate = 35000;
            $attendanceDaysCount = $user->attendances->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $calculatedAmount = $mealRate * $attendanceDaysCount;
            $user->calculated_meal_allowance = $calculatedAmount;
            
            // Check if this period has been paid
            $payment = \App\Models\MealAllowancePayment::where('user_id', $user->id)
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->where('start_date', '<=', $startDate->toDateString())
                              ->where('end_date', '>=', $endDate->toDateString());
                      });
                })
                ->first();
            
            $user->is_meal_paid = $payment ? true : false;
            
            // Jika sudah lunas, set total menjadi 0 agar tidak dihitung sebagai beban tagihan
            $user->total_meal_allowance = $user->is_meal_paid ? 0 : $calculatedAmount;
        }

        return [
            'users' => $users,
            'divisions' => $divisions,
            'divisionId' => $divisionId,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'recapRouteName' => 'hrd.attendance.recap',
        ];
    }

    public function payMealAllowance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($request->has('bulk_pay')) {
            // Bulk payment
            foreach ($request->bulk_pay as $userId => $amount) {
                \App\Models\MealAllowancePayment::where('user_id', $userId)
                    ->where(function($q) use ($request) {
                        $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                          ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                          ->orWhere(function($sub) use ($request) {
                              $sub->where('start_date', '<=', $request->start_date)
                                  ->where('end_date', '>=', $request->end_date);
                          });
                    })->delete();

                \App\Models\MealAllowancePayment::create(
                    [
                        'user_id' => $userId,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'amount' => $amount,
                        'paid_by' => auth()->id(),
                    ]
                );
            }
        } else {
            // Single payment
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric',
            ]);

            \App\Models\MealAllowancePayment::where('user_id', $request->user_id)
                ->where(function($q) use ($request) {
                    $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($sub) use ($request) {
                          $sub->where('start_date', '<=', $request->start_date)
                              ->where('end_date', '>=', $request->end_date);
                      });
                })->delete();

            \App\Models\MealAllowancePayment::create(
                [
                    'user_id' => $request->user_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'amount' => $request->amount,
                    'paid_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', 'Pembayaran uang makan berhasil dicatat.');
    }

    public function togglePayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        $payments = \App\Models\MealAllowancePayment::where('user_id', $request->user_id)
            ->where(function($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function($sub) use ($request) {
                      $sub->where('start_date', '<=', $request->start_date)
                          ->where('end_date', '>=', $request->end_date);
                  });
            })->get();

        if ($payments->count() > 0) {
            foreach ($payments as $p) {
                $p->delete();
            }
            return back()->with('success', 'Status diubah menjadi Belum Lunas.');
        } else {
            \App\Models\MealAllowancePayment::create([
                'user_id' => $request->user_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'amount' => $request->amount,
                'paid_by' => auth()->id(),
            ]);
            return back()->with('success', 'Status diubah menjadi Lunas.');
        }
    }

    public function paymentHistory(Request $request)
    {
        $payments = \App\Models\MealAllowancePayment::with(['user', 'paidBy'])
            ->orderByDesc('start_date')
            ->get();

        $groupedByPeriod = [];
        foreach ($payments as $payment) {
            $periodKey = $payment->start_date . '|' . $payment->end_date;
            $startDate = Carbon::parse($payment->start_date);
            $endDate = Carbon::parse($payment->end_date);
            $periodLabel = $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y');

            if (!isset($groupedByPeriod[$periodKey])) {
                $groupedByPeriod[$periodKey] = [
                    'label' => $periodLabel,
                    'startDate' => $payment->start_date,
                    'endDate' => $payment->end_date,
                    'items' => [],
                    'totalAmount' => 0,
                    'totalCount' => 0,
                ];
            }

            $groupedByPeriod[$periodKey]['items'][] = $payment;
            $groupedByPeriod[$periodKey]['totalAmount'] += $payment->amount;
            $groupedByPeriod[$periodKey]['totalCount']++;
        }

        return view('hrd.attendance.payment-history', [
            'groupedByPeriod' => collect($groupedByPeriod),
            'totalPayments' => $payments->count(),
            'grandTotal' => $payments->sum('amount'),
        ]);
    }
}
