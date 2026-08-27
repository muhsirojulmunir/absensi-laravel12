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
        <div class="space-y-6">
            {{-- Employee Card & Summary --}}
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
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
                    <div>
                        <a href="{{ route($reportRouteName, ['month' => $month]) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            <span>Semua Karyawan</span>
                        </a>
                    </div>
                </div>

                {{-- Metric Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-3.5 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $report['summary']['total_present'] }}</p>
                        <p class="text-[10px] font-bold text-emerald-600/80 dark:text-emerald-400/70 uppercase tracking-wider mt-0.5">Hadir</p>
                    </div>
                    <div class="bg-amber-50/60 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl p-3.5 text-center">
                        <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $report['summary']['total_late'] }}</p>
                        <p class="text-[10px] font-bold text-amber-600/80 dark:text-amber-400/70 uppercase tracking-wider mt-0.5">Terlambat</p>
                    </div>
                    <div class="bg-sky-50/60 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/30 rounded-xl p-3.5 text-center">
                        <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400">{{ $report['summary']['total_leave'] }}</p>
                        <p class="text-[10px] font-bold text-sky-600/80 dark:text-sky-400/70 uppercase tracking-wider mt-0.5">Izin / Cuti</p>
                    </div>
                    <div class="bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-xl p-3.5 text-center">
                        <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ $report['summary']['total_sick'] }}</p>
                        <p class="text-[10px] font-bold text-rose-600/80 dark:text-rose-400/70 uppercase tracking-wider mt-0.5">Sakit</p>
                    </div>
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
                            <th class="py-3.5 pl-6 pr-4 min-w-[240px]">Nama Karyawan</th>
                            <th class="py-3.5 px-3 text-center w-[85px]">Hadir</th>
                            <th class="py-3.5 px-3 text-center w-[85px]">Telat</th>
                            <th class="py-3.5 px-3 text-center w-[85px]">Izin</th>
                            <th class="py-3.5 px-3 text-center w-[85px]">Sakit</th>
                            <th class="py-3.5 px-3 text-center w-[90px]">Total</th>
                            <th class="py-3.5 pl-3 pr-6 text-right w-[140px]">Aksi</th>
                        </tr>
                    </thead>

                    @php
                        $staffReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan');
                        $ramayanaReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan_ramayana');
                    @endphp

                    {{-- Staff Section --}}
                    @if($staffReports->count() > 0)
                        <tbody>
                            <tr>
                                <td colspan="7" class="pt-4 pb-1.5 pl-6 pr-4">
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
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_present'], 'activeClass' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_late'], 'activeClass' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_leave'], 'activeClass' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_sick'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold rounded-lg tabular-nums bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60">
                                            {{ $row['summary']['total_attendance_records'] }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3 pl-3 pr-6 text-right" @click.stop>
                                        <button type="button" @click="expanded = !expanded"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                                :class="expanded
                                                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-500/20'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                            <span x-text="expanded ? 'Tutup' : 'Log Absen'"></span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Nested Detail Accordion Tray --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-slate-50/70 dark:bg-[#070b13]">
                                    <td colspan="7" class="p-4 sm:p-5 border-y border-slate-200/80 dark:border-slate-800">
                                        <div class="space-y-3.5 max-w-5xl mx-auto">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                                        Log Harian: {{ $row['employee']->name }}
                                                    </h4>
                                                </div>
                                                <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}" 
                                                   class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                                    <span>Buka Halaman Penuh</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            </div>

                                            {{-- Mini Daily Table --}}
                                            @if($row['attendances']->count() > 0)
                                                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-xs">
                                                    <div class="max-h-64 overflow-y-auto">
                                                        <table class="w-full text-xs">
                                                            <thead class="bg-slate-100/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold sticky top-0 border-b border-slate-200 dark:border-slate-800">
                                                                <tr>
                                                                    <th class="text-left px-4 py-2.5">Tanggal</th>
                                                                    <th class="text-left px-4 py-2.5">Masuk</th>
                                                                    <th class="text-left px-4 py-2.5">Pulang</th>
                                                                    <th class="text-left px-4 py-2.5">Estimasi Pulang</th>
                                                                    <th class="text-left px-4 py-2.5">Status</th>
                                                                    <th class="text-left px-4 py-2.5">Pulang Cepat</th>
                                                                    <th class="text-left px-4 py-2.5">Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                                                @foreach($row['attendances'] as $att)
                                                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                                                    <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                                        {{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}
                                                                        <span class="text-[10px] text-slate-400 block">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono font-medium">
                                                                        {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '-' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono font-medium">
                                                                        {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : 'Belum' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono text-slate-500">
                                                                        {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i') : '-' }}
                                                                    </td>
                                                                    <td class="px-4 py-2">
                                                                        @if($att->status === 'Hadir')
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Hadir</span>
                                                                        @elseif($att->status === 'Terlambat')
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Telat</span>
                                                                        @else
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $att->status }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 text-slate-500">
                                                                        {{ $att->is_pulang_cepat ? 'Ya' : 'Tidak' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 text-slate-400 text-[11px] truncate max-w-[150px]">
                                                                        {{ $att->note ?? '-' }}
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-400">
                                                    Belum ada riwayat absensi pada bulan ini
                                                </div>
                                            @endif
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
                                <td colspan="7" class="pt-4 pb-1.5 pl-6 pr-4">
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
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_present'], 'activeClass' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_late'], 'activeClass' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_leave'], 'activeClass' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @include('pic.reports.partials.count-badge', ['value' => $row['summary']['total_sick'], 'activeClass' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'])
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold rounded-lg tabular-nums bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60">
                                            {{ $row['summary']['total_attendance_records'] }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3 pl-3 pr-6 text-right" @click.stop>
                                        <button type="button" @click="expanded = !expanded"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border transition-all"
                                                :class="expanded 
                                                    ? 'bg-fuchsia-600 text-white border-fuchsia-600 shadow-sm shadow-fuchsia-500/20' 
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                            <span x-text="expanded ? 'Tutup' : 'Log Absen'"></span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Nested Detail Accordion Tray --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-slate-50/70 dark:bg-[#070b13]">
                                    <td colspan="7" class="p-4 sm:p-5 border-y border-slate-200/80 dark:border-slate-800">
                                        <div class="space-y-3.5 max-w-5xl mx-auto">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-fuchsia-500"></span>
                                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                                        Log Harian: {{ $row['employee']->name }}
                                                    </h4>
                                                </div>
                                                <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}" 
                                                   class="text-[11px] font-semibold text-fuchsia-600 dark:text-fuchsia-400 hover:underline flex items-center gap-1">
                                                    <span>Buka Halaman Penuh</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            </div>

                                            {{-- Mini Daily Table --}}
                                            @if($row['attendances']->count() > 0)
                                                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-xs">
                                                    <div class="max-h-64 overflow-y-auto">
                                                        <table class="w-full text-xs">
                                                            <thead class="bg-slate-100/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold sticky top-0 border-b border-slate-200 dark:border-slate-800">
                                                                <tr>
                                                                    <th class="text-left px-4 py-2.5">Tanggal</th>
                                                                    <th class="text-left px-4 py-2.5">Masuk</th>
                                                                    <th class="text-left px-4 py-2.5">Pulang</th>
                                                                    <th class="text-left px-4 py-2.5">Estimasi Pulang</th>
                                                                    <th class="text-left px-4 py-2.5">Status</th>
                                                                    <th class="text-left px-4 py-2.5">Pulang Cepat</th>
                                                                    <th class="text-left px-4 py-2.5">Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                                                @foreach($row['attendances'] as $att)
                                                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                                                    <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                                        {{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}
                                                                        <span class="text-[10px] text-slate-400 block">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono font-medium">
                                                                        {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '-' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono font-medium">
                                                                        {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : 'Belum' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 font-mono text-slate-500">
                                                                        {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i') : '-' }}
                                                                    </td>
                                                                    <td class="px-4 py-2">
                                                                        @if($att->status === 'Hadir')
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Hadir</span>
                                                                        @elseif($att->status === 'Terlambat')
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Telat</span>
                                                                        @else
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $att->status }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 text-slate-500">
                                                                        {{ $att->is_pulang_cepat ? 'Ya' : 'Tidak' }}
                                                                    </td>
                                                                    <td class="px-4 py-2 text-slate-400 text-[11px] truncate max-w-[150px]">
                                                                        {{ $att->note ?? '-' }}
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-400">
                                                    Belum ada riwayat absensi pada bulan ini
                                                </div>
                                            @endif
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
@endsection
