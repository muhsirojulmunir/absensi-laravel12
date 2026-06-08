@extends('layouts.master')

@section('title', 'Detail Evaluasi - ' . $evaluation->user->name)

@section('content')
<div class="max-w-5xl mx-auto pb-20 space-y-6 animate-[fadeIn_0.5s_ease-out]">

<div class="flex items-center justify-between group">
    <a href="{{ route('performance.index', ['month' => $evaluation->month, 'year' => $evaluation->year]) }}"
       class="inline-flex items-center space-x-2 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-all group/back">
        <div class="p-2 bg-blue-50 dark:bg-slate-800 rounded-xl group-hover/back:-translate-x-1 transition-transform border border-blue-100 dark:border-slate-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </div>
        <span class="text-[11px] font-black uppercase tracking-[0.2em] italic">Kembali ke Daftar Evaluasi</span>
    </a>
</div>

@php
    $att = $evaluation->attendance_summary;
    $sales = $evaluation->sales_summary;
    $hadir = $att['total_hadir'] ?? 0;
    $telat = $att['total_telat'] ?? 0;
    $pulangCepat = $att['total_pulang_cepat'] ?? 0;
    $izinSakit = $att['total_izin_sakit'] ?? 0;
    $avgLateness = $att['avg_lateness_minutes'] ?? 0;
    $tepatWaktu = $hadir - $telat;
    $totalRecord = $hadir + $izinSakit + ($att['total_libur'] ?? 0);
    $persenHadir = $totalRecord > 0 ? round(($hadir / max($totalRecord, 1)) * 100) : 0;

    $predicateColor = match($evaluation->predicate) {
        'Sangat Baik' => 'from-emerald-500 to-green-600',
        'Baik' => 'from-blue-500 to-indigo-600',
        'Cukup' => 'from-amber-500 to-orange-600',
        'Kurang' => 'from-red-500 to-rose-600',
        default => 'from-slate-400 to-slate-500'
    };
    $predicateBg = match($evaluation->predicate) {
        'Sangat Baik' => 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/30',
        'Baik' => 'bg-blue-50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/30',
        'Cukup' => 'bg-amber-50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/30',
        'Kurang' => 'bg-red-50 border-red-200 dark:bg-red-950/20 dark:border-red-900/30',
        default => 'bg-slate-50 border-slate-200 dark:bg-slate-900/20 dark:border-slate-800'
    };
    $predicateEmoji = match($evaluation->predicate) {
        'Sangat Baik' => '🌟',
        'Baik' => '👍',
        'Cukup' => '📊',
        'Kurang' => '⚠️',
        default => '⏳'
    };
    $bulan = Carbon\Carbon::create($evaluation->year, $evaluation->month, 1)->translatedFormat('F Y');
@endphp

<!-- Header Card -->
<div class="bg-gradient-to-r {{ $predicateColor }} rounded-[2.5rem] p-8 md:p-10 text-white shadow-lg relative overflow-hidden group">
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold font-mono shadow-inner">
                {{ substr($evaluation->user->name, 0, 1) }}
            </div>
            <div>
                <p class="text-[10px] font-black text-white/80 uppercase tracking-[0.3em] font-mono leading-none">Evaluasi Kinerja Bulanan</p>
                <h1 class="text-3xl font-black uppercase tracking-tighter italic mt-1">{{ $evaluation->user->name }}</h1>
                <p class="text-white/95 text-xs font-bold mt-1.5 flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 bg-white/20 rounded-md">{{ $evaluation->user->employee_id }}</span>
                    <span>&bull;</span>
                    <span>{{ $evaluation->user->division->name ?? 'Staff' }}</span>
                    <span>&bull;</span>
                    <span>Periode: {{ $bulan }}</span>
                </p>
            </div>
        </div>
        <div class="text-center md:text-right">
            <div class="text-4xl mb-1.5">{{ $predicateEmoji }}</div>
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/10">
                <span class="text-lg font-black uppercase tracking-widest">{{ $evaluation->predicate }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-blue-100 dark:border-slate-700 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.05)]">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-500 dark:text-emerald-400 text-lg"></i>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">Hadir</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mt-2">{{ $hadir }} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">hari</span></p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-blue-100 dark:border-slate-700 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.05)]">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                <i class="fas fa-clock text-amber-500 dark:text-amber-400 text-lg"></i>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">Terlambat</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mt-2">{{ $telat }} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">kali</span></p>
        @if($avgLateness > 0)
        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-widest">Rata-rata {{ $avgLateness }} menit</p>
        @endif
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-blue-100 dark:border-slate-700 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.05)]">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center">
                <i class="fas fa-running text-orange-500 dark:text-orange-400 text-lg"></i>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">Pulang Cepat</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mt-2">{{ $pulangCepat }} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">kali</span></p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-blue-100 dark:border-slate-700 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.05)]">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                <i class="fas fa-calendar-minus text-blue-500 dark:text-blue-400 text-lg"></i>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">Izin/Sakit</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mt-2">{{ $izinSakit }} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">hari</span></p>
    </div>
