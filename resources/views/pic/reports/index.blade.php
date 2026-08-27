@extends('layouts.master')

@section('title', 'Laporan Absensi')

@section('content')
@php
    $reportRouteName = $reportRouteName ?? 'pic.reports.index';
@endphp

<div class="max-w-7xl mx-auto" x-data="reportPage()">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-blue-900 dark:text-white flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span>Laporan Absensi</span>
        </h1>
        <p class="text-sm text-blue-600/70 dark:text-blue-400/70 mt-1 ml-[52px]">Lihat rekap absensi detail per karyawan dalam satu bulan</p>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route($reportRouteName) }}" class="mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm p-5">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- Employee Select --}}
                <div class="md:col-span-6">
                    <label for="employee_id" class="block text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Pilih Karyawan</label>
                    <select name="employee_id" id="employee_id"
                            class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
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
                <div class="md:col-span-4">
                    <label for="month" class="block text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Bulan</label>
                    <input type="month" name="month" id="month" value="{{ $month }}"
                           class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                </div>

                {{-- Submit --}}
                <div class="md:col-span-2">
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($report)
        {{-- Employee Info & Summary Cards --}}
        <div class="mb-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                            {{ strtoupper(substr($report['employee']->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-blue-900 dark:text-white">{{ $report['employee']->name }}</h2>
                            <p class="text-xs text-blue-500 dark:text-blue-400">Rekap Bulan: {{ \Carbon\Carbon::parse($report['month'] . '-01')->locale('id')->translatedFormat('F Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    {{-- Hadir --}}
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl p-4 text-center">
                        <div class="w-9 h-9 mx-auto mb-2 bg-emerald-100 dark:bg-emerald-800/40 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $report['summary']['total_present'] }}</p>
                        <p class="text-[10px] font-semibold text-emerald-600/70 dark:text-emerald-400/70 uppercase tracking-wider mt-0.5">Hadir</p>
                    </div>

                    {{-- Telat --}}
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-xl p-4 text-center">
                        <div class="w-9 h-9 mx-auto mb-2 bg-amber-100 dark:bg-amber-800/40 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300">{{ $report['summary']['total_late'] }}</p>
                        <p class="text-[10px] font-semibold text-amber-600/70 dark:text-amber-400/70 uppercase tracking-wider mt-0.5">Telat</p>
                    </div>

                    {{-- Izin --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/40 rounded-xl p-4 text-center">
                        <div class="w-9 h-9 mx-auto mb-2 bg-blue-100 dark:bg-blue-800/40 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-blue-700 dark:text-blue-300">{{ $report['summary']['total_leave'] }}</p>
                        <p class="text-[10px] font-semibold text-blue-600/70 dark:text-blue-400/70 uppercase tracking-wider mt-0.5">Izin</p>
                    </div>

                    {{-- Sakit --}}
                    <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/40 rounded-xl p-4 text-center">
                        <div class="w-9 h-9 mx-auto mb-2 bg-rose-100 dark:bg-rose-800/40 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                        <p class="text-2xl font-extrabold text-rose-700 dark:text-rose-300">{{ $report['summary']['total_sick'] }}</p>
                        <p class="text-[10px] font-semibold text-rose-600/70 dark:text-rose-400/70 uppercase tracking-wider mt-0.5">Sakit</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Detail Harian</span>
                </h3>
                <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">{{ $report['attendances']->count() }} data absensi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-50/70 dark:bg-slate-800/50">
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tanggal</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Jam Masuk</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Jam Pulang</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Estimasi Pulang</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Pulang Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100/50 dark:divide-slate-800/50">
                        @forelse($report['attendances'] as $att)
                            <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-blue-900 dark:text-white">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}</span>
                                    <span class="block text-[10px] text-blue-400 dark:text-blue-500">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($att->check_in)
                                        <span class="inline-flex items-center space-x-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                            <span>{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($att->check_out)
                                        <span class="inline-flex items-center space-x-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            <span>{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Belum Pulang</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($att->check_in)
                                        @php
                                            $estimasi = \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i');
                                        @endphp
                                        <span class="inline-flex items-center space-x-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-lg text-xs font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>{{ $estimasi }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($att->status === 'Hadir')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Hadir
                                        </span>
                                        @if($att->note === 'Absen Diluar')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 ml-1">
                                                📍 Diluar
                                            </span>
                                        @endif
                                    @elseif($att->status === 'Terlambat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>Telat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>{{ ucfirst($att->status ?? '-') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($att->is_pulang_cepat)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            Ya
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-blue-200 dark:text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <p class="text-sm font-semibold text-blue-400 dark:text-slate-500">Tidak ada data absensi</p>
                                        <p class="text-xs text-blue-300 dark:text-slate-600 mt-0.5">Karyawan ini belum memiliki riwayat absensi di bulan ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Leave Requests Table --}}
        @if($report['leaves']->count() > 0)
        <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Riwayat Izin / Cuti</span>
                </h3>
                <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">{{ $report['leaves']->count() }} pengajuan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-50/70 dark:bg-slate-800/50">
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tipe</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tanggal Mulai</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tanggal Selesai</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Alasan</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100/50 dark:divide-slate-800/50">
                        @foreach($report['leaves'] as $leave)
                            <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    @if($leave->type === 'Sakit')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                            Sakit
                                        </span>
                                    @elseif($leave->type === 'Izin Tidak Masuk' || $leave->type === 'Izin Tdk Masuk')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            Izin Tdk Masuk
                                        </span>
                                    @elseif($leave->type === 'Izin Masuk Siang' || $leave->type === 'Izin Siang')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Izin Siang
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ $leave->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-blue-900 dark:text-white font-medium">{{ $leave->start_date->locale('id')->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3 text-blue-900 dark:text-white font-medium">{{ $leave->end_date->locale('id')->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3 text-blue-700 dark:text-blue-300 max-w-[200px] truncate">{{ $leave->reason }}</td>
                                <td class="px-5 py-3">
                                    @if($leave->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Disetujui
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5 animate-pulse"></span>Menunggu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    @elseif($allReports->count() > 0)
        {{-- All employees monthly recap --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"></path></svg>
                    <span>Rekap Semua Karyawan</span>
                </h3>
                    <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">
                    {{ $allReports->count() }} karyawan - {{ \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y') }}
                </span>
                     <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-50/70 dark:bg-slate-800/50">
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nama Karyawan</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Hadir</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Telat</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Izin/Cuti</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Sakit</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Total Absensi</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    @php
                        $staffReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan');
                        $ramayanaReports = $allReports->filter(fn($row) => $row['employee']->role->slug === 'karyawan_ramayana');
                    @endphp

                    @if($staffReports->count() > 0)
                        <tbody class="border-t-2 border-blue-100 dark:border-slate-800">
                            <tr class="bg-blue-50/90 dark:bg-slate-800/60 font-bold">
                                <td colspan="7" class="px-5 py-2.5 text-xs text-blue-800 dark:text-blue-300 uppercase tracking-widest">
                                    💼 Staff Kantor
                                </td>
                            </tr>
                        </tbody>
                        @foreach($staffReports as $row)
                            <tbody x-data="{ expanded: false }" class="divide-y divide-blue-100/50 dark:divide-slate-800/50">
                                <tr class="hover:bg-blue-50/60 dark:hover:bg-slate-800/50 transition-colors cursor-pointer" @click="expanded = !expanded">
                                    <td class="px-5 py-3.5 pl-6">
                                        <div class="flex items-center space-x-3">
                                            <button type="button" @click.stop="expanded = !expanded" 
                                                class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-slate-700 flex items-center justify-center transition-all flex-shrink-0"
                                                title="Buka / Tutup Detail">
                                                <svg class="w-4 h-4 transition-transform duration-300" :class="expanded ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div>
                                                <p class="font-bold text-blue-950 dark:text-white leading-tight">{{ $row['employee']->name }}</p>
                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">{{ $row['employee']->location->name ?? ($row['employee']->division->name ?? '-') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-emerald-600 dark:text-emerald-400 font-bold">{{ $row['summary']['total_present'] }}</td>
                                    <td class="px-5 py-3.5 text-amber-600 dark:text-amber-400 font-bold">{{ $row['summary']['total_late'] }}</td>
                                    <td class="px-5 py-3.5 text-blue-600 dark:text-blue-400 font-bold">{{ $row['summary']['total_leave'] }}</td>
                                    <td class="px-5 py-3.5 text-rose-600 dark:text-rose-400 font-bold">{{ $row['summary']['total_sick'] }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 font-bold">{{ $row['summary']['total_attendance_records'] }}</td>
                                    <td class="px-5 py-3.5" @click.stop>
                                        <div class="flex items-center space-x-2">
                                            <button type="button" @click="expanded = !expanded"
                                               class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-blue-700 dark:text-blue-300 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
                                                <span x-text="expanded ? 'Tutup' : 'Lihat Log'"></span>
                                                <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}"
                                               class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black px-2.5 py-1.5 rounded-lg uppercase tracking-wider transition-all"
                                               title="Halaman Detail Lengkap">
                                                <span>Detail</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Drawer Row --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-blue-50/40 dark:bg-slate-900/90">
                                    <td colspan="7" class="px-6 py-5 border-y border-blue-200/60 dark:border-slate-800">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                    <h4 class="text-xs font-black text-blue-900 dark:text-white uppercase tracking-wider">
                                                        Detail Absensi Harian: {{ $row['employee']->name }}
                                                    </h4>
                                                    <span class="text-[11px] text-blue-500/80 font-medium">({{ \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y') }})</span>
                                                </div>
                                            </div>

                                            @if($row['attendances']->count() > 0)
                                            <div class="bg-white dark:bg-slate-950 rounded-xl border border-blue-100 dark:border-slate-800 overflow-hidden shadow-sm">
                                                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                                                    <table class="w-full text-xs">
                                                        <thead class="bg-blue-50/80 dark:bg-slate-900 text-blue-800 dark:text-blue-300 font-bold sticky top-0 border-b border-blue-100 dark:border-slate-800">
                                                            <tr>
                                                                <th class="text-left px-4 py-2.5">Tanggal</th>
                                                                <th class="text-left px-4 py-2.5">Jam Masuk</th>
                                                                <th class="text-left px-4 py-2.5">Jam Pulang</th>
                                                                <th class="text-left px-4 py-2.5">Estimasi Pulang</th>
                                                                <th class="text-left px-4 py-2.5">Status</th>
                                                                <th class="text-left px-4 py-2.5">Pulang Cepat</th>
                                                                <th class="text-left px-4 py-2.5">Catatan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-blue-50 dark:divide-slate-900/60">
                                                            @foreach($row['attendances'] as $att)
                                                            <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-900/40 transition-colors">
                                                                <td class="px-4 py-2 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                                    {{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}
                                                                    <span class="text-[10px] text-slate-400 block font-normal">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->check_in)
                                                                        <span class="inline-flex items-center space-x-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                                                            <span>{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                                                                        </span>
                                                                    @else
                                                                        <span class="text-slate-400">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->check_out)
                                                                        <span class="inline-flex items-center space-x-1 text-indigo-600 dark:text-indigo-400 font-bold">
                                                                            <span>{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                                                                        </span>
                                                                    @else
                                                                        <span class="text-[11px] text-slate-400 italic">Belum Pulang</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-slate-500">
                                                                    @if($att->check_in)
                                                                        {{ \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i') }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->status === 'Hadir')
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300">Hadir</span>
                                                                    @elseif($att->status === 'Terlambat')
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300">Telat</span>
                                                                    @else
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $att->status }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->is_pulang_cepat)
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300">Ya</span>
                                                                    @else
                                                                        <span class="text-slate-400 text-[11px]">Tidak</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-slate-500">
                                                                    {{ $att->note ?? '-' }}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            @else
                                            <div class="bg-white dark:bg-slate-950 rounded-xl p-4 text-center border border-dashed border-slate-200 dark:border-slate-800">
                                                <p class="text-xs text-slate-400 font-medium">Belum ada riwayat absensi pada bulan ini</p>
                                            </div>
                                            @endif

                                            @if($row['leaves']->count() > 0)
                                            <div class="mt-3">
                                                <p class="text-[11px] font-bold text-blue-900 dark:text-white uppercase tracking-wider mb-2">Riwayat Izin / Cuti ({{ $row['leaves']->count() }})</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($row['leaves'] as $leave)
                                                    <div class="bg-white dark:bg-slate-950 rounded-xl p-3 border border-blue-100 dark:border-slate-800 shadow-sm flex items-start justify-between gap-2">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $leave->type }}</p>
                                                            <p class="text-[10px] text-slate-400 mt-0.5">
                                                                {{ $leave->start_date->locale('id')->translatedFormat('d M') }} s/d {{ $leave->end_date->locale('id')->translatedFormat('d M Y') }}
                                                            </p>
                                                            @if($leave->reason)
                                                            <p class="text-[10px] text-slate-500 italic mt-1 line-clamp-1">"{{ $leave->reason }}"</p>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            @if($leave->status === 'approved')
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700">Disetujui</span>
                                                            @elseif($leave->status === 'rejected')
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Ditolak</span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    @endif

                    @if($ramayanaReports->count() > 0)
                        <tbody class="border-t-2 border-fuchsia-100 dark:border-slate-800">
                            <tr class="bg-fuchsia-50/90 dark:bg-fuchsia-950/20 font-bold">
                                <td colspan="7" class="px-5 py-2.5 text-xs text-fuchsia-800 dark:text-fuchsia-300 uppercase tracking-widest">
                                    🛍️ Karyawan Ramayana
                                </td>
                            </tr>
                        </tbody>
                        @foreach($ramayanaReports as $row)
                            <tbody x-data="{ expanded: false }" class="divide-y divide-blue-100/50 dark:divide-slate-800/50">
                                <tr class="hover:bg-blue-50/60 dark:hover:bg-slate-800/50 transition-colors cursor-pointer" @click="expanded = !expanded">
                                    <td class="px-5 py-3.5 pl-6">
                                        <div class="flex items-center space-x-3">
                                            <button type="button" @click.stop="expanded = !expanded" 
                                                class="w-7 h-7 rounded-lg bg-fuchsia-50 dark:bg-slate-800 text-fuchsia-600 dark:text-fuchsia-400 hover:bg-fuchsia-100 dark:hover:bg-slate-700 flex items-center justify-center transition-all flex-shrink-0"
                                                title="Buka / Tutup Detail">
                                                <svg class="w-4 h-4 transition-transform duration-300" :class="expanded ? 'rotate-180 text-fuchsia-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div>
                                                <p class="font-bold text-blue-950 dark:text-white leading-tight">{{ $row['employee']->name }}</p>
                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">{{ $row['employee']->location->name ?? ($row['employee']->division->name ?? '-') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-emerald-600 dark:text-emerald-400 font-bold">{{ $row['summary']['total_present'] }}</td>
                                    <td class="px-5 py-3.5 text-amber-600 dark:text-amber-400 font-bold">{{ $row['summary']['total_late'] }}</td>
                                    <td class="px-5 py-3.5 text-blue-600 dark:text-blue-400 font-bold">{{ $row['summary']['total_leave'] }}</td>
                                    <td class="px-5 py-3.5 text-rose-600 dark:text-rose-400 font-bold">{{ $row['summary']['total_sick'] }}</td>
                                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 font-bold">{{ $row['summary']['total_attendance_records'] }}</td>
                                    <td class="px-5 py-3.5" @click.stop>
                                        <div class="flex items-center space-x-2">
                                            <button type="button" @click="expanded = !expanded"
                                               class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-blue-700 dark:text-blue-300 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
                                                <span x-text="expanded ? 'Tutup' : 'Lihat Log'"></span>
                                                <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            <a href="{{ route($reportRouteName, ['employee_id' => $row['employee']->id, 'month' => $month]) }}"
                                               class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black px-2.5 py-1.5 rounded-lg uppercase tracking-wider transition-all"
                                               title="Halaman Detail Lengkap">
                                                <span>Detail</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Drawer Row --}}
                                <tr x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="bg-blue-50/40 dark:bg-slate-900/90">
                                    <td colspan="7" class="px-6 py-5 border-y border-blue-200/60 dark:border-slate-800">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-2 h-2 rounded-full bg-fuchsia-500"></span>
                                                    <h4 class="text-xs font-black text-blue-900 dark:text-white uppercase tracking-wider">
                                                        Detail Absensi Harian: {{ $row['employee']->name }}
                                                    </h4>
                                                    <span class="text-[11px] text-blue-500/80 font-medium">({{ \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y') }})</span>
                                                </div>
                                            </div>

                                            @if($row['attendances']->count() > 0)
                                            <div class="bg-white dark:bg-slate-950 rounded-xl border border-blue-100 dark:border-slate-800 overflow-hidden shadow-sm">
                                                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                                                    <table class="w-full text-xs">
                                                        <thead class="bg-fuchsia-50/80 dark:bg-slate-900 text-fuchsia-800 dark:text-fuchsia-300 font-bold sticky top-0 border-b border-blue-100 dark:border-slate-800">
                                                            <tr>
                                                                <th class="text-left px-4 py-2.5">Tanggal</th>
                                                                <th class="text-left px-4 py-2.5">Jam Masuk</th>
                                                                <th class="text-left px-4 py-2.5">Jam Pulang</th>
                                                                <th class="text-left px-4 py-2.5">Estimasi Pulang</th>
                                                                <th class="text-left px-4 py-2.5">Status</th>
                                                                <th class="text-left px-4 py-2.5">Pulang Cepat</th>
                                                                <th class="text-left px-4 py-2.5">Catatan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-blue-50 dark:divide-slate-900/60">
                                                            @foreach($row['attendances'] as $att)
                                                            <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-900/40 transition-colors">
                                                                <td class="px-4 py-2 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                                    {{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('d M Y') }}
                                                                    <span class="text-[10px] text-slate-400 block font-normal">{{ \Carbon\Carbon::parse($att->date)->locale('id')->translatedFormat('l') }}</span>
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->check_in)
                                                                        <span class="inline-flex items-center space-x-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                                                            <span>{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                                                                        </span>
                                                                    @else
                                                                        <span class="text-slate-400">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->check_out)
                                                                        <span class="inline-flex items-center space-x-1 text-indigo-600 dark:text-indigo-400 font-bold">
                                                                            <span>{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                                                                        </span>
                                                                    @else
                                                                        <span class="text-[11px] text-slate-400 italic">Belum Pulang</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-slate-500">
                                                                    @if($att->check_in)
                                                                        {{ \Carbon\Carbon::parse($att->check_in)->addHours(8)->format('H:i') }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->status === 'Hadir')
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300">Hadir</span>
                                                                    @elseif($att->status === 'Terlambat')
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300">Telat</span>
                                                                    @else
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $att->status }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($att->is_pulang_cepat)
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300">Ya</span>
                                                                    @else
                                                                        <span class="text-slate-400 text-[11px]">Tidak</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-slate-500">
                                                                    {{ $att->note ?? '-' }}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            @else
                                            <div class="bg-white dark:bg-slate-950 rounded-xl p-4 text-center border border-dashed border-slate-200 dark:border-slate-800">
                                                <p class="text-xs text-slate-400 font-medium">Belum ada riwayat absensi pada bulan ini</p>
                                            </div>
                                            @endif

                                            @if($row['leaves']->count() > 0)
                                            <div class="mt-3">
                                                <p class="text-[11px] font-bold text-blue-900 dark:text-white uppercase tracking-wider mb-2">Riwayat Izin / Cuti ({{ $row['leaves']->count() }})</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($row['leaves'] as $leave)
                                                    <div class="bg-white dark:bg-slate-950 rounded-xl p-3 border border-blue-100 dark:border-slate-800 shadow-sm flex items-start justify-between gap-2">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $leave->type }}</p>
                                                            <p class="text-[10px] text-slate-400 mt-0.5">
                                                                {{ $leave->start_date->locale('id')->translatedFormat('d M') }} s/d {{ $leave->end_date->locale('id')->translatedFormat('d M Y') }}
                                                            </p>
                                                            @if($leave->reason)
                                                            <p class="text-[10px] text-slate-500 italic mt-1 line-clamp-1">"{{ $leave->reason }}"</p>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            @if($leave->status === 'approved')
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700">Disetujui</span>
                                                            @elseif($leave->status === 'rejected')
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Ditolak</span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
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
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500 italic">
                                    Tidak ada data karyawan ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>
            </div>
        </div>
    @elseif($employeeId)
        {{-- No data state --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm p-10 text-center">
            <svg class="w-16 h-16 mx-auto text-blue-200 dark:text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-lg font-bold text-blue-400 dark:text-slate-500">Tidak Ada Data</p>
            <p class="text-sm text-blue-300 dark:text-slate-600 mt-1">Karyawan ini belum memiliki data absensi di bulan yang dipilih</p>
        </div>
    @else
        {{-- Initial state --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-sm p-10 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-400 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <p class="text-lg font-bold text-blue-800 dark:text-blue-200">Pilih Karyawan & Bulan</p>
            <p class="text-sm text-blue-500 dark:text-blue-400 mt-1 max-w-md mx-auto">Gunakan filter di atas untuk memilih karyawan dan periode bulan, lalu klik "Tampilkan" untuk melihat rekap absensi lengkap.</p>
        </div>
    @endif
</div>

<script>
function reportPage() {
    return {};
}
</script>
@endsection
