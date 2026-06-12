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
        $data = $this->buildRecapData($request);

        // Enable filter setiap saat
        $today = Carbon::now();
        $canFilter = true;

        $data['canFilter'] = $canFilter;
        $data['todayName'] = $today->translatedFormat('l');

        return view('hrd.attendance.recap', $data);
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

            // Jangan restrict end date - allow sampai masa depan
            // (untuk Live Streamer yang perlu bayar sampai Sabtu hari itu)
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

        // Inject Edo dynamically if not already present
        $hasEdo = $users->contains(function ($u) {
            return strtolower($u->name) === 'edo';
        });

        if (!$hasEdo) {
            $edo = new User();
            $edo->id = null;
            $edo->name = 'Edo';
            
            $edoDivision = new \stdClass();
            $edoDivision->name = 'Staff Kantor';
            $edo->division = $edoDivision;
            
            $edo->setRelation('attendances', collect());
            $edo->setRelation('leaveRequests', collect());
            
            $users->push($edo);
        }

        $divisions = \App\Models\Division::all();

        // Get all holidays in this period to determine off days
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

            // Calculate Meal Allowance
            $isLiveStreamer = $user->division && strtolower($user->division->name) === 'live streaming';
            $attendanceDaysCount = $user->attendances->whereIn('status', ['Hadir', 'Terlambat'])->count();

            if ($isLiveStreamer) {
                $calculatedAmount = 0;
                $currentDateCheck = $startDate->copy();
                
                while ($currentDateCheck->lte($endDate)) {
                    $dateStr = $currentDateCheck->toDateString();
                    
                    $hasAttendance = $user->attendances->where('date', $dateStr)->first();
                    $excuse = $user->leaveRequests
                        ->where('status', '!=', 'rejected')
                        ->filter(function($lr) use ($dateStr) {
                            $checkDate = Carbon::parse($dateStr);
                            return $checkDate->between($lr->start_date, $lr->end_date);
                        })
                        ->first();

                    if ($hasAttendance) {
                        if (in_array($hasAttendance->status, ['Hadir', 'Terlambat'])) {
                            $calculatedAmount += 35000;
                        } elseif (in_array($hasAttendance->status, ['Izin', 'Sakit'])) {
                            $calculatedAmount += 20000;
                        }
                    } elseif ($excuse) {
                        if (in_array($excuse->type, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit', 'Lainnya'])) {
                            $calculatedAmount += 20000;
                        }
                        // Jika tipe-nya Libur / Libur (Day Off), tidak dapat uang makan (tambah 0)
                    } elseif ($currentDateCheck->isSaturday()) {
                        // Sabtu auto-hadir jika tidak ada izin/attendance
                        $calculatedAmount += 35000;
                    }
                    
                    $currentDateCheck->addDay();
                }
            } else {
                // Regular employee: 35rb per hari hadir
                $calculatedAmount = 35000 * $attendanceDaysCount;
            }

            if ($user->name === 'Edo') {
                $calculatedAmount = 0;
                $isLiveStreamer = false;
                $masukDates = [];
                $liburDates = [];
                $izinDates = [];
                $dailyAllowances = [];

                $currentDateCheck = $startDate->copy();
                while ($currentDateCheck->lte($endDate)) {
                    $dateStr = $currentDateCheck->toDateString();
                    \Carbon\Carbon::setLocale('id');
                    $formattedDate = $currentDateCheck->translatedFormat('d M');

                    if ($currentDateCheck->isWeekend()) {
                        $dailyAllowances[$dateStr] = 'Libur';
                        $liburDates[] = $formattedDate;
                    } else {
                        $dailyAllowances[$dateStr] = '35.000';
                        $masukDates[] = $formattedDate;
                        $calculatedAmount += 35000;
                    }
                    $currentDateCheck->addDay();
                }
                $user->calculated_meal_allowance = $calculatedAmount;
                $user->is_live_streamer = $isLiveStreamer;
            } else {
                // Generate detailed dates breakdown and daily allowances for recap print preview
                $masukDates = [];
                $liburDates = [];
                $izinDates = [];
                $dailyAllowances = [];

                $currentDateCheck = $startDate->copy();
                while ($currentDateCheck->lte($endDate)) {
                    $dateStr = $currentDateCheck->toDateString();
                    \Carbon\Carbon::setLocale('id');
                    $formattedDate = $currentDateCheck->translatedFormat('d M');

                    if ($currentDateCheck->isAfter(\Carbon\Carbon::today())) {
                        if ($currentDateCheck->isWeekend() && $user->division && str_contains(strtolower($user->division->name), 'staff kantor')) {
                            $dailyAllowances[$dateStr] = 'Libur';
                        } else {
                            $dailyAllowances[$dateStr] = '-';
                        }
                        $currentDateCheck->addDay();
                        continue;
                    }

                    $hasAttendance = $user->attendances->where('date', $dateStr)->first();
                    $excuse = $user->leaveRequests
                        ->where('status', '!=', 'rejected')
                        ->filter(function($lr) use ($dateStr) {
                            $checkDate = Carbon::parse($dateStr);
                            return $checkDate->between($lr->start_date, $lr->end_date);
                        })
                        ->first();

                    if ($isLiveStreamer) {
                        if ($hasAttendance) {
                            if (in_array($hasAttendance->status, ['Hadir', 'Terlambat'])) {
                                $masukDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = '35.000';
                            } elseif (in_array($hasAttendance->status, ['Izin', 'Sakit'])) {
                                $izinDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = '20.000';
                            } else {
                                $liburDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Libur';
                            }
                        } elseif ($excuse) {
                            if (in_array($excuse->type, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit', 'Lainnya'])) {
                                $izinDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = '20.000';
                            } else {
                                $liburDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Libur';
                            }
                        } elseif ($currentDateCheck->isSaturday()) {
                            $masukDates[] = $formattedDate . ' (Sabtu)';
                            $dailyAllowances[$dateStr] = '35.000';
                        } else {
                            $dailyAllowances[$dateStr] = '-';
                        }
                    } else {
                        if ($hasAttendance) {
                            if (in_array($hasAttendance->status, ['Hadir', 'Terlambat'])) {
                                $masukDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = '35.000';
                            } elseif (in_array($hasAttendance->status, ['Izin', 'Sakit'])) {
                                $izinDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Izin';
                            } else {
                                $liburDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Libur';
                            }
                        } elseif ($excuse) {
                            if (in_array($excuse->type, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit', 'Lainnya'])) {
                                $izinDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Izin';
                            } else {
                                $liburDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Libur';
                            }
                        } else {
                            $isStaffKantor = $user->division && str_contains(strtolower($user->division->name), 'staff kantor');
                            if ($isStaffKantor && $currentDateCheck->isWeekend()) {
                                $liburDates[] = $formattedDate;
                                $dailyAllowances[$dateStr] = 'Libur';
                            } else {
                                $holiday = $holidays->first(function($h) use ($dateStr) {
                                    return \Carbon\Carbon::parse($h->date)->toDateString() === $dateStr;
                                });
                                if ($holiday) {
                                    $liburDates[] = $formattedDate;
                                    $dailyAllowances[$dateStr] = 'Libur';
                                } else {
                                    $dailyAllowances[$dateStr] = '-';
                                }
                            }
                        }
                    }
                    $currentDateCheck->addDay();
                }
            }

            $detailParts = [];
            if (!empty($masukDates)) {
                $detailParts[] = "Masuk (" . count($masukDates) . "x): " . implode(', ', $masukDates);
            }
            if (!empty($izinDates)) {
                $detailParts[] = "Izin (" . count($izinDates) . "x): " . implode(', ', $izinDates);
            }
            if (!empty($liburDates)) {
                $detailParts[] = "Libur (" . count($liburDates) . "x): " . implode(', ', $liburDates);
            }
            $user->meal_allowance_details = implode(" | ", $detailParts);
            $user->daily_allowances = $dailyAllowances;

            // Check if this period has been paid and update if needed (dynamic history)
            $payment = \App\Models\MealAllowancePayment::where('user_id', $user->id)
                ->where('start_date', $startDate->toDateString())
                ->where('end_date', $endDate->toDateString())
                ->first();

            if ($payment) {
                // Do not dynamically update the saved amount to prevent overwriting history
                // $payment->update(['amount' => $calculatedAmount]);
            }

            $user->is_meal_paid = $payment ? true : false;
            
            // Jika sudah lunas, set total menjadi 0 agar tidak dihitung sebagai beban tagihan
            $user->total_meal_allowance = $user->is_meal_paid ? 0 : $calculatedAmount;
        }

        // Generate periodDates array to send to the view
        $periodDates = [];
        $currentDateCheck = $startDate->copy();
        while ($currentDateCheck->lte($endDate)) {
            $periodDates[] = [
                'date_string' => $currentDateCheck->toDateString(),
                'formatted' => $currentDateCheck->translatedFormat('d M'),
                'is_saturday' => $currentDateCheck->isSaturday(),
                'is_weekend' => $currentDateCheck->isWeekend(),
            ];
            $currentDateCheck->addDay();
        }

        return [
            'users' => $users,
            'divisions' => $divisions,
            'divisionId' => $divisionId,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'recapRouteName' => 'hrd.attendance.recap',
            'periodDates' => $periodDates,
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
                    ->where('start_date', $request->start_date)
                    ->where('end_date', $request->end_date)
                    ->delete();

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
                ->where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->delete();

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
            ->where('start_date', $request->start_date)
            ->where('end_date', $request->end_date)
            ->get();

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

    public function saveHistory(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'payments' => 'required|array',
            'payments.*.name' => 'required|string',
            'payments.*.amount' => 'required|numeric',
            'payments.*.user_id' => 'nullable|integer',
        ]);

        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate = Carbon::parse($request->end_date)->toDateString();

        // Delete existing records for this exact period
        \App\Models\MealAllowancePayment::where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->delete();

        // Save new records
        foreach ($request->payments as $p) {
            \App\Models\MealAllowancePayment::create([
                'user_id' => $p['user_id'] ?: null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'amount' => $p['amount'],
                'manual_employee_name' => $p['user_id'] ? null : $p['name'],
                'paid_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan ke riwayat pembayaran.',
        ]);
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

    public function updatePaymentHistory(Request $request, \App\Models\MealAllowancePayment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'manual_employee_name' => 'nullable|string|max:255',
        ]);

        $payment->amount = $request->amount;
        if ($payment->manual_employee_name !== null) {
            $payment->manual_employee_name = $request->manual_employee_name;
        }
        $payment->save();

        return back()->with('success', 'Riwayat pembayaran berhasil diupdate.');
    }

    public function deletePaymentHistory(\App\Models\MealAllowancePayment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Riwayat pembayaran berhasil dihapus.');
    }

    // ===================================================================
    // NOTIFIKASI TEST - Halaman test notifikasi untuk HRD
    // ===================================================================

    public function notificationTestPage()
    {
        $users = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan'))
            ->with('division')
            ->get();

        $allUsers = User::whereHas('role', fn($q) => $q->where('slug', 'karyawan'))
            ->with('division')
            ->where('is_active', true)
            ->get();

        return view('hrd.attendance.notification-test', compact('users', 'allUsers'));
    }

    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:100',
            'message' => 'required|string|max:500',
        ]);

        $user = User::find($request->user_id);

        if (!$user->fcm_token) {
            return back()->with('error', "Karyawan {$user->name} belum memiliki FCM Token. Minta karyawan untuk login ke web terlebih dahulu dan izinkan notifikasi browser.");
        }

        $firebase = new \App\Services\FirebaseService();
        $success = $firebase->sendNotification($user->fcm_token, $request->title, $request->message);

        if ($success) {
            return back()->with('success', "✅ Notifikasi berhasil dikirim ke {$user->name}!");
        }

        return back()->with('error', "❌ Gagal mengirim notifikasi ke {$user->name}. Cek log Laravel untuk detail.");
    }

    public function runSmartNotification(Request $request)
    {
        $options = ['--test' => true];

        if ($request->filled('user')) {
            $options['--user'] = $request->user;
        }
        if ($request->filled('type')) {
            $options['--type'] = $request->type;
        }

        \Illuminate\Support\Facades\Artisan::call('notify:smart-attendance', $options);
        $output = \Illuminate\Support\Facades\Artisan::output();

        return back()->with('smart_output', $output ?: 'Tidak ada output. Cek log Laravel.');
    }
}