</div>

<!-- Chart & AI Feedback Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Attendance Donut Chart -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.02)] p-8">
        <h3 class="text-xs font-black text-blue-900 dark:text-blue-200 uppercase tracking-widest mb-6 flex items-center gap-2">
            <i class="fas fa-chart-pie text-blue-500"></i> Distribusi Kehadiran
        </h3>
        <div class="flex items-center justify-center">
            <canvas id="attendanceChart" width="220" height="220"></canvas>
        </div>
        <div class="mt-6 space-y-3">
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block shadow-sm"></span> Tepat Waktu</span>
                <span class="text-slate-800 dark:text-slate-200">{{ $tepatWaktu }}</span>
            </div>
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block shadow-sm"></span> Terlambat</span>
                <span class="text-slate-800 dark:text-slate-200">{{ $telat }}</span>
            </div>
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block shadow-sm"></span> Pulang Cepat</span>
                <span class="text-slate-800 dark:text-slate-200">{{ $pulangCepat }}</span>
            </div>
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block shadow-sm"></span> Izin/Sakit</span>
                <span class="text-slate-800 dark:text-slate-200">{{ $izinSakit }}</span>
            </div>
        </div>
    </div>

    <!-- AI Feedback -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.02)] p-8">
            <h3 class="text-xs font-black text-indigo-900 dark:text-indigo-200 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-robot text-indigo-500"></i> Rangkuman Evaluasi
            </h3>
            <div class="{{ $predicateBg }} border dark:border-slate-700 rounded-[1.5rem] p-6 shadow-inner">
                <p class="text-slate-700 dark:text-slate-300 leading-relaxed font-medium italic">"{{ $evaluation->ai_feedback }}"</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.02)] p-8">
            <h3 class="text-xs font-black text-amber-900 dark:text-amber-200 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-lightbulb text-amber-500"></i> Saran & Reminder
            </h3>
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/10 border border-amber-200 dark:border-amber-900/30 rounded-[1.5rem] p-6 shadow-inner">
                <p class="text-slate-700 dark:text-slate-300 leading-relaxed font-medium italic">"{{ $evaluation->ai_recommendation }}"</p>
            </div>
        </div>
    </div>
</div>

@if($sales)
<!-- Sales Summary -->
<div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.02)] p-8">
    <h3 class="text-xs font-black text-green-900 dark:text-green-200 uppercase tracking-widest mb-6 flex items-center gap-2">
        <i class="fas fa-cash-register text-green-500"></i> Performa Penjualan
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-50/50 dark:bg-green-900/10 rounded-2xl p-5 border border-green-100 dark:border-green-900/30">
            <p class="text-[10px] font-black text-green-600 dark:text-green-500 uppercase tracking-widest mb-1">Total Item Terjual</p>
            <p class="text-3xl font-black text-green-700 dark:text-green-400">{{ number_format($sales['total_items_sold']) }} <span class="text-sm font-bold opacity-70">pcs</span></p>
        </div>
        <div class="bg-green-50/50 dark:bg-green-900/10 rounded-2xl p-5 border border-green-100 dark:border-green-900/30">
            <p class="text-[10px] font-black text-green-600 dark:text-green-500 uppercase tracking-widest mb-1">Total Omset</p>
            <p class="text-3xl font-black text-green-700 dark:text-green-400">Rp {{ number_format($sales['total_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-50/50 dark:bg-green-900/10 rounded-2xl p-5 border border-green-100 dark:border-green-900/30">
            <p class="text-[10px] font-black text-green-600 dark:text-green-500 uppercase tracking-widest mb-1">Total Transaksi</p>
            <p class="text-3xl font-black text-green-700 dark:text-green-400">{{ number_format($sales['total_transactions']) }}</p>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Izin/Sakit'],
            datasets: [{
                data: [{{ $tepatWaktu }}, {{ $telat }}, {{ $pulangCepat }}, {{ $izinSakit }}],
                backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#60a5fa'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12 },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 10
                }
            }
        }
    });
});
</script>

</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
