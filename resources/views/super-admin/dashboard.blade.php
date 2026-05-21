@extends('layouts.master')
@section('title', 'Dashboard Super Admin')
@section('content')
    <div class="space-y-8 animate-[fadeIn_0.5s_ease-out]">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
            <div class="transition-transform duration-300 group-hover:translate-x-1">
                <h1 class="text-3xl font-extrabold text-blue-950 dark:text-white tracking-tight">Analitik Global <span
                        class="text-blue-500">.</span></h1>
                <p class="text-blue-600/80 dark:text-blue-400 mt-1 font-medium">Portal monitoring terpadu dan manajemen
                    sistem.</p>
            </div>
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 px-6 py-3 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div
                    class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 transition-transform duration-300 hover:rotate-12 hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p
                        class="text-[10px] font-bold text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1.5">
                        Status Sistem</p>
                    <div class="flex items-center space-x-2">
                        <span
                            class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        <p
                            class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight leading-none">
                            Berjalan & Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl relative overflow-hidden group shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] cursor-pointer">
                <div
                    class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-all duration-500">
                </div>
                <div class="relative z-10 text-center md:text-left">
                    <div
                        class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400 inline-block mb-5 shadow-sm group-hover:scale-110 group-hover:rotate-3 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-blue-400 dark:text-blue-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Total
                        Karyawan</h3>
                    <p class="text-4xl font-black text-blue-700 dark:text-blue-400 tracking-tighter">{{ $totalUsers }}</p>
                </div>
            </div>

            <div
                class="md:col-span-3 bg-gradient-to-br from-blue-50/50 via-white to-blue-50/30 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900 border border-dashed border-blue-200 dark:border-slate-700 p-8 rounded-3xl flex items-center justify-center relative overflow-hidden transition-all duration-500 hover:border-blue-400/50 dark:hover:border-slate-500 hover:bg-blue-50 dark:hover:bg-slate-800/80">
                <div
                    class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoNTksIDEzMCwgMjQ2LCAwLjE1KSIvPjwvc3ZnPg==')]">
                </div>
                <p
                    class="text-blue-600 dark:text-blue-400 text-[11px] font-bold uppercase tracking-[0.3em] flex items-center gap-3 relative z-10 group cursor-default bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm px-6 py-3 rounded-2xl border border-white dark:border-slate-700">
                    <svg class="w-5 h-5 animate-spin-slow text-blue-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Modul Metrik Sistem Tingkat Lanjut Diaktifkan
                </p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Distribution Chart -->
            <div
                class="lg:col-span-2 bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl relative shadow-[0_8px_30px_rgb(0,0,0,0.04)] group transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-blue-950 dark:text-white tracking-tight">Distribusi Peran</h3>
                        <p class="text-xs text-blue-400 dark:text-blue-500 mt-1.5 font-medium tracking-wide">Rincian
                            demografis pengguna sistem secara real-time.</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="h-80 relative w-full">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>

            <!-- Role Count Details -->
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col justify-between transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div class="space-y-8">
                    <h3 class="text-xl font-bold text-blue-950 dark:text-white tracking-tight">Buku Besar Pengguna</h3>
                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($usersList as $index => $user)
                            <div
                                class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-800 hover:border-blue-300 dark:hover:border-slate-600 hover:bg-blue-50/50 dark:hover:bg-slate-800 hover:shadow-sm hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold uppercase shadow-sm group-hover:scale-110 transition-transform duration-300">
                                        {{ substr($user->username ?? $user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-base font-black text-blue-950 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors tracking-wide">{{ $user->username ?? $user->name }}</span>
                                        <span
                                            class="text-[10px] font-bold text-blue-500/80 dark:text-blue-400 mt-0.5 uppercase tracking-wider">
                                            {{ $user->role->name ?? 'Karyawan' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 text-right">
                                    Login: <br />
                                    {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Belum Login' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-blue-100/80 dark:border-slate-700 text-center">
                    <p class="text-[10px] text-blue-400 dark:text-blue-500 font-bold uppercase tracking-[0.3em]">JMN Matrix
                        &bull; {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.getElementById('custom-animations')) {
                const style = document.createElement('style');
                style.id = 'custom-animations';
                style.innerHTML = `
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .animate-spin-slow {
                        animation: spin 8s linear infinite;
                    }
                `;
                document.head.appendChild(style);
            }

            const ctx = document.getElementById('roleChart').getContext('2d');
            const colors = ['#3b82f6', '#6366f1', '#10b981', '#f59e0b', '#ef4444'];

            const createGradient = (color) => {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color);
                gradient.addColorStop(1, color + '33');
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
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 12,
                        borderSkipped: false,
                        barThickness: 32,
                        hoverBackgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        y: {
                            duration: 1500,
                            easing: 'easeOutElastic'
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#64748b',
                            bodyColor: '#1e293b',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            titleFont: { family: 'Poppins', size: 12 },
                            bodyFont: { family: 'Poppins', size: 14, weight: 'bold' },
                            cornerRadius: 12,
                            displayColors: false,
                            boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : '#f1f5f9',
                                borderDash: [5, 5]
                            },
                            border: { display: false },
                            ticks: {
                                font: { family: 'Poppins', size: 11, weight: '600' },
                                color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#94a3b8',
                                padding: 10
                            }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                font: { family: 'Poppins', size: 11, weight: '600' },
                                color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b',
                                padding: 10
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection