@extends('layouts.master')
@section('title', 'Rekap Absensi')
@section('content')
    <div class="space-y-8" x-data="{ period: '{{ $period }}' }">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Rekap Absensi</h1>
                <p class="text-blue-500 dark:text-blue-400 mt-1">Laporan performa untuk periode
                    {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M') }} -
                    {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
            </div>

            <form action="{{ route('hrd.attendance.recap') }}" method="GET"
                class="flex flex-wrap items-center gap-3 bg-slate-50 dark:bg-slate-900 shadow-inner p-2 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <select name="period" x-model="period"
                    class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none cursor-pointer">
                    <option value="day" class="dark:bg-slate-900">Harian</option>
                    <option value="week" class="dark:bg-slate-900">Minggu Ini</option>
                    <option value="month" class="dark:bg-slate-900">Bulanan</option>
                    <option value="custom" class="dark:bg-slate-900">Custom</option>
                </select>

                <div x-show="period === 'day'" class="flex items-center">
                    <input type="date" name="date" value="{{ $startDate }}"
                        class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white [color-scheme:dark] outline-none">
                </div>

                <div x-show="period === 'month'" class="flex items-center space-x-2">
                    <select name="month"
                        class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white outline-none">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }} class="dark:bg-slate-900">
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <select name="year"
                        class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white outline-none">
                        @foreach(range(date('Y'), date('Y') - 5) as $y)
                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }} class="dark:bg-slate-900">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="period === 'custom'" class="flex items-center space-x-2">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white [color-scheme:dark] outline-none">
                    <span class="text-blue-400 dark:text-blue-500 font-bold">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white [color-scheme:dark] outline-none">
                </div>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-xl transition-all shadow-lg shadow-blue-600/10 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-600 dark:bg-blue-900/80 rounded-[2rem] p-6 shadow-xl shadow-blue-600/20 text-white relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80 mb-1">Total Dana Diperlukan</p>
                <h3 class="text-2xl font-black tracking-tighter italic">
                    Rp {{ number_format($users->sum('total_meal_allowance'), 0, ',', '.') }}
                </h3>
                <div class="mt-4 flex items-center gap-2">
                    <div class="px-2 py-0.5 bg-white/20 rounded-full text-[8px] font-black uppercase tracking-widest">Estimasi</div>
                    <p class="text-[9px] font-bold text-blue-100 italic">* Berdasarkan rekap periode terpilih</p>
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] p-6 shadow-sm flex items-center justify-between group">
                <div>
                    <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.2em] mb-1">Total Karyawan</p>
                    <h3 class="text-2xl font-black text-blue-950 dark:text-white tracking-tighter">{{ $users->count() }} Orang</h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 dark:bg-slate-900 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:rotate-12 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] p-6 shadow-sm flex items-center justify-between group">
                <div>
                    <p class="text-[10px] font-black text-emerald-400 dark:text-emerald-500 uppercase tracking-[0.2em] mb-1">Kehadiran Rata-rata</p>
                    @php
                        $avgPercentage = $users->count() > 0 ? $users->avg(function($u) {
                            $total = $u->hadir_count + $u->terlambat_count + $u->izin_count;
                            return $total > 0 ? (($u->hadir_count - $u->pulang_cepat_count) / $total) * 100 : 0;
                        }) : 0;
                    @endphp
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">{{ round($avgPercentage) }}%</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 dark:bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:rotate-12 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Recap Table -->
        <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-blue-100 dark:border-slate-700 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-blue-50/30 dark:bg-slate-900/50 border-b border-blue-100 dark:border-slate-700">
                            <th class="px-8 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Karyawan
                            </th>
                            <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">
                                Hadir</th>
                            <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">
                                Terlambat</th>
                            <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center text-orange-500 dark:text-orange-400">
                                Pulang Cepat</th>
                            <th
                                class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center text-blue-400 dark:text-blue-500">
                                Izin/Sakit/Off</th>
                            <th class="px-6 py-5 text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider text-center bg-blue-50/50 dark:bg-slate-900/80">
                                Uang Makan</th>
                            <th class="px-8 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-right">
                                Performa Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-colors group">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="font-bold text-blue-950 dark:text-white text-sm group-hover:text-blue-400 transition-colors">
                                        {{ $user->name }}</div>
                                    <div class="text-[10px] text-blue-500 dark:text-blue-400 font-bold uppercase tracking-wider">
                                        {{ $user->division->name ?? 'Tidak ada Divisi' }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-emerald-500 dark:text-emerald-400 font-bold text-sm">
                                    {{ $user->hadir_count }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-amber-500 dark:text-amber-400 font-bold text-sm">
                                    {{ $user->terlambat_count }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-orange-500 dark:text-orange-400 font-bold text-sm">
                                    {{ $user->pulang_cepat_count }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-blue-400 dark:text-blue-500 font-bold text-sm">
                                    {{ $user->izin_count }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center bg-blue-50/20 dark:bg-slate-900/50">
                                    <div class="text-sm font-black text-blue-600 dark:text-blue-400 tracking-tighter">Rp {{ number_format($user->total_meal_allowance, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-right">
                                    @php
                                        $totalDays = $user->hadir_count + $user->terlambat_count + $user->izin_count;
                                        $percentage = $totalDays > 0 ? round((($user->hadir_count - $user->pulang_cepat_count) / $totalDays) * 100) : 0;
                                        if ($percentage < 0) $percentage = 0;
                                    @endphp
                                    <div class="flex items-center justify-end space-x-3">
                                        <div
                                            class="w-32 bg-slate-50 dark:bg-slate-900 shadow-inner rounded-full h-1.5 overflow-hidden border border-blue-100 dark:border-slate-800 shadow-inner">
                                            <div class="bg-blue-600 dark:bg-blue-500 h-full transition-all duration-700"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-blue-950 dark:text-white">{{ $percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="px-8 py-20 text-center text-blue-500 dark:text-blue-400 font-bold uppercase tracking-widest text-xs opacity-40">
                                    Data rekap tidak ditemukan untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection