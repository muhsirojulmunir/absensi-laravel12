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

        $attendanceSummary = [
            'total_hadir' => $totalHadir,
            'total_telat' => $totalTelat,
            'total_pulang_cepat' => $totalPulangCepat,
            'total_libur' => $totalLibur,
            'total_izin_sakit' => $izinSakit,
            'total_hari_cuti_approved' => $totalHariIzin,
            'avg_lateness_minutes' => round($avgLateness),
        ];

        // 3. Sales Data (If applicable)
        $salesSummary = null;
        $divisionName = strtolower($user->division->name ?? '');
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

        // If completely zero attendance, score should be 0 unless there's an excuse
        $totalDays = Carbon::create($year, $month, 1)->daysInMonth;
        
        // === Hitung Skor ===
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

        // Penalti kehadiran rendah vs total hari aktif (roughly 22)
        if ($hadir < 10) $score -= 40;
        elseif ($hadir < 15) $score -= 20;

        // Bonus kehadiran tinggi
        if ($hadir >= 22 && $telat === 0 && $pulangCepat === 0) $score += 5;

        $score = max(0, min(100, $score));

        // === Tentukan Predikat ===
        if ($score >= 85) $predicate = 'Sangat Baik';
        elseif ($score >= 70) $predicate = 'Baik';
        elseif ($score >= 50) $predicate = 'Cukup';
        else $predicate = 'Kurang';

        // === Generate Feedback ===
        $feedbackParts = [];
        
        if ($tepatWaktu > 0 && $telat === 0) {
            $feedbackParts[] = "{$name} menunjukkan kedisiplinan yang sangat baik selama bulan {$bulan} dengan {$hadir} hari kehadiran sempurna tanpa keterlambatan";
        } elseif ($hadir > 0) {
            $feedbackParts[] = "{$name} tercatat hadir {$hadir} hari di bulan {$bulan}";
            if ($telat > 0) {
                $feedbackParts[] = "namun terlambat {$telat} kali" . ($avgLateness > 0 ? " (rata-rata {$avgLateness} menit)" : "");
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
            $feedbackParts[] = "mengambil cuti yang disetujui sebanyak {$cuti} hari";
        }

        if (empty($feedbackParts)) {
             $feedbackParts[] = "{$name} tidak memiliki catatan kehadiran di bulan {$bulan}";
        }
        if ($sales && $sales['total_transactions'] > 0) {
            $revenue = number_format($sales['total_revenue'], 0, ',', '.');
            $feedbackParts[] = "Dari sisi penjualan, berhasil mencatat {$sales['total_transactions']} transaksi dengan total omset Rp {$revenue}";
        }

        $feedback = implode(', ', $feedbackParts) . '.';
        $feedback = ucfirst($feedback);

        // === Generate Recommendation ===
        $recommendations = [];

        if ($telat > 3) {
            $recommendations[] = "Perlu meningkatkan disiplin waktu kedatangan. Coba atur alarm 30 menit lebih awal";
        } elseif ($telat > 0) {
            $recommendations[] = "Keterlambatan masih terjadi {$telat} kali, usahakan untuk datang tepat waktu di bulan depan";
        }

        if ($pulangCepat > 2) {
            $recommendations[] = "Hindari pulang lebih awal kecuali ada keperluan mendesak yang sudah disetujui";
        }

        if ($hadir >= 20 && $telat === 0) {
            $recommendations[] = "Pertahankan konsistensi kehadiran yang sangat baik ini! Kamu menjadi contoh yang baik untuk tim";
        }

        if ($hadir < 15 && $hadir > 0) {
            $recommendations[] = "Tingkatkan frekuensi kehadiran di bulan depan untuk menunjukkan komitmen yang lebih baik";
        }

        if ($sales && $sales['total_transactions'] > 0) {
            $recommendations[] = "Terus tingkatkan performa penjualan. Coba fokus pada produk-produk unggulan untuk meningkatkan omset";
        }

        if (empty($recommendations)) {
            if ($score < 70) {
                 $recommendations[] = "Tingkatkan performa secara keseluruhan dan jadilah lebih proaktif di bulan berikutnya";
            } else {
                 $recommendations[] = "Terus pertahankan performa yang sudah baik dan jadilah inspirasi bagi rekan kerja lainnya";
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
