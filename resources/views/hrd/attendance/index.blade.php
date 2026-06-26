@extends('layouts.master')
@section('title', 'Monitoring Absensi')
@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Monitoring Absensi</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pemantauan kehadiran seluruh karyawan secara real-time.</p>
        </div>
        <form action="{{ route('hrd.attendance.index') }}" method="GET" class="flex items-center space-x-3 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-xl px-4 py-2 shadow-sm">
            <label class="text-[9px] font-bold text-slate-450 uppercase tracking-widest whitespace-nowrap">Pilih Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" 
                   class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-0 cursor-pointer [color-scheme:dark] outline-none w-[110px]">
        </form>
    </div>

    <!-- Stats Summary Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Hadir</p>
            <p class="text-2xl font-bold text-emerald-500 dark:text-emerald-400">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Pulang Cepat</p>
            <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Izin / Sakit</p>
            <p class="text-2xl font-bold text-blue-500 dark:text-blue-400">{{ $attendances->whereIn('status', ['Izin', 'Sakit'])->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Total Log</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $attendances->count() }}</p>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-slate-50/50 dark:bg-slate-900/20 px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                <h2 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Log Langsung</h2>
            </div>
            <div class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wide">
                {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800/85">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Check In</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Check Out</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Status Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-slate-55 dark:hover:bg-slate-900/20 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold border border-slate-200/40 dark:border-slate-800 text-xs overflow-hidden shadow-inner">
                                        @if($attendance->user->avatar)
                                            <img src="{{ asset('storage/' . $attendance->user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($attendance->user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-white">{{ $attendance->user->name }}</div>
                                        <div class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">{{ $attendance->user->division->name ?? 'Tanpa Divisi' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $attendance->check_in ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $attendance->check_out ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                @php
                                    $statusColors = [
                                        'Hadir' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/20',
                                        'Terlambat' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/20',
                                        'Izin' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/20',
                                        'Sakit' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/20',
                                    ];
                                    $colorClass = $statusColors[$attendance->status] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                @endphp
                                <div class="flex flex-col items-end gap-1">
                                    <span class="px-2.5 py-1 border {{ $colorClass }} font-bold uppercase rounded-lg tracking-wider text-[9px]">{{ $attendance->status }}</span>
                                    @if($attendance->is_pulang_cepat)
                                        <span class="text-[8px] text-amber-600 dark:text-amber-450 font-bold uppercase tracking-wider">Pulang Cepat</span>
                                    @endif
                                    @if($attendance->note === 'Absen Diluar')
                                        <span class="text-[8px] text-teal-600 dark:text-teal-450 font-bold uppercase tracking-wider">📍 Diluar Radius</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="text-slate-400 dark:text-slate-500 text-xs font-semibold italic">Tidak ada catatan kehadiran pada tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
