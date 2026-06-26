@extends('layouts.master')
@section('title', 'Dashboard Super Admin')
@section('content')
    @php
        $totalKaryawan = $usersList->filter(fn($u) => in_array($u->role->slug ?? '', ['karyawan', 'karyawan_ramayana']))->count();
        $totalManagement = $usersList->filter(fn($u) => in_array($u->role->slug ?? '', ['hrd', 'pic', 'pic_ramayana', 'hrd_ramayana']))->count();
        $totalAdmin = $usersList->filter(fn($u) => in_array($u->role->slug ?? '', ['super-admin', 'admin']))->count();
    @endphp

    <div class="space-y-6 animate-fade-in">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
            <div class="space-y-1">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Analitik Global <span class="text-blue-500">.</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Portal monitoring terpadu dan manajemen sistem.</p>
            </div>
            
            <div class="inline-flex items-center space-x-3.5 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 px-5 py-3 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="bg-emerald-50 dark:bg-emerald-950/30 p-2 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Status Sistem</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 tracking-tight">Berjalan &amp; Aktif</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Total Pengguna -->
            <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-555">Global</span>
                </div>
                <div>
                    <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Total Pengguna</h3>
                    <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $totalUsers }}</p>
                </div>
            </div>

            <!-- Card 2: Karyawan -->
            <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-550 dark:text-indigo-400">Divisi Staff</span>
                </div>
                <div>
                    <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Total Karyawan</h3>
                    <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $totalKaryawan }}</p>
                </div>
            </div>

            <!-- Card 3: Management -->
            <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-purple-50 dark:bg-purple-950/30 rounded-xl text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-550 dark:text-purple-400">HR &amp; PIC</span>
                </div>
                <div>
                    <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Tim Manajemen</h3>
                    <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $totalManagement }}</p>
                </div>
            </div>

            <!-- Card 4: Administrator -->
            <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 rounded-xl text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12V5a3 3 0 016 0v7a3 3 0 01-3 3h-3zm0 0H6a3 3 0 00-3 3v2a3 3 0 003 3h12a3 3 0 003-3v-2a3 3 0 00-3-3h-3"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-550 dark:text-rose-400">Admin</span>
                </div>
                <div>
                    <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Super Admin</h3>
                    <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $totalAdmin }}</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Distribution Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Distribusi Peran</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium">Rincian demografis pengguna sistem secara real-time.</p>
                    </div>
                    <div class="p-2 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path>
                        </svg>
                    </div>
                </div>
                <div class="h-80 relative w-full">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>

            <!-- User Ledger Details -->
            <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm flex flex-col justify-between min-h-[440px]">
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white tracking-tight">Buku Besar Pengguna</h3>
                    <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1.5 custom-scrollbar">
                        @foreach($usersList as $user)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-800/60 hover:border-slate-200 dark:hover:border-slate-700 transition-all duration-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ substr($user->username ?? $user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[130px]">{{ $user->username ?? $user->name }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">
                                            {{ $user->role->name ?? 'Karyawan' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-medium text-slate-450 dark:text-slate-500 text-right leading-relaxed">
                                    Login: <br />
                                    <span class="font-semibold text-slate-600 dark:text-slate-400">
                                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Belum Login' }}
                                    </span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center">
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest">JMN Matrix &bull; {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('roleChart').getContext('2d');
            const colors = ['#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e'];

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)';
            const tickColor = isDark ? '#64748b' : '#94a3b8';

            const createGradient = (color) => {
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, color + 'e6'); // 90% opacity
                gradient.addColorStop(1, color + '1a'); // 10% opacity
                return gradient;
            };

            const gradients = colors.map(createGradient);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($roleNames) !!},
                    datasets: [{
                        label: 'Pengguna',
                        data: {!! json_encode($roleCounts) !!},
                        backgroundColor: gradients,
                        borderColor: colors.slice(0, {!! count($roleNames) !!}),
                        borderWidth: 1.5,
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 28,
                        hoverBackgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        y: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    },
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
                            displayColors: false
                        }
                    },
                    scales: {
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
                                padding: 8
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                                color: tickColor,
                                padding: 8
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection