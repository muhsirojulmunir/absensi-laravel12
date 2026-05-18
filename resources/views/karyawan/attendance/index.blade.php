@extends('layouts.master')
@section('title', 'Riwayat Absensi')
@section('content')
<div class="space-y-6 md:space-y-10 animate-[fadeIn_0.5s_ease-out]" x-data="attendanceHandler()">
    <!-- Header: Bold & Minimalist -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <div class="space-y-1 transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Riwayat <span class="text-blue-500">Kehadiran</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 font-medium tracking-wide">Pantau performa dan keberadaan harian Anda.</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] px-6 py-3 rounded-2xl flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-500 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 group-hover:rotate-12 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1">Status</p>
                    <p class="text-sm font-bold text-blue-900 dark:text-blue-200 tracking-tight">Sangat Baik 🔥</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Snapshot -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.2em]">Hadir (Bulan Ini)</p>
            <p class="text-2xl md:text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-amber-400 uppercase tracking-[0.2em]">Pulang Cepat</p>
            <p class="text-2xl md:text-3xl font-black text-amber-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em]">Izin</p>
            <p class="text-2xl md:text-3xl font-black text-indigo-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $attendances->where('status', 'Izin')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-red-400 uppercase tracking-[0.2em]">Sakit</p>
            <p class="text-2xl md:text-3xl font-black text-red-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $attendances->where('status', 'Sakit')->count() }}</p>
        </div>
    </div>

    <!-- History Table: Sleek & Clean -->
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] md:rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-blue-50/50 dark:bg-slate-900/50 border-b border-blue-100 dark:border-slate-700">
                        <th class="px-6 md:px-10 py-4 md:py-6 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">Tanggal</th>
                        <th class="px-6 md:px-8 py-4 md:py-6 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-center">Absen Masuk</th>
                        <th class="px-6 md:px-8 py-4 md:py-6 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-center">Absen Keluar</th>
                        <th class="px-6 md:px-10 py-4 md:py-6 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-colors group">
                            <td class="px-6 md:px-10 py-4 md:py-6 whitespace-nowrap">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <div class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.3)] group-hover:scale-150 transition-transform"></div>
                                    <span class="text-xs md:text-sm font-bold text-blue-900 dark:text-blue-100 tracking-widest uppercase">{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 md:px-8 py-4 md:py-6 whitespace-nowrap text-center">
                                <span class="bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg text-sm md:text-base font-black text-blue-600 dark:text-blue-400 font-mono tracking-tighter shadow-sm border border-blue-100 dark:border-blue-900/50">{{ $attendance->check_in ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 md:px-8 py-4 md:py-6 whitespace-nowrap text-center">
                                <span class="bg-slate-50 dark:bg-slate-900/50 px-3 py-1.5 rounded-lg text-sm md:text-base font-black text-blue-800 dark:text-blue-200 font-mono tracking-tighter border border-slate-100 dark:border-slate-800">{{ $attendance->check_out ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 md:px-10 py-4 md:py-6 whitespace-nowrap text-right">
                                @php
                                    $statusColors = [
                                        'Hadir' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30',
                                        'Terlambat' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/30',
                                        'Izin' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/30',
                                        'Sakit' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/30',
                                    ];
                                    $colorClass = $statusColors[$attendance->status] ?? 'bg-slate-50 dark:bg-slate-900/20 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-800';
                                @endphp
                                <span class="px-3 md:px-5 py-1.5 md:py-2 border {{ $colorClass }} text-[8px] md:text-[10px] font-black uppercase rounded-full tracking-widest shadow-sm">
                                    {{ $attendance->status }} 
                                </span>
                                @if($attendance->is_pulang_cepat)
                                    <span class="block mt-2 px-3 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 text-[8px] md:text-[10px] font-black uppercase rounded-lg tracking-widest shadow-sm inline-block">
                                        <svg class="w-3 h-3 inline-block mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Pulang Cepat
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 md:px-10 py-20 md:py-32 text-center text-blue-400 font-black uppercase tracking-[0.3em] text-xs md:text-sm opacity-50 italic">Belum ada riwayat kehadiran. ✨</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="bg-blue-50/30 dark:bg-slate-900/50 px-6 md:px-10 py-4 md:py-6 border-t border-blue-50 dark:border-slate-700 flex items-center justify-between">
            <p class="text-[9px] md:text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic">Total Data: {{ $attendances->count() }}</p>
        </div>
    </div>
</div>
@endsection
