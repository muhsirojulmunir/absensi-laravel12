@extends('layouts.master')
@section('title', 'HRD Dashboard')
@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">HR Intelligence <span class="text-blue-500">.</span></h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Monitoring sumber daya manusia &amp; analitik absensi.</p>
        </div>
        <div class="inline-flex items-center space-x-3.5 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 px-5 py-3 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <div class="bg-blue-50 dark:bg-blue-950/30 p-2.5 rounded-xl text-blue-600 dark:text-blue-400">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Anggota Aktif</p>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tracking-tight">Sesi Langsung</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Kehadiran Hari Ini -->
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none">* {{ $todayAttendance }} / {{ $totalEmployees }} Hadir</span>
            </div>
            <div>
                <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Rasio Kehadiran Hari Ini</h3>
                <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $attendancePercentage }}%</p>
            </div>
        </div>

        <!-- Card 2: Log Langsung -->
        <a href="{{ route('hrd.attendance.index') }}" class="group bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm hover:border-blue-500/30 dark:hover:border-blue-500/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
             <div class="flex items-center justify-between mb-4">
                 <div class="p-2.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl text-blue-600 dark:text-blue-400">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </div>
                 <span class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest bg-blue-50 dark:bg-blue-950/20 px-2.5 py-1 rounded-lg">Real-Time</span>
             </div>
             <div>
                 <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Monitoring Kehadiran</h3>
                 <p class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight leading-snug mb-1">Pelacak Kehadiran Tepat Waktu &amp; Log Langsung</p>
                 <span class="text-[9px] text-blue-600 dark:text-blue-450 font-bold uppercase tracking-wider flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                     Buka Monitoring &rarr;
                 </span>
             </div>
        </a>

        <!-- Card 3: Rekap & Laporan -->
        <a href="{{ route('hrd.attendance.recap') }}" class="group bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm hover:border-indigo-500/30 dark:hover:border-indigo-500/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
             <div class="flex items-center justify-between mb-4">
                 <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl text-indigo-600 dark:text-indigo-400">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                 </div>
                 <span class="text-[9px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest bg-indigo-50 dark:bg-indigo-950/20 px-2.5 py-1 rounded-lg">Laporan</span>
             </div>
             <div>
                 <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Rekapitulasi Absensi</h3>
                 <p class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight leading-snug mb-1">Analitik Kinerja Historis &amp; Ringkasan Bulanan</p>
                 <span class="text-[9px] text-indigo-600 dark:text-indigo-455 font-bold uppercase tracking-wider flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                     Lihat Rekapitulasi &rarr;
                 </span>
             </div>
        </a>
    </div>

    <!-- Chart Section -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-6 relative z-10">
            <div>
                <h3 class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Wawasan Tren</h3>
                <h2 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Tren Kehadiran Mingguan</h2>
            </div>
            <div class="flex items-center space-x-2 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 px-3.5 py-1.5 rounded-xl">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <span class="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Karyawan Hadir</span>
            </div>
        </div>
        
        <div class="relative h-80 w-full z-10">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)';
        const tickColor = isDark ? '#64748b' : '#94a3b8';

        // Gradient fill for the chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.15)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Total Kehadiran',
                    data: @json($counts),
                    borderColor: '#2563eb',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBorderWidth: 3,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.35,
                    borderCapStyle: 'round'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#151b26' : '#ffffff',
                        titleColor: isDark ? '#94a3b8' : '#64748b',
                        bodyColor: isDark ? '#f1f5f9' : '#1e293b',
                        borderColor: isDark ? '#1f2937' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.y} Karyawan Hadir`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                            color: tickColor,
                            padding: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: gridColor,
                            borderDash: [4, 4],
                            drawBorder: false 
                        },
                        ticks: { 
                            font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                            color: tickColor,
                            padding: 8,
                            stepSize: 1,
                            callback: function(value) {
                                if (Math.floor(value) === value) return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
