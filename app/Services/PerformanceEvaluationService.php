<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SalesInput;
use App\Models\PerformanceEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PerformanceEvaluationService
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function evaluateUser(User $user, int $month, int $year, bool $useAi = true)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $now = Carbon::now();
        if ($endDate > $now) {
            $endDate = $now;
        }

        // Aplikasi mulai berjalan sejak 21 Mei 2026. Sesuaikan start date jika di periode tersebut.
        $appStartDate = Carbon::create(2026, 5, 21)->startOfDay();
        if ($startDate->lt($appStartDate) && $endDate->gte($appStartDate)) {
            $startDate = $appStartDate->copy();
        }

        // 1. Gather Attendance Data
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $totalHadir = $attendances->filter(fn($a) => strtolower($a->status) === 'hadir')->count();
        $totalTelat = $attendances->filter(fn($a) => strtolower($a->status) === 'hadir' && $a->lateness_minutes > 0)->count();
        $totalPulangCepat = $attendances->filter(fn($a) => strtolower($a->status) === 'hadir' && $a->is_pulang_cepat)->count();
        $totalLibur = $attendances->filter(fn($a) => strtolower($a->status) === 'libur')->count();
        $izinSakit = $attendances->filter(fn($a) => in_array(strtolower($a->status), ['izin', 'sakit']))->count();
        $avgLateness = $attendances->filter(fn($a) => strtolower($a->status) === 'hadir' && $a->lateness_minutes > 0)->avg('lateness_minutes') ?? 0;

        // 2. Leave Requests
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->get();

        $totalHariIzin = 0;
        foreach ($leaveRequests as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $totalHariIzin += $start->diffInDays($end) + 1;
        }

        // Calculate expected days dynamically based on Division / Role
        $divisionName = strtolower($user->division->name ?? '');
        $isLiveStreamer = str_contains($divisionName, 'live streaming') || str_contains($divisionName, 'streamer');
        $isStaffKantor = str_contains($divisionName, 'staff kantor');

        $expectedDays = 0;
        $current = $startDate->copy();
        
        // Fetch holidays in the period
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        if ($isLiveStreamer) {
            // Live streamer: 1 day per week target
            $weeks = $startDate->diffInWeeks($endDate) + 1;
            $expectedDays = $weeks;
        } elseif ($isStaffKantor) {
            // Staff Kantor: Senin-Jumat, Sabtu-Minggu Libur & Libur Nasional Libur
            while ($current->lte($endDate)) {
                if (!$current->isWeekend() && !in_array($current->toDateString(), $holidays)) {
                    $expectedDays++;
                }
                $current->addDay();
            }
        } else {
            // Other staff (e.g. Gudang / Ramayana): Senin-Sabtu, Minggu Libur & Libur Nasional Libur
            while ($current->lte($endDate)) {
                if (!$current->isSunday() && !in_array($current->toDateString(), $holidays)) {
                    $expectedDays++;
                }
                $current->addDay();
            }
        }

        $attendanceSummary = [
            'total_hadir' => $totalHadir,
            'total_telat' => $totalTelat,
            'total_pulang_cepat' => $totalPulangCepat,
            'total_libur' => $totalLibur,
            'total_izin_sakit' => $izinSakit,
            'total_hari_cuti_approved' => $totalHariIzin,
            'avg_lateness_minutes' => round($avgLateness),
            'expected_days' => $expectedDays,
            'is_live_streamer' => $isLiveStreamer,
            'is_staff_kantor' => $isStaffKantor,
        ];

        // 3. Sales Data (If applicable)
        $salesSummary = null;
        if (str_contains($divisionName, 'ramayana') || str_contains($divisionName, 'sales')) {
            $sales = SalesInput::where('user_id', $user->id)
                ->where('type', 'sale')
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $salesSummary = [
                'total_items_sold' => $sales->sum('qty'),
                'total_revenue' => $sales->sum(function ($sale) {
                    return $sale->qty * $sale->nominal;
                }),
                'total_transactions' => $sales->count(),
            ];
        }

        // 4. Try AI first, fallback to smart local evaluation
        $result = null;
        if ($useAi) {
            $result = $this->geminiService->generatePerformanceReport($user, $month, $year, $attendanceSummary, $salesSummary);
        }

        // If AI failed (returned fallback), use smart local evaluation
        if (!$result || $result['predicate'] === 'Menunggu Evaluasi') {
            $result = $this->generateLocalEvaluation($user, $attendanceSummary, $salesSummary, $month, $year);
        }

        // 5. Save to Database
        return PerformanceEvaluation::updateOrCreate(
            ['user_id' => $user->id, 'month' => $month, 'year' => $year],
            [
                'attendance_summary' => $attendanceSummary,
                'sales_summary' => $salesSummary,
                'predicate' => $result['predicate'],
                'ai_feedback' => $result['feedback'],
                'ai_recommendation' => $result['recommendation'],
            ]
        );
    }

    /**
     * Generate evaluasi cerdas secara lokal tanpa AI
     */
    private function generateLocalEvaluation(User $user, array $attendance, ?array $sales, int $month, int $year): array
    {
        $name = $user->name;
        $bulan = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $hadir = $attendance['total_hadir'];
        $telat = $attendance['total_telat'];
        $pulangCepat = $attendance['total_pulang_cepat'];
        $izinSakit = $attendance['total_izin_sakit'];
        $avgLateness = $attendance['avg_lateness_minutes'] ?? 0;
        $tepatWaktu = $hadir - $telat;
        $expectedDays = $attendance['expected_days'] ?? 22;
        $isLiveStreamer = $attendance['is_live_streamer'] ?? false;

        // If completely zero attendance, score should be 0 unless there's an excuse
        if ($hadir == 0 && $izinSakit == 0 && ($attendance['total_libur'] ?? 0) == 0 && ($attendance['total_hari_cuti_approved'] ?? 0) == 0) {
            $score = 0; // Completely inactive
            $predicate = 'Kurang';
            $feedback = "{$name} tidak memiliki catatan kehadiran sama sekali di bulan {$bulan}.";
            $recommendation = "Harap segera menghubungi HRD atau PIC untuk klarifikasi status kehadiran.";
            
            return [
                'predicate' => $predicate,
                'feedback' => $feedback,
                'recommendation' => $recommendation,
            ];
        }

        $score = 100;

        // Penalti keterlambatan (berat)
        if ($hadir > 0) {
            $persenTelat = ($telat / $hadir) * 100;
            if ($persenTelat > 50) $score -= 35;
            elseif ($persenTelat > 30) $score -= 25;
            elseif ($persenTelat > 15) $score -= 15;
            elseif ($persenTelat > 0) $score -= 5;
        }

        // Penalti pulang cepat
        if ($hadir > 0) {
            $persenPulangCepat = ($pulangCepat / $hadir) * 100;
            if ($persenPulangCepat > 30) $score -= 20;
            elseif ($persenPulangCepat > 10) $score -= 10;
            elseif ($persenPulangCepat > 0) $score -= 5;
        }

        // Penalti kehadiran rendah berdasarkan rate dari expected days
        if ($expectedDays > 0) {
            $attendanceRate = ($hadir / $expectedDays) * 100;
            if ($attendanceRate < 50) $score -= 40;
            elseif ($attendanceRate < 75) $score -= 20;
            elseif ($attendanceRate < 90) $score -= 10;
        } else {
            $attendanceRate = 100;
        }

        // Bonus kehadiran tinggi
        if ($expectedDays > 0 && $hadir >= $expectedDays && $telat === 0 && $pulangCepat === 0) {
            $score += 5;
        }

        $score = max(0, min(100, $score));

        // === Tentukan Predikat ===
        if ($score >= 85) $predicate = 'Sangat Baik';
        elseif ($score >= 70) $predicate = 'Baik';
        elseif ($score >= 50) $predicate = 'Cukup';
        else $predicate = 'Kurang';

        // === Generate Feedback ===
        $feedbackParts = [];
        
        if ($tepatWaktu > 0 && $telat === 0) {
            if ($isLiveStreamer) {
                $feedbackParts[] = "{$name} menunjukkan kedisiplinan streaming yang luar biasa dengan menyelesaikan {$hadir} kali jadwal streaming tepat waktu";
            } else {
                $feedbackParts[] = "{$name} menunjukkan kedisiplinan yang sangat baik selama bulan {$bulan} dengan {$hadir} hari kehadiran sempurna tanpa keterlambatan";
            }
        } elseif ($hadir > 0) {
            if ($isLiveStreamer) {
                $feedbackParts[] = "{$name} melakukan streaming sebanyak {$hadir} kali di bulan {$bulan}";
            } else {
                $feedbackParts[] = "{$name} tercatat hadir {$hadir} hari di bulan {$bulan}";
            }
            if ($telat > 0) {
                $feedbackParts[] = "namun terlambat {$telat} kali" . ($avgLateness > 0 ? " (rata-rata {$avgLateness} menit)" : "");
            }
        }

        if ($expectedDays > 0) {
            if ($isLiveStreamer) {
                $feedbackParts[] = "mencapai " . round($attendanceRate) . "% dari total target {$expectedDays} sesi streaming";
            } else {
                $feedbackParts[] = "mencapai " . round($attendanceRate) . "% dari target {$expectedDays} hari kerja aktif";
            }
        }

        if ($pulangCepat > 0) {
            $feedbackParts[] = "pulang lebih awal {$pulangCepat} kali";
        }
        if ($izinSakit > 0) {
            $feedbackParts[] = "mengambil izin/sakit sebanyak {$izinSakit} hari";
        }
        $cuti = $attendance['total_hari_cuti_approved'] ?? 0;
        if ($cuti > 0) {
            $feedbackParts[] = "mengambil cuti disetujui {$cuti} hari";
        }

        if (empty($feedbackParts)) {
             $feedbackParts[] = "{$name} tidak memiliki catatan kehadiran di bulan {$bulan}";
        }
        if ($sales && $sales['total_transactions'] > 0) {
            $revenue = number_format($sales['total_revenue'], 0, ',', '.');
            $feedbackParts[] = "Serta berhasil mencatat {$sales['total_transactions']} transaksi penjualan dengan total omset Rp {$revenue}";
        }

        $feedback = implode(', ', $feedbackParts) . '.';
        $feedback = ucfirst($feedback);

        // === Generate Recommendation ===
        $recommendations = [];

        if ($telat > 3) {
            $recommendations[] = "Harap meningkatkan kedisiplinan waktu masuk kerja";
        } elseif ($telat > 0) {
            $recommendations[] = "Usahakan untuk konsisten hadir tepat waktu";
        }

        if ($pulangCepat > 2) {
            $recommendations[] = "Kurangi frekuensi pulang lebih cepat";
        }

        if ($expectedDays > 0 && $hadir >= $expectedDays && $telat === 0) {
            $recommendations[] = "Pertahankan konsistensi performa luar biasa ini!";
        }

        if ($expectedDays > 0 && $hadir < $expectedDays) {
            $recommendations[] = "Tingkatkan tingkat kehadiran atau penuhi target sesi di bulan berikutnya";
        }

        if ($sales && $sales['total_transactions'] > 0) {
            $recommendations[] = "Terus tingkatkan target penjualan produk di counter";
        }

        if (empty($recommendations)) {
            if ($score < 70) {
                 $recommendations[] = "Tingkatkan performa secara keseluruhan di bulan berikutnya";
            } else {
                 $recommendations[] = "Pertahankan kinerja positif yang sudah dicapai";
            }
        }

        $recommendation = implode('. ', $recommendations) . '.';

        return [
            'predicate' => $predicate,
            'feedback' => $feedback,
            'recommendation' => $recommendation,
        ];
    }
}
