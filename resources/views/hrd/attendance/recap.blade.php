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
                <select name="division_id"
                    class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none cursor-pointer">
                    <option value="" class="dark:bg-slate-900">Semua Divisi</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}" {{ $divisionId == $div->id ? 'selected' : '' }} class="dark:bg-slate-900">{{ $div->name }}</option>
                    @endforeach
                </select>

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

        <!-- Print Button -->
        <div class="flex justify-end print-hide">
            <button onclick="openPrintPreview()" class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-3 rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan (PDF)
            </button>
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

    {{-- ============================== PRINT PREVIEW MODAL ============================== --}}
    <div id="printPreviewModal" class="fixed inset-0 z-[999999] hidden" style="z-index: 999999 !important;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70" onclick="closePrintPreview()"></div>

        {{-- Modal Content --}}
        <div class="relative z-10 flex flex-col h-full">

            {{-- Toolbar --}}
            <div class="print-hide flex items-center justify-between px-6 py-3" style="background: #1e293b; border-bottom: 1px solid #334155;">
                <div class="flex items-center gap-4">
                    <button onclick="closePrintPreview()" style="color: #fff; padding: 8px; border-radius: 8px; background: #334155;" onmouseover="this.style.background='#475569'" onmouseout="this.style.background='#334155'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <span style="color: #fff; font-weight: 700; font-size: 16px;">Preview Cetak — Laporan Uang Makan</span>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="addRow()" style="display: flex; align-items: center; gap: 6px; background: #10b981; color: #fff; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        + Tambah Baris
                    </button>
                    <div style="width: 1px; height: 32px; background: #475569;"></div>
                    <button onclick="doPrint()" style="display: flex; align-items: center; gap: 6px; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; box-shadow: 0 4px 12px rgba(37,99,235,0.3);" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        🖨️ Print Sekarang
                    </button>
                </div>
            </div>

            {{-- A4 Paper Preview --}}
            <div class="flex-1 overflow-y-auto" style="background: #64748b; padding: 32px;">
                <div id="printArea" style="background: #fff; max-width: 210mm; min-height: 297mm; margin: 0 auto; padding: 15mm 20mm; box-shadow: 0 25px 50px rgba(0,0,0,0.3); color: #000 !important;">

                    {{-- Kop Surat --}}
                    <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #000;">
                        <h1 style="font-size: 16px; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Laporan Uang Makan Karyawan</h1>
                        <p style="font-size: 12px; color: #333; margin: 4px 0 2px 0;">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
                        <p style="font-size: 12px; color: #333; margin: 0;">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>

                    {{-- Tabel --}}
                    <table id="printTable" style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                        <thead>
                            <tr style="background: #e5e7eb;">
                                <th style="border: 1px solid #000; padding: 4px 6px; text-align: center; font-weight: 700; width: 45px; color: #000;">No</th>
                                <th style="border: 1px solid #000; padding: 4px 6px; text-align: left; font-weight: 700; color: #000;">Nama</th>
                                <th style="border: 1px solid #000; padding: 4px 6px; text-align: right; font-weight: 700; min-width: 120px; color: #000;">Jumlah Uang Makan</th>
                                <th class="ttd-col" style="border: 1px solid #000; padding: 4px 6px; text-align: center; font-weight: 700; min-width: 100px; color: #000;">TTD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                                <tr class="data-row" style="position: relative;">
                                    <td class="row-num" style="border: 1px solid #000; padding: 4px 6px; text-align: center; color: #000; position: relative;">
                                        <span class="num-text">{{ $index + 1 }}</span>
                                        <div class="print-hide action-btns" style="position: absolute; left: -45px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 4px; z-index: 50;">
                                            <button onclick="moveRowUp(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▲</button>
                                            <button onclick="moveRowDown(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▼</button>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #000; padding: 4px 6px; color: #000;" contenteditable="true">{{ $user->name }}</td>
                                    <td class="uang-makan-cell" style="border: 1px solid #000; padding: 4px 6px; text-align: right; color: #000;" contenteditable="true" oninput="formatRupiah(this); calculateTotal()">Rp {{ number_format($user->total_meal_allowance, 0, ',', '.') }}</td>
                                    <td class="ttd-col" style="border: 1px solid #000; padding: 4px 6px; text-align: center; height: 35px; color: #000; position: relative;" contenteditable="true">
                                        <div class="print-hide action-btns" style="position: absolute; right: -45px; top: 50%; transform: translateY(-50%); z-index: 50;" contenteditable="false">
                                            <button onclick="deleteRow(this)" style="font-size: 16px; font-weight: bold; background: #fee2e2; color: #dc2626; border-radius: 6px; padding: 8px 12px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">✕</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="border: 1px solid #000; padding: 12px; text-align: center; color: #999; font-style: italic;">Tidak ada data karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: #e5e7eb; font-weight: 700;">
                                <td style="border: 1px solid #000; padding: 4px 6px; text-align: center; color: #000;" colspan="2">TOTAL</td>
                                <td id="totalUangMakan" style="border: 1px solid #000; padding: 4px 6px; text-align: right; color: #000;">Rp {{ number_format($users->sum('total_meal_allowance'), 0, ',', '.') }}</td>
                                <td class="ttd-col" style="border: 1px solid #000; padding: 4px 6px; color: #000;"></td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- Tanda Tangan --}}
                    <div style="margin-top: 40px; display: flex; justify-content: space-between; padding: 0 30px;">
                        <div style="text-align: center;">
                            <p style="font-size: 12px; font-weight: 700; color: #000; margin-bottom: 60px;">Dibuat oleh,</p>
                            <p style="border-top: 1px solid #000; padding-top: 4px; font-size: 12px; font-weight: 700; color: #000; padding-left: 16px; padding-right: 16px;" contenteditable="true">( HRD )</p>
                        </div>
                        <div style="text-align: center;">
                            <p style="font-size: 12px; font-weight: 700; color: #000; margin-bottom: 60px;">Disetujui oleh,</p>
                            <p style="border-top: 1px solid #000; padding-top: 4px; font-size: 12px; font-weight: 700; color: #000; padding-left: 16px; padding-right: 16px;" contenteditable="true">( Direktur )</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============================== PRINT CSS ============================== --}}
    <style>
        /* Hover/Focus styling for editable cells */
        #printArea [contenteditable="true"]:hover {
            background-color: #eff6ff !important;
            cursor: text;
        }
        #printArea [contenteditable="true"]:focus {
            background-color: #dbeafe !important;
            outline: 2px solid #3b82f6 !important;
            outline-offset: -2px;
        }

        /* Tampilkan action button pas hover baris */
        tr.data-row:hover .action-btns {
            opacity: 1 !important;
        }
        .action-btns {
            opacity: 0;
            transition: opacity 0.2s;
        }

        /* Jangan biarkan tombol bisa diedit di cell contenteditable */
        [contenteditable="false"] {
            user-select: none;
        }

        @media print {
            /* Hide everything first */
            body * { visibility: hidden !important; }

            /* Show only printArea */
            #printArea, #printArea * { visibility: visible !important; }

            #printArea {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 15mm 20mm !important;
                box-shadow: none !important;
                background: #fff !important;
                color: #000 !important;
            }

            /* Hide all non-print elements */
            .print-hide, nav, aside, header, footer,
            #printPreviewModal > div:first-child,
            #printPreviewModal .print-hide {
                display: none !important;
                visibility: hidden !important;
            }

            #printPreviewModal {
                position: absolute !important;
                inset: 0 !important;
                z-index: 999999 !important;
                display: block !important;
                background: #fff !important;
            }

            #printPreviewModal > .relative {
                position: static !important;
                height: auto !important;
            }

            #printPreviewModal .flex-1.overflow-y-auto,
            #printPreviewModal .overflow-y-auto {
                overflow: visible !important;
                padding: 0 !important;
                background: #fff !important;
            }

            @page { size: A4; margin: 10mm; }

            #printTable th, #printTable td {
                border: 0.5pt solid #000 !important;
                padding: 3pt 5pt !important;
                color: #000 !important;
            }

            #printTable th, #printTable tfoot td {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            [contenteditable] { outline: none !important; }
        }
    </style>

    {{-- ============================== JAVASCRIPT ============================== --}}
    <script>
        function openPrintPreview() {
            document.getElementById('printPreviewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            calculateTotal(); // pastikan hitung ulang saat buka
        }

        function closePrintPreview() {
            document.getElementById('printPreviewModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Fitur Tambah Baris
        function addRow() {
            const tbody = document.querySelector('#printTable tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'data-row';

            // Bersihkan teks "Tidak ada data karyawan" jika ada
            const emptyState = tbody.querySelector('td[colspan="4"]');
            if (emptyState) emptyState.parentElement.remove();

            newRow.innerHTML = `
                <td class="row-num" style="border: 1px solid #000; padding: 4px 6px; text-align: center; color: #000; position: relative;">
                    <span class="num-text"></span>
                    <div class="print-hide action-btns" style="position: absolute; left: -45px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 4px; z-index: 50;">
                        <button onclick="moveRowUp(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▲</button>
                        <button onclick="moveRowDown(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▼</button>
                    </div>
                </td>
                <td style="border: 1px solid #000; padding: 4px 6px; color: #000;" contenteditable="true"></td>
                <td class="uang-makan-cell" style="border: 1px solid #000; padding: 4px 6px; text-align: right; color: #000;" contenteditable="true" oninput="formatRupiah(this); calculateTotal()">Rp 0</td>
                <td class="ttd-col" style="border: 1px solid #000; padding: 4px 6px; text-align: center; height: 35px; color: #000; position: relative;" contenteditable="true">
                    <div class="print-hide action-btns" style="position: absolute; right: -45px; top: 50%; transform: translateY(-50%); z-index: 50;" contenteditable="false">
                        <button onclick="deleteRow(this)" style="font-size: 16px; font-weight: bold; background: #fee2e2; color: #dc2626; border-radius: 6px; padding: 8px 12px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">✕</button>
                    </div>
                </td>
            `;

            tbody.appendChild(newRow);
            updateRowNumbers();
            calculateTotal();

            // Fokus ke nama karyawan baru
            newRow.querySelector('td:nth-child(2)').focus();
        }

        // Fitur Hapus Baris
        function deleteRow(btn) {
            const row = btn.closest('tr');
            if (confirm("Hapus baris ini?")) {
                row.remove();
                updateRowNumbers();
                calculateTotal();
            }
        }

        // Fitur Pindah Posisi (Atas/Bawah)
        function moveRowUp(btn) {
            const row = btn.closest('tr');
            const prevRow = row.previousElementSibling;
            if (prevRow) {
                row.parentNode.insertBefore(row, prevRow);
                updateRowNumbers();
            }
        }

        function moveRowDown(btn) {
            const row = btn.closest('tr');
            const nextRow = row.nextElementSibling;
            if (nextRow) {
                row.parentNode.insertBefore(nextRow, row);
                updateRowNumbers();
            }
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('#printTable tbody tr.data-row');
            rows.forEach((row, index) => {
                const numText = row.querySelector('.num-text');
                if(numText) numText.textContent = index + 1;
            });
        }

        // Fitur Format Rupiah Otomatis saat mengetik
        function formatRupiah(element) {
            let text = element.innerText || element.textContent;
            let numStr = text.replace(/[^0-9]/g, '');
            
            if (numStr === '') {
                element.innerText = 'Rp 0';
            } else {
                let num = parseInt(numStr, 10);
                element.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
            }

            // Kembalikan kursor ke posisi paling belakang teks
            let range = document.createRange();
            let sel = window.getSelection();
            range.selectNodeContents(element);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        }

        // Fitur Hitung Total Otomatis
        function calculateTotal() {
            const cells = document.querySelectorAll('.uang-makan-cell');
            let total = 0;
            
            cells.forEach(cell => {
                let text = cell.innerText || cell.textContent;
                let numStr = text.replace(/[^0-9]/g, ''); 
                if (numStr !== '') {
                    total += parseInt(numStr, 10);
                }
            });

            // Format total kembali ke Rp
            const totalStr = new Intl.NumberFormat('id-ID').format(total);
            const totalCell = document.getElementById('totalUangMakan');
            if(totalCell) {
                totalCell.innerText = 'Rp ' + totalStr;
            }
        }

        function doPrint() {
            window.print();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePrintPreview();
        });
    </script>
@endsection