@extends('layouts.master')
@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="space-y-6 animate-fade-in" x-data="rekapBulanan()" x-init="init()">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Rekap Absensi Bulanan</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Akumulasi kehadiran, lupa absen, dan ketidakhadiran seluruh karyawan
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('super-admin.attendance.rekap-bulanan') }}" id="monthForm">
                <div class="flex items-center gap-2">
                    <label for="month" class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">Pilih Bulan:</label>
                    <input
                        type="month"
                        name="month"
                        id="month"
                        value="{{ $month }}"
                        max="{{ now()->format('Y-m') }}"
                        onchange="document.getElementById('monthForm').submit()"
                        class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all outline-none"
                    >
                </div>
            </form>
        </div>
    </div>

    {{-- ===== SUMMARY CARDS ===== --}}
    @php
        $totalEmployees   = count($rekap);
        $totalMasukAll    = collect($rekap)->sum('total_masuk');
        $totalLupaAbsen   = collect($rekap)->sum('total_lupa_absen');
        $totalTidakHadir  = collect($rekap)->sum('total_tidak_hadir');
        $monthLabel       = \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y');
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        {{-- Total Karyawan --}}
        <div class="rounded-2xl p-4 bg-gradient-to-br from-indigo-600 to-indigo-800 text-white shadow-sm border border-indigo-500/30">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-white/15 rounded-xl flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-indigo-200 uppercase tracking-wider">Total Karyawan</p>
                    <p class="text-2xl font-black">{{ $totalEmployees }}</p>
                </div>
            </div>
        </div>

        {{-- Total Hari Masuk --}}
        <div class="rounded-2xl p-4 bg-gradient-to-br from-emerald-600 to-teal-800 text-white shadow-sm border border-emerald-500/30">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-white/15 rounded-xl flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-emerald-200 uppercase tracking-wider">Total Hari Masuk</p>
                    <p class="text-2xl font-black">{{ $totalMasukAll }}</p>
                </div>
            </div>
        </div>

        {{-- Total Lupa Absen --}}
        <div class="rounded-2xl p-4 bg-gradient-to-br from-amber-600 to-amber-800 text-white shadow-sm border border-amber-500/30">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-white/15 rounded-xl flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-amber-200 uppercase tracking-wider">Total Lupa Absen</p>
                    <p class="text-2xl font-black">{{ $totalLupaAbsen }}</p>
                </div>
            </div>
        </div>

        {{-- Total Tidak Hadir / Libur --}}
        <div class="rounded-2xl p-4 bg-gradient-to-br from-rose-600 to-rose-800 text-white shadow-sm border border-rose-500/30">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-white/15 rounded-xl flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-rose-200 uppercase tracking-wider">Total Libur / Tdk Hadir</p>
                    <p class="text-2xl font-black">{{ $totalTidakHadir }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SEARCH & FILTER ===== --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari nama karyawan atau divisi..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Filter:</span>
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Semua</button>
                <button @click="filter = 'lupa'" :class="filter === 'lupa' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Ada Lupa Absen</button>
                <button @click="filter = 'tidak_hadir'" :class="filter === 'tidak_hadir' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Ada Tidak Hadir</button>
            </div>
        </div>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-2.5 font-medium">
            Periode: <strong class="text-slate-700 dark:text-slate-300">{{ $monthLabel }}</strong>
            &middot; Data dihitung s.d. hari ini
            &middot; Klik baris karyawan untuk melihat rincian Masuk, Lupa Absen, dan Tidak Hadir
        </p>
    </div>

    {{-- ===== TABEL REKAP ===== --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden">
        @if(count($rekap) === 0)
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada data karyawan</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Pastikan ada karyawan aktif di sistem</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/95 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider">
                            <th class="px-4 py-3.5 text-slate-500 dark:text-slate-400 w-12 text-center">No</th>
                            <th class="px-4 py-3.5 text-slate-600 dark:text-slate-300 min-w-[220px]">Nama Karyawan</th>
                            <th class="px-4 py-3.5 text-center text-emerald-600 dark:text-emerald-400 w-32">✓ Masuk</th>
                            <th class="px-4 py-3.5 text-center text-amber-600 dark:text-amber-400 w-32">⏰ Lupa Absen</th>
                            <th class="px-4 py-3.5 text-center text-rose-600 dark:text-rose-400 w-36">✗ Tidak Hadir / Libur</th>
                            <th class="px-4 py-3.5 text-center text-slate-500 dark:text-slate-400 w-24">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                        @foreach($rekap as $i => $row)
                        @php $emp = $row['employee']; @endphp
                        {{-- row utama --}}
                        <tr
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 cursor-pointer transition-colors duration-150"
                            :class="{{ $i }} === openRow ? 'bg-blue-50/40 dark:bg-slate-800/60' : ''"
                            @click="toggleRow({{ $i }})"
                            x-show="shouldShow('{{ addslashes(strtolower($emp->name)) }}', '{{ addslashes(strtolower($emp->division?->name ?? '')) }}', {{ $row['total_lupa_absen'] }}, {{ $row['total_tidak_hadir'] }})"
                        >
                            <td class="px-4 py-3.5 text-slate-400 dark:text-slate-500 font-mono text-xs text-center">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-xs">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100 truncate text-sm leading-tight">{{ $emp->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mt-0.5">{{ $emp->division?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- 1. Masuk --}}
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center min-w-[2.25rem] h-8 px-2.5 rounded-lg font-bold text-sm tabular-nums
                                    {{ $row['total_masuk'] > 0 ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                    {{ $row['total_masuk'] }}
                                </span>
                            </td>
                            {{-- 2. Lupa Absen --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($row['total_lupa_absen'] > 0)
                                    <span class="inline-flex items-center justify-center min-w-[2.25rem] h-8 px-2.5 rounded-lg bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/25 font-bold text-sm tabular-nums">
                                        {{ $row['total_lupa_absen'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 font-medium text-sm">&mdash;</span>
                                @endif
                            </td>
                            {{-- 3. Tidak Hadir / Libur --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($row['total_tidak_hadir'] > 0)
                                    <span class="inline-flex items-center justify-center min-w-[2.25rem] h-8 px-2.5 rounded-lg bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/25 font-bold text-sm tabular-nums">
                                        {{ $row['total_tidak_hadir'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 font-medium text-sm">&mdash;</span>
                                @endif
                            </td>
                            {{-- Toggle Button --}}
                            <td class="px-4 py-3.5 text-center">
                                <button type="button"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                        :class="{{ $i }} === openRow
                                            ? 'bg-blue-600 text-white border-blue-600 shadow-xs'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                    <span x-text="{{ $i }} === openRow ? 'Tutup' : 'Detail'"></span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{{ $i }} === openRow ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </td>
                        </tr>

                        {{-- detail row (Accordion Tray) --}}
                        <tr
                            x-show="{{ $i }} === openRow"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="border-b border-slate-100 dark:border-slate-800"
                            style="display:none"
                        >
                            <td colspan="6" class="px-4 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5">

                                    {{-- ==================== KOLOM 1 (KIRI): MASUK ==================== --}}
                                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col" x-data="{ showAllMasuk{{ $i }}: false }">
                                        <div class="px-3.5 py-2.5 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-800/50 flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide">
                                                    Masuk &mdash; {{ count($row['masuk_list']) }} Hari
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md bg-emerald-600 text-white shadow-xs">
                                                {{ count($row['masuk_list']) }}
                                            </span>
                                        </div>
                                        @if(count($row['masuk_list']) === 0)
                                            <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic px-3">Belum ada catatan masuk</p>
                                        @else
                                            <div class="divide-y divide-slate-100 dark:divide-slate-700/60" :class="showAllMasuk{{ $i }} ? 'max-h-none' : 'max-h-64 overflow-y-hidden'" style="transition: max-height 0.3s ease;">
                                                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                                                @foreach($row['masuk_list'] as $item)
                                                    <div class="py-2 first:pt-0 last:pb-0 flex items-start justify-between gap-2 text-xs">
                                                        <div class="min-w-0">
                                                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                                            <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 font-medium">{!! $item['jam_detail'] !!}</p>
                                                            @if($item['note'])
                                                                <p class="text-slate-400 dark:text-slate-500 text-[10px] italic mt-0.5">Catatan: {{ $item['note'] }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                                            @if($item['lupa_tag'])
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                                                                    {{ $item['lupa_tag'] }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $item['status'] === 'Terlambat' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' }}">
                                                                    {{ $item['status'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                </div>
                                            </div>
                                            @if(count($row['masuk_list']) > 5)
                                                <div class="px-3 py-2 border-t border-slate-100 dark:border-slate-700/60">
                                                    <button type="button" @click="showAllMasuk{{ $i }} = !showAllMasuk{{ $i }}" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 transition-colors">
                                                        <span x-text="showAllMasuk{{ $i }} ? 'Ringkas Tampilan ↑' : 'Lihat Semua ({{ count($row["masuk_list"]) }} Hari) ↓'"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- ==================== KOLOM 2 (TENGAH): LUPA ABSEN ==================== --}}
                                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col" x-data="{ showAllLupa{{ $i }}: false }">
                                        <div class="px-3.5 py-2.5 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-100 dark:border-amber-800/50 flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                                                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">
                                                    Lupa Absen &mdash; {{ count($row['lupa_absen_list']) }} Hari
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($row['lupa_absen_list']) > 0 ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' }}">
                                                {{ count($row['lupa_absen_list']) }}
                                            </span>
                                        </div>
                                        @if(count($row['lupa_absen_list']) === 0)
                                            <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic px-3">Tidak ada lupa absen ✓</p>
                                        @else
                                            <div :class="showAllLupa{{ $i }} ? 'max-h-none' : 'max-h-64 overflow-y-hidden'" style="transition: max-height 0.3s ease;">
                                                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                                                @foreach($row['lupa_absen_list'] as $item)
                                                    <div class="py-2 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                                                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                                        <div class="min-w-0">
                                                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                                            <p class="text-amber-700 dark:text-amber-400 text-[11px] font-medium mt-0.5">{{ $item['detail'] }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                </div>
                                            </div>
                                            @if(count($row['lupa_absen_list']) > 5)
                                                <div class="px-3 py-2 border-t border-slate-100 dark:border-slate-700/60">
                                                    <button type="button" @click="showAllLupa{{ $i }} = !showAllLupa{{ $i }}" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-semibold text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-200 transition-colors">
                                                        <span x-text="showAllLupa{{ $i }} ? 'Ringkas Tampilan ↑' : 'Lihat Semua ({{ count($row["lupa_absen_list"]) }} Hari) ↓'"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- ==================== KOLOM 3 (KANAN): TIDAK HADIR / LIBUR ==================== --}}
                                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col" x-data="{ showAllTidak{{ $i }}: false }">
                                        <div class="px-3.5 py-2.5 bg-rose-50 dark:bg-rose-950/40 border-b border-rose-100 dark:border-rose-800/50 flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                                                <span class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wide">
                                                    Tidak Hadir / Libur &mdash; {{ count($row['tidak_hadir_list']) }} Hari
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($row['tidak_hadir_list']) > 0 ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400' }}">
                                                {{ count($row['tidak_hadir_list']) }}
                                            </span>
                                        </div>
                                        @if(count($row['tidak_hadir_list']) === 0)
                                            <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic px-3">Tidak ada hari libur / tidak hadir</p>
                                        @else
                                            <div :class="showAllTidak{{ $i }} ? 'max-h-none' : 'max-h-64 overflow-y-hidden'" style="transition: max-height 0.3s ease;">
                                                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                                                @foreach($row['tidak_hadir_list'] as $item)
                                                    <div class="py-2 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                                                        <span class="mt-1 w-1.5 h-1.5 rounded-full {{ $item['is_holiday'] ? 'bg-slate-400' : 'bg-rose-500' }} flex-shrink-0"></span>
                                                        <div class="min-w-0">
                                                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                                            <p class="{{ $item['is_holiday'] ? 'text-slate-500 dark:text-slate-400' : 'text-rose-600 dark:text-rose-400 font-medium' }} text-[11px] mt-0.5">{{ $item['keterangan'] }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                </div>
                                            </div>
                                            @if(count($row['tidak_hadir_list']) > 5)
                                                <div class="px-3 py-2 border-t border-slate-100 dark:border-slate-700/60">
                                                    <button type="button" @click="showAllTidak{{ $i }} = !showAllTidak{{ $i }}" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-semibold text-rose-700 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-200 transition-colors">
                                                        <span x-text="showAllTidak{{ $i }} ? 'Ringkas Tampilan ↑' : 'Lihat Semua ({{ count($row["tidak_hadir_list"]) }} Hari) ↓'"></span>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<script>
function rekapBulanan() {
    return {
        openRow: null,
        search: '',
        filter: 'all',
        init() {},
        toggleRow(index) {
            this.openRow = this.openRow === index ? null : index;
        },
        shouldShow(name, division, lupaTotal, tidakHadir) {
            const q = this.search.toLowerCase().trim();
            if (q && !name.includes(q) && !division.includes(q)) return false;
            if (this.filter === 'lupa' && lupaTotal === 0) return false;
            if (this.filter === 'tidak_hadir' && tidakHadir === 0) return false;
            return true;
        }
    };
}
</script>
@endsection
