@extends('layouts.master')
@section('title', 'HRD Dashboard')
@section('content')
<div class="space-y-8 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <div class="transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl font-extrabold text-blue-950 dark:text-white tracking-tight">HR Intelligence <span class="text-blue-500">.</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 mt-1 font-medium">Monitoring sumber daya manusia & analitik absensi.</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 px-6 py-3 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 transition-transform duration-300 hover:rotate-12 hover:scale-110">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-blue-400 dark:text-blue-500 uppercase tracking-widest mb-1.5 leading-none">Anggota Aktif</p>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
                    <p class="text-sm font-extrabold text-blue-950 dark:text-blue-100 tracking-tight leading-none">Sesi Langsung</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 hover:-translate-y-1 cursor-default">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400 inline-block mb-5 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-blue-400 dark:text-blue-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Kehadiran Hari Ini</h3>
                <p class="text-4xl font-black text-blue-700 dark:text-blue-400 tracking-tighter">{{ $attendancePercentage }}%</p>
                <p class="text-[10px] text-blue-400 dark:text-blue-500 font-bold mt-2 italic">* {{ $todayAttendance }} / {{ $totalEmployees }} Karyawan</p>
            </div>
        </div>

        <a href="{{ route('hrd.attendance.index') }}" class="group relative bg-blue-600 p-8 rounded-3xl shadow-[0_20px_40px_-15px_rgba(37,99,235,0.5)] overflow-hidden text-white transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(37,99,235,0.6)] active:scale-[0.98]">
             <svg class="absolute -right-10 -bottom-10 w-48 h-48 text-white/10 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
             <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-blue-100 mb-2">Log Langsung</h3>
                    <p class="text-xl font-bold leading-tight tracking-wide">Monitoring Kehadiran & Pelacak Tepat Waktu</p>
                </div>
                <div class="mt-8 flex items-center space-x-2 font-bold text-sm bg-white/10 w-max px-4 py-2 rounded-xl backdrop-blur-md">
                    <span>Buka Monitoring</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
             </div>
        </a>

        <a href="{{ route('hrd.attendance.recap') }}" class="group relative bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-all duration-300 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-indigo-500/10 hover:-translate-y-1">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-50 dark:bg-indigo-900/10 rounded-full blur-3xl group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl text-indigo-600 dark:text-indigo-400 inline-block mb-5 shadow-sm group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-blue-400 dark:text-blue-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Rekap & Laporan</h3>
                    <p class="text-xl font-bold text-blue-950 dark:text-white leading-tight tracking-wide">Analitik Kinerja Historis & Ringkasan</p>
                </div>
                <div class="mt-8 text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center space-x-2 bg-indigo-50/80 dark:bg-slate-700 w-max px-4 py-2 rounded-xl group-hover:bg-indigo-100 dark:group-hover:bg-slate-600 transition-colors">
                    <span>Lihat Rekapitulasi</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Chart Section -->
    <div class="bg-white dark:bg-slate-800 p-10 rounded-[2.5rem] border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
        <div class="flex items-center justify-between mb-8 relative z-10">
            <div>
                <h3 class="text-[11px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.3em] mb-2 italic">Wawasan Trafik</h3>
                <h2 class="text-2xl font-black text-blue-950 dark:text-white tracking-tighter">Tren Kehadiran <span class="text-blue-500">Mingguan</span></h2>
            </div>
            <div class="hidden md:flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/30 px-4 py-2 rounded-xl border border-blue-100 dark:border-blue-800/50">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">Karyawan Aktif</span>
            </div>
        </div>
        
        <div class="relative h-[300px] w-full z-10">
            <canvas id="attendanceChart"></canvas>
        </div>

        <!-- Decorative background elements -->
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-50/50 rounded-full blur-3xl text-blue-100/20"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        // Gradient fill for the chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Total Kehadiran',
                    data: @json($counts),
                    borderColor: '#2563eb',
                    borderWidth: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBorderWidth: 4,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4,
                    borderCapStyle: 'round'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 13, weight: 'bold', family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' },
                        padding: 12,
                        cornerRadius: 12,
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
                            font: { size: 10, weight: 'bold', family: 'Inter' },
                            color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : '#f1f5f9',
                            drawBorder: false 
                        },
                        ticks: { 
                            font: { size: 10, weight: 'bold', family: 'Inter' },
                            color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b',
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
</div>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
