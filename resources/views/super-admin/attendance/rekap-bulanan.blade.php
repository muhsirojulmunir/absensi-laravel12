@extends('layouts.master')
@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="space-y-6 animate-fade-in" x-data="rekapBulanan()" x-init="init()">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Rekap Absensi Bulanan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Akumulasi kehadiran, ketidakhadiran, dan lupa absen seluruh karyawan
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('super-admin.attendance.rekap-bulanan') }}" id="monthForm">
                <div class="flex items-center gap-2">
                    <label for="month" class="text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">Pilih Bulan:</label>
                    <input
                        type="month"
                        name="month"
                        id="month"
                        value="{{ $month }}"
                        max="{{ now()->format('Y-m') }}"
                        onchange="document.getElementById('monthForm').submit()"
                        class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                    >
                </div>
            </form>
        </div>
    </div>

    {{-- ===== SUMMARY CARDS ===== --}}
    @php
        $totalEmployees   = count($rekap);
        $totalMasukAll    = collect($rekap)->sum('total_masuk');
        $totalTidakHadir  = collect($rekap)->sum('total_tidak_hadir');
        $totalLupaAbsen   = collect($rekap)->sum('lupa_absen_total');
        $monthLabel       = \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y');
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl p-4 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-indigo-100">Total Karyawan</p>
                    <p class="text-2xl font-bold">{{ $totalEmployees }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-emerald-100">Total Hari Masuk</p>
                    <p class="text-2xl font-bold">{{ $totalMasukAll }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-4 bg-gradient-to-br from-rose-500 to-rose-700 text-white shadow-md">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-rose-100">Total Tidak Hadir</p>
                    <p class="text-2xl font-bold">{{ $totalTidakHadir }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-4 bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-md">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-amber-100">Total Lupa Absen</p>
                    <p class="text-2xl font-bold">{{ $totalLupaAbsen }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SEARCH & FILTER ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari nama karyawan atau divisi..." class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm text-gray-500 dark:text-gray-400">Filter:</span>
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Semua</button>
                <button @click="filter = 'lupa'" :class="filter === 'lupa' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Ada Lupa Absen</button>
                <button @click="filter = 'tidak_hadir'" :class="filter === 'tidak_hadir' ? 'bg-rose-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Ada Tidak Hadir</button>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2.5">
            Periode: <strong class="text-gray-600 dark:text-gray-300">{{ $monthLabel }}</strong>
            &middot; Data dihitung s.d. hari ini
            &middot; Klik baris untuk melihat detail tanggal
        </p>
    </div>

    {{-- ===== TABEL REKAP ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        @if(count($rekap) === 0)
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data karyawan</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Pastikan ada karyawan aktif di sistem</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/60 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3.5 text-left font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider w-12">No</th>
                            <th class="px-4 py-3.5 text-left font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Karyawan</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-emerald-600 dark:text-emerald-400 text-xs uppercase tracking-wider w-28">✓ Masuk</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-rose-600 dark:text-rose-400 text-xs uppercase tracking-wider w-32">✗ Tidak Hadir</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-amber-600 dark:text-amber-400 text-xs uppercase tracking-wider w-32">⏰ Lupa Absen</th>
                            <th class="px-4 py-3.5 text-center font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider w-24">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $i => $row)
                        @php $emp = $row['employee']; @endphp
                        {{-- row utama --}}
                        <tr
                            class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 cursor-pointer transition-colors duration-150"
                            :class="{{ $i }} === openRow ? 'bg-indigo-50/60 dark:bg-indigo-900/10' : ''"
                            @click="toggleRow({{ $i }})"
                            x-show="shouldShow('{{ addslashes(strtolower($emp->name)) }}', '{{ addslashes(strtolower($emp->division?->name ?? '')) }}', {{ $row['lupa_absen_total'] }}, {{ $row['total_tidak_hadir'] }})"
                        >
                            <td class="px-4 py-3.5 text-gray-400 dark:text-gray-500 font-mono text-xs">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800 dark:text-white truncate">{{ $emp->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $emp->division?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-full font-bold text-base
                                    {{ $row['total_masuk'] > 0 ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' }}">
                                    {{ $row['total_masuk'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['total_tidak_hadir'] > 0)
                                    <span class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 font-bold text-base">
                                        {{ $row['total_tidak_hadir'] }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-medium text-base">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['lupa_absen_total'] > 0)
                                    <span class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-bold text-base">
                                        {{ $row['lupa_absen_total'] }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-medium text-base">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{{ $i }} === openRow ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </td>
                        </tr>
                        {{-- detail row --}}
                        <tr
                            x-show="{{ $i }} === openRow"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="border-b border-gray-100 dark:border-gray-700"
                            style="display:none"
                        >
                            <td colspan="6" class="px-4 pb-5 pt-0 bg-gray-50/80 dark:bg-gray-700/20">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3">
                                    {{-- Tidak Hadir --}}
                                    <div class="rounded-xl border border-rose-200 dark:border-rose-800/40 overflow-hidden">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-900/20 border-b border-rose-200 dark:border-rose-800/40">
                                            <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                                            <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wide">
                                                Tidak Hadir &mdash; {{ count($row['tidak_hadir_dates']) }} hari
                                            </span>
                                        </div>
                                        <div class="p-3 max-h-52 overflow-y-auto">
                                            @if(count($row['tidak_hadir_dates']) === 0)
                                                <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4 italic">Tidak ada hari tidak hadir 🎉</p>
                                            @else
                                                <ul class="space-y-1.5">
                                                    @foreach($row['tidak_hadir_dates'] as $d)
                                                        <li class="flex items-start gap-2 text-xs text-gray-700 dark:text-gray-300">
                                                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                                                            {{ $d['label'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Lupa Absen Masuk --}}
                                    <div class="rounded-xl border border-amber-200 dark:border-amber-800/40 overflow-hidden">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/40">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">
                                                Lupa Absen Masuk &mdash; {{ count($row['lupa_absen_masuk']) }}
                                            </span>
                                        </div>
                                        <div class="p-3 max-h-52 overflow-y-auto">
                                            @if(count($row['lupa_absen_masuk']) === 0)
                                                <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4 italic">Tidak ada lupa absen masuk ✓</p>
                                            @else
                                                <ul class="space-y-2">
                                                    @foreach($row['lupa_absen_masuk'] as $d)
                                                        <li class="text-xs">
                                                            <div class="flex items-start gap-2">
                                                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                                <div>
                                                                    <p class="font-medium text-gray-700 dark:text-gray-300">{{ $d['label'] }}</p>
                                                                    <p class="text-gray-400 dark:text-gray-500">{{ $d['detail'] }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Lupa Absen Pulang --}}
                                    <div class="rounded-xl border border-orange-200 dark:border-orange-800/40 overflow-hidden">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800/40">
                                            <span class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0"></span>
                                            <span class="text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wide">
                                                Lupa Absen Pulang &mdash; {{ count($row['lupa_absen_pulang']) }}
                                            </span>
                                        </div>
                                        <div class="p-3 max-h-52 overflow-y-auto">
                                            @if(count($row['lupa_absen_pulang']) === 0)
                                                <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4 italic">Tidak ada lupa absen pulang ✓</p>
                                            @else
                                                <ul class="space-y-2">
                                                    @foreach($row['lupa_absen_pulang'] as $d)
                                                        <li class="text-xs">
                                                            <div class="flex items-start gap-2">
                                                                <span class="mt-1 w-1.5 h-1.5 rounded-full bg-orange-400 flex-shrink-0"></span>
                                                                <div>
                                                                    <p class="font-medium text-gray-700 dark:text-gray-300">{{ $d['label'] }}</p>
                                                                    <p class="text-gray-400 dark:text-gray-500">{{ $d['detail'] }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
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
