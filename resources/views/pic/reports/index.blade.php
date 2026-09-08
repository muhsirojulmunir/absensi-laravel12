@extends('layouts.master')

@section('title', 'Laporan Presensi')

@section('content')
@php
    $reportRouteName = $reportRouteName ?? 'pic.reports.index';
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-11 h-11 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/20 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Laporan Presensi</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Rekapitulasi dan log absensi bulanan seluruh karyawan</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route($reportRouteName) }}">
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
                
                {{-- Employee Select --}}
                <div class="md:col-span-6 space-y-1.5">
                    <label for="employee_id" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Karyawan</label>
                    <select name="employee_id" id="employee_id"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 text-sm font-medium text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        <option value="">-- Semua Karyawan --</option>
                        @php
                            $staffEmployees = $employees->filter(fn($e) => $e->role->slug === 'karyawan');
                            $ramayanaEmployees = $employees->filter(fn($e) => $e->role->slug === 'karyawan_ramayana');
                        @endphp
                        @if($staffEmployees->count() > 0 && $ramayanaEmployees->count() > 0)
                            <optgroup label="💼 Staff Kantor">
                                @foreach($staffEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="🛍️ Karyawan Ramayana">
                                @foreach($ramayanaEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </optgroup>
                        @else
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Month Picker --}}
                <div class="md:col-span-4 space-y-1.5">
                    <label for="month" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Bulan</label>
                    <input type="month" name="month" id="month" value="{{ $month }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 text-sm font-medium text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                </div>

                {{-- Submit Button --}}
                <div class="md:col-span-2">
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-150 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Single Employee Full View --}}
    @if($report)
        <div class="space-y-8">
            {{-- Employee Card & Summary --}}
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-7">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-base shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($report['employee']->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $report['employee']->name }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                                {{ $report['employee']->location->name ?? ($report['employee']->division->name ?? 'Karyawan') }} • 
                                Periode: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ \Carbon\Carbon::parse($report['month'] . '-01')->locale('id')->translatedFormat('F Y') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="printRekapKecil(
                                    {{ json_encode($report['employee']->name) }},
                                    {{ json_encode($report['employee']->location->name ?? ($report['employee']->division->name ?? 'Karyawan')) }},
                                    {{ json_encode(\Carbon\Carbon::parse($report['month'] . '-01')->locale('id')->translatedFormat('F Y')) }},
                                    {{ json_encode((string)$report['summary']['total_masuk']) }},
                                    {{ json_encode((string)$report['summary']['total_off_days']) }}
                                )"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 transition-colors shadow-xs active:scale-95">
                            <svg class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Print Rekap</span>
                        </button>
                        <a href="{{ route($reportRouteName, ['month' => $month]) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            <span>Semua Karyawan</span>
                        </a>
                    </div>
                </div>

                {{-- Metric Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-4.5">
                    <div class="bg-emerald-500/10 dark:bg-emerald-950/30 border border-emerald-500/20 dark:border-emerald-800/40 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $report['summary']['total_masuk'] }}</p>
                        <p class="text-[10px] font-bold text-emerald-700/80 dark:text-emerald-400/80 uppercase tracking-wider mt-0.5">Total Masuk ({{ $report['summary']['total_present'] }} Hadir, {{ $report['summary']['total_late'] }} Telat)</p>
                    </div>
                    <div class="bg-red-500/10 dark:bg-red-950/30 border border-red-500/20 dark:border-red-900/40 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-red-600 dark:text-red-400">{{ $report['summary']['total_off_days'] }}</p>
                        <p class="text-[10px] font-bold text-red-700/80 dark:text-red-400/80 uppercase tracking-wider mt-0.5">Libur / Tdk Hadir</p>
                    </div>
                    <div class="bg-amber-500/10 dark:bg-amber-950/30 border border-amber-500/20 dark:border-amber-800/40 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $report['summary']['total_lupa_absen'] }}</p>
                        <p class="text-[10px] font-bold text-amber-700/80 dark:text-amber-400/80 uppercase tracking-wider mt-0.5">Lupa Absen</p>
                    </div>
                    <div class="bg-sky-500/10 dark:bg-sky-950/30 border border-sky-500/20 dark:border-sky-800/40 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400">{{ $report['summary']['total_leave'] + $report['summary']['total_sick'] }}</p>
                        <p class="text-[10px] font-bold text-sky-700/80 dark:text-sky-400/80 uppercase tracking-wider mt-0.5">Izin & Sakit</p>
                    </div>
                </div>

                {{-- Panel Rincian Presensi 3 Kolom --}}
                <div class="mt-8 pt-7 border-t border-slate-200/70 dark:border-slate-800">
                    <div class="flex items-center space-x-2.5 mb-5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Rincian Akumulasi Presensi: {{ $report['employee']->name }}
                        </h3>
                    </div>
                    @include('pic.reports.partials.accumulation-panels', [
                        'masukList'       => $report['masuk_list'],
                        'lupaAbsenList'   => $report['lupa_absen_list'],
                        'tidakHadirList'  => $report['tidak_hadir_list'],
                    ])
                </div>
            </div>

            {{-- Daily Attendance Table --}}
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Log Presensi Harian</span>
                    </h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $report['attendances']->count() }} catatan</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200/70 dark:border-slate-800">
                                <th class="text-left px-5 py-3">Tanggal</th>
                                <th class="text-left px-5 py-3">Jam Masuk</th>
                                <th class="text-left px-5 py-3">Jam Pulang</th>
                                <th class="text-left px-5 py-3">Estimasi Pulang</th>
                                <th class="text-left px-5 py-3">Status</th>
                                <th class="text-left px-5 py-3">Pulang Cepat</th>
                                <th class="text-left px-5 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($report['attendances'] as $att)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}
                                        <span class="text-[10px] text-slate-400 block font-normal">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                    </td>
                                    <td class="px-5 py-3 font-medium">
                                        @if($att->check_in)
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-medium">
                                        @if($att->check_out)
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold font-mono">{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum Pulang</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 font-mono text-xs">
                                        {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($att->status === 'Hadir')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                Hadir
                                            </span>
                                        @elseif($att->status === 'Terlambat')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                Telat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                {{ ucfirst($att->status ?? '-') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($att->is_pulang_cepat)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                                Ya
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 text-xs">
                                        {{ $att->note ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada data absensi di bulan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Leaves Table (If Any) --}}
            @if($report['leaves']->count() > 0)
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Riwayat Izin & Cuti</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200/70 dark:border-slate-800">
                                <th class="text-left px-5 py-3">Jenis</th>
                                <th class="text-left px-5 py-3">Rentang Tanggal</th>
                                <th class="text-left px-5 py-3">Alasan</th>
                                <th class="text-left px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @foreach($report['leaves'] as $leave)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $leave->type }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $leave->start_date->locale('id')->translatedFormat('d M Y') }} s/d {{ $leave->end_date->locale('id')->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs">{{ $leave->reason }}</td>
                                <td class="px-5 py-3">
                                    @if($leave->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Disetujui</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

    {{-- All Employees Table View --}}
    @elseif($allReports->count() > 0)
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            
            {{-- Table Header Info --}}
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"></path></svg>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">Rekapitulasi Presensi Karyawan</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        {{ \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y') }}
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $allReports->count() }} Karyawan</span>
                </div>
            </div>

            {{-- Responsive Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="sticky top-0 z-10 bg-slate-50/95 dark:bg-slate-900/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 pl-6 pr-4 min-w-[220px]">Nama Karyawan</th>
                            <th class="py-3.5 px-2.5 text-center w-[85px] text-emerald-600 dark:text-emerald-400">Masuk</th>
                            <th class="py-3.5 px-2.5 text-center w-[95px] text-rose-600 dark:text-rose-400">Libur / Tdk</th>
                            <th class="py-3.5 px-2.5 text-center w-[95px] text-amber-600 dark:text-amber-400">Lupa Absen</th>
                            <th class="py-3.5 px-2.5 text-center w-[70px]">Izin</th>
                            <th class="py-3.5 px-2.5 text-center w-[70px]">Sakit</th>
                            <th class="py-3.5 px-2.5 text-center w-[75px]">Total Log</th>
                            <th class="py-3.5 pl-3 pr-6 text-right w-[175px]">Aksi</th>
                        </tr>
                    </thead>

                    @php
                        $staffReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan')->sortByDesc(fn($row) => [$row['summary']['total_masuk'], $row['summary']['total_present']]);
                        $ramayanaReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan_ramayana')->sortByDesc(fn($row) => [$row['summary']['total_masuk'], $row['summary']['total_present']]);
                    @endphp

                    {{-- Staff Section --}}
                    @if($staffReports->count() > 0)
                        <tbody>
                            <tr>
                                <td colspan="8" class="pt-4 pb-1.5 pl-6 pr-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Staff Kantor</span>
                                        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-600">{{ $staffReports->count() }} orang</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @foreach($staffReports as $row)
                            <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors cursor-pointer"
                                    @click="expanded = !expanded"
                                    :class="expanded ? 'bg-blue-50/30 dark:bg-slate-800/40' : ''">

                                    {{-- Name & Avatar --}}
                                    <td class="py-3 pl-6 pr-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm ring-2 ring-white dark:ring-slate-900">
                                                {{ strtoupper(substr($row['employee']->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate leading-tight">{{ $row['employee']->name }}</p>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5 truncate">
                                                    {{ $row['employee']->location->name ?? ($row['employee']->division->name ?? 'Staff') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Counts --}}
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_masuk'], 'activeClass' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-extrabold border border-emerald-500/30'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_off_days'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_lupa_absen'], 'activeClass' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_leave'], 'activeClass' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_sick'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold rounded-lg tabular-nums bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60">
                                            {{ $row['summary']['total_attendance_records'] }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3 pl-3 pr-6 text-right" @click.stop>
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            <button type="button" 
                                                    onclick="printRekapKecil(
                                                        {{ json_encode($row['employee']->name) }},
                                                        {{ json_encode($row['employee']->location->name ?? ($row['employee']->division->name ?? 'Staff')) }},
                                                        {{ json_encode(\Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y')) }},
                                                        {{ json_encode((string)$row['summary']['total_masuk']) }},
                                                        {{ json_encode((string)$row['summary']['total_off_days']) }}
                                                    )"
                                                    title="Print Rekap Ringkas"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-all shadow-xs active:scale-95">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                                <span class="hidden sm:inline">Print</span>
                                            </button>
                                            <button type="button" @click="expanded = !expanded"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                                    :class="expanded
                                                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-500/20'
                                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                                <span x-text="expanded ? 'Tutup' : 'Detail & Log'"></span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Nested Detail Accordion Tray --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-slate-50 dark:bg-slate-900">
                                    <td colspan="8" class="p-4 sm:p-5 border-y border-slate-200/80 dark:border-slate-800">
                                        <div class="space-y-3.5 max-w-5xl mx-auto">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                                        Rincian Presensi: {{ $row['employee']->name }}
                                                    </h4>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" 
                                                            onclick="printRekapKecil(
                                                                {{ json_encode($row['employee']->name) }},
                                                                {{ json_encode($row['employee']->location->name ?? ($row['employee']->division->name ?? 'Staff')) }},
                                                                {{ json_encode(\Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y')) }},
                                                                {{ json_encode((string)$row['summary']['total_masuk']) }},
                                                                {{ json_encode((string)$row['summary']['total_off_days']) }}
                                                            )"
                                                            class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 flex items-center gap-1 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                        <span>Print Rekap</span>
                                                    </button>
                                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                                    <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}" 
                                                       class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                                        <span>Buka Halaman Penuh</span>
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- 3 Panel Akumulasi Masuk, Lupa Absen, dan Tidak Hadir --}}
                                            @include('pic.reports.partials.accumulation-panels', [
                                                'masukList'       => $row['masuk_list'],
                                                'lupaAbsenList'   => $row['lupa_absen_list'],
                                                'tidakHadirList'  => $row['tidak_hadir_list'],
                                            ])
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        @endforeach

                    @endif

                    {{-- Ramayana Section --}}
                    @if($ramayanaReports->count() > 0)
                        <tbody>
                            <tr>
                                <td colspan="8" class="pt-4 pb-1.5 pl-6 pr-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-500"></span>
                                        <span class="text-[10px] font-bold text-fuchsia-600 dark:text-fuchsia-400 uppercase tracking-widest">Karyawan Ramayana</span>
                                        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-600">{{ $ramayanaReports->count() }} orang</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @foreach($ramayanaReports as $row)
                            <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors cursor-pointer" 
                                    @click="expanded = !expanded"
                                    :class="expanded ? 'bg-fuchsia-50/30 dark:bg-slate-800/40' : ''">
                                    
                                    {{-- Name & Avatar --}}
                                    <td class="py-3 pl-6 pr-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm ring-2 ring-white dark:ring-slate-900">
                                                {{ strtoupper(substr($row['employee']->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate leading-tight">{{ $row['employee']->name }}</p>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5 truncate">
                                                    {{ $row['employee']->location->name ?? ($row['employee']->division->name ?? 'Ramayana') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Counts --}}
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_masuk'], 'activeClass' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-extrabold border border-emerald-500/30'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_off_days'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_lupa_absen'], 'activeClass' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_leave'], 'activeClass' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_sick'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-2.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold rounded-lg tabular-nums bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60">
                                            {{ $row['summary']['total_attendance_records'] }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3 pl-3 pr-6 text-right" @click.stop>
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            <button type="button" 
                                                    onclick="printRekapKecil(
                                                        {{ json_encode($row['employee']->name) }},
                                                        {{ json_encode($row['employee']->location->name ?? ($row['employee']->division->name ?? 'Ramayana')) }},
                                                        {{ json_encode(\Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y')) }},
                                                        {{ json_encode((string)$row['summary']['total_masuk']) }},
                                                        {{ json_encode((string)$row['summary']['total_off_days']) }}
                                                    )"
                                                    title="Print Rekap Ringkas"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-fuchsia-600 dark:hover:text-fuchsia-400 transition-all shadow-xs active:scale-95">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                                <span class="hidden sm:inline">Print</span>
                                            </button>
                                            <button type="button" @click="expanded = !expanded"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                                    :class="expanded 
                                                        ? 'bg-fuchsia-600 text-white border-fuchsia-600 shadow-sm shadow-fuchsia-500/20' 
                                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                                <span x-text="expanded ? 'Tutup' : 'Detail & Log'"></span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Nested Detail Accordion Tray --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-slate-50 dark:bg-slate-900">
                                    <td colspan="8" class="p-4 sm:p-5 border-y border-slate-200/80 dark:border-slate-800">
                                        <div class="space-y-3.5 max-w-5xl mx-auto">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-fuchsia-500"></span>
                                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                                        Rincian Presensi: {{ $row['employee']->name }}
                                                    </h4>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" 
                                                            onclick="printRekapKecil(
                                                                {{ json_encode($row['employee']->name) }},
                                                                {{ json_encode($row['employee']->location->name ?? ($row['employee']->division->name ?? 'Ramayana')) }},
                                                                {{ json_encode(\Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y')) }},
                                                                {{ json_encode((string)$row['summary']['total_masuk']) }},
                                                                {{ json_encode((string)$row['summary']['total_off_days']) }}
                                                            )"
                                                            class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:text-fuchsia-600 dark:hover:text-fuchsia-400 flex items-center gap-1 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                        <span>Print Rekap</span>
                                                    </button>
                                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                                    <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}" 
                                                       class="text-[11px] font-semibold text-fuchsia-600 dark:text-fuchsia-400 hover:underline flex items-center gap-1">
                                                        <span>Buka Halaman Penuh</span>
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- 3 Panel Akumulasi Masuk, Lupa Absen, dan Tidak Hadir --}}
                                            @include('pic.reports.partials.accumulation-panels', [
                                                'masukList'       => $row['masuk_list'],
                                                'lupaAbsenList'   => $row['lupa_absen_list'],
                                                'tidakHadirList'  => $row['tidak_hadir_list'],
                                            ])

                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        @endforeach
                    @endif

                    @if($staffReports->isEmpty() && $ramayanaReports->isEmpty())
                        <tbody>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500 text-sm">
                                    Tidak ada data karyawan ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-base font-bold text-slate-700 dark:text-slate-300">Belum Ada Data</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Pilih filter karyawan atau bulan di atas untuk menampilkan data.</p>
        </div>
    @endif

</div>

@push('scripts')
<script>
function printRekapKecil(name, location, period, masuk, tidakMasuk) {
    let printIframe = document.getElementById('print-slip-iframe');
    if (!printIframe) {
        printIframe = document.createElement('iframe');
        printIframe.id = 'print-slip-iframe';
        printIframe.style.position = 'fixed';
        printIframe.style.right = '0';
        printIframe.style.bottom = '0';
        printIframe.style.width = '0';
        printIframe.style.height = '0';
        printIframe.style.border = '0';
        printIframe.style.visibility = 'hidden';
        document.body.appendChild(printIframe);
    }

    const slipHtml = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi - ${name}</title>
    <style>
        @page {
            size: auto;
            margin: 6mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: #000000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #000000;
            background: #ffffff;
            padding: 4px;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        .slip-card {
            width: 78mm;
            max-width: 100%;
            border: 1px dashed #000000;
            border-radius: 4px;
            padding: 10px 12px;
            background: #ffffff;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #000000;
        }
        .period {
            font-size: 10.5px;
            font-weight: 700;
            color: #000000;
            margin-top: 1px;
        }
        .info-table {
            width: 100%;
            font-size: 10.5px;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000000;
            padding-bottom: 6px;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
            color: #000000;
        }
        .info-label {
            width: 54px;
            color: #000000;
            font-weight: 600;
        }
        .info-sep {
            width: 8px;
            color: #000000;
            text-align: center;
        }
        .info-value {
            color: #000000;
            font-weight: 700;
        }
        .recap-box {
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 4px;
            padding: 6px 8px;
        }
        .recap-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3.5px 0;
        }
        .recap-row:not(:last-child) {
            border-bottom: 1px dashed #000000;
        }
        .recap-label {
            font-size: 10.5px;
            font-weight: 600;
            color: #000000;
        }
        .recap-badge {
            font-size: 11px;
            font-weight: 800;
            color: #000000;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
                display: block;
            }
            .slip-card {
                width: 78mm !important;
                max-width: 78mm !important;
                border: 1px dashed #000000 !important;
                border-radius: 0 !important;
                padding: 8px 10px !important;
                page-break-inside: avoid;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="slip-card">
        <div class="header">
            <div class="title">REKAP PRESENSI</div>
            <div class="period">${period}</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Nama</td>
                <td class="info-sep">:</td>
                <td class="info-value">${name}</td>
            </tr>
            <tr>
                <td class="info-label">Counter</td>
                <td class="info-sep">:</td>
                <td class="info-value">${location}</td>
            </tr>
        </table>

        <div class="recap-box">
            <div class="recap-row">
                <span class="recap-label">Jumlah Masuk</span>
                <span class="recap-badge">${masuk} Hari</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Jumlah Tidak Masuk</span>
                <span class="recap-badge">${tidakMasuk} Hari</span>
            </div>
        </div>
    </div>
</body>
</html>`;

    const iframeDoc = printIframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(slipHtml);
    iframeDoc.close();

    setTimeout(() => {
        try {
            printIframe.contentWindow.focus();
            printIframe.contentWindow.print();
        } catch (err) {
            const printWin = window.open('', '_blank', 'width=320,height=400');
            if (printWin) {
                printWin.document.write(slipHtml);
                printWin.document.close();
                printWin.focus();
                printWin.print();
            }
        }
    }, 250);
}
</script>
@endpush
@endsection
