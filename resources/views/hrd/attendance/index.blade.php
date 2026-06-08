@extends('layouts.master')
@section('title', 'Monitoring Absensi')
@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Monitoring Absensi</h1>
            <p class="text-blue-500 dark:text-blue-400 mt-1">Pemantauan kehadiran seluruh karyawan secara real-time.</p>
        </div>
        <form action="{{ route('hrd.attendance.index') }}" method="GET" class="flex items-center space-x-3 bg-slate-50 dark:bg-slate-900 shadow-inner border border-blue-100 dark:border-slate-800 rounded-2xl px-4 py-2 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <label class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-[0.15em]">Pilih Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" 
                   class="bg-transparent border-none text-sm font-bold text-blue-950 dark:text-white focus:ring-0 cursor-pointer [color-scheme:dark] px-2 outline-none">
        </form>
    </div>

    <!-- Stats Summary Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Hadir</p>
            <p class="text-xl font-bold text-emerald-500 dark:text-emerald-400">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Pulang Cepat</p>
            <p class="text-xl font-bold text-amber-500 dark:text-amber-400">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Izin/Sakit</p>
            <p class="text-xl font-bold text-blue-500 dark:text-blue-400">{{ $attendances->whereIn('status', ['Izin', 'Sakit'])->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Total Log</p>
            <p class="text-xl font-bold text-blue-950 dark:text-white">{{ $attendances->count() }}</p>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-blue-100 dark:border-slate-700 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] overflow-hidden">
        <div class="bg-blue-50/30 dark:bg-slate-900/50 px-8 py-5 border-b border-blue-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                <h2 class="text-sm font-bold text-blue-950 dark:text-white uppercase tracking-wider">Log Langsung</h2>
            </div>
            <div class="text-[11px] font-medium text-blue-600/80 dark:text-blue-400/80">
                Data untuk {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 shadow-inner/30 border-b border-blue-50 dark:border-slate-800">
                        <th class="px-8 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Check In</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Check Out</th>
                        <th class="px-8 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-right">Status Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-all group">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <div class="w-9 h-9 bg-blue-50 dark:bg-slate-900 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold border border-blue-200/50 dark:border-slate-700 group-hover:border-blue-500/50 transition-colors overflow-hidden">
                                        @if($attendance->user->avatar)
                                            <img src="{{ asset('storage/' . $attendance->user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($attendance->user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-blue-950 dark:text-white group-hover:text-blue-400 transition-colors">{{ $attendance->user->name }}</div>
                                        <div class="text-[10px] text-blue-500 dark:text-blue-400 font-bold uppercase tracking-wider">{{ $attendance->user->division->name ?? 'No Division' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <span class="text-sm font-bold text-blue-400">{{ $attendance->check_in ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <span class="text-sm font-bold text-blue-500">{{ $attendance->check_out ?? '--:--' }}</span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-right text-xs">
                                @php
                                    $statusColors = [
                                        'Hadir' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                        'Terlambat' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                        'Izin' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                        'Sakit' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                    ];
                                    $colorClass = $statusColors[$attendance->status] ?? 'bg-blue-50 text-blue-500 border-blue-200';
                                @endphp
                                <div class="flex flex-col items-end gap-1">
                                    <span class="px-3 py-1.5 border {{ $colorClass }} font-bold uppercase rounded-lg tracking-wide shadow-sm inline-flex items-center space-x-1">
                                        <span>{{ $attendance->status }}</span>
                                    </span>
                                    @if($attendance->is_pulang_cepat)
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30 font-bold uppercase rounded-lg tracking-widest text-[10px] shadow-sm inline-flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Pulang Cepat</span>
                                        </span>
                                    @endif
                                    @if($attendance->note === 'Absen Diluar')
                                        <span class="px-2.5 py-1 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-500/30 font-bold uppercase rounded-lg tracking-widest text-[10px] shadow-sm inline-flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span>Absen Diluar</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3 opacity-40 dark:opacity-20">
                                    <svg class="w-12 h-12 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-blue-500 dark:text-blue-400 text-sm font-semibold">Tidak ada catatan kehadiran pada tanggal ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
