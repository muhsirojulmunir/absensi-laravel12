@extends('layouts.master')
@section('title', 'Rekap Absensi')
@section('content')
    <div class="space-y-8" x-data="{
        autoSubmit() {
            this.$refs.filterForm.submit();
        }
    }">
        <!-- Header Section -->
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Rekap Absensi</h1>
                <p class="text-sm text-blue-500 dark:text-blue-400 mt-2">Laporan performa untuk periode
                    {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M') }} -
                    {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
            </div>

            <!-- Improved Filter Section -->
            <div class="bg-white dark:bg-slate-800/50 rounded-2xl border border-blue-100 dark:border-slate-700 p-4 shadow-sm">
                <form action="{{ route($recapRouteName ?? 'hrd.attendance.recap') }}" method="GET" x-ref="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Divisi Filter -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Divisi</label>
                        <select name="division_id" @change="autoSubmit()"
                            class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer transition">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ $divisionId == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Custom Date Range -->
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Rentang Tanggal</label>
                        <input type="text" name="custom_date_range" id="custom_date_range" placeholder="Pilih rentang tanggal"
                            class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white outline-none cursor-pointer focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 dark:from-blue-900 dark:to-blue-800 rounded-xl p-5 shadow-lg text-white relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-blue-100 uppercase tracking-wider mb-2">Total Dana Diperlukan</p>
                    <h3 class="text-2xl md:text-3xl font-bold tracking-tight">
                        Rp {{ number_format($users->sum('total_meal_allowance'), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-blue-200 mt-3 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-2">Total Karyawan</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-blue-950 dark:text-white">{{ $users->count() }} <span class="text-base text-blue-500 dark:text-blue-400">Orang</span></h3>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 dark:bg-slate-700 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 print-hide">
            @php
                $hasUnpaid = $users->contains(fn($u) => $u->calculated_meal_allowance > 0 && !$u->is_meal_paid);
            @endphp
            @if($hasUnpaid)
            <form action="{{ route('hrd.attendance.pay-meal-allowance') }}" method="POST" onsubmit="return confirm('Tandai LUNAS uang makan untuk SEMUA karyawan di periode ini?')">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                @foreach($users as $u)
                    @if($u->calculated_meal_allowance > 0 && !$u->is_meal_paid)
                        <input type="hidden" name="bulk_pay[{{ $u->id }}]" value="{{ $u->calculated_meal_allowance }}">
                    @endif
                @endforeach
                <button type="submit" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-md hover:shadow-lg active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Tandai Semua Lunas
                </button>
            </form>
            @endif
            <a href="{{ route('hrd.attendance.payment-history') }}" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-md hover:shadow-lg active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Riwayat Pembayaran
            </a>
            <button onclick="openPrintPreview()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-md hover:shadow-lg active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan (PDF)
            </button>
        </div>

        <!-- Recap Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-blue-50 dark:bg-slate-900/50 border-b border-blue-100 dark:border-slate-700">
                            <th class="px-6 py-4 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Karyawan</th>
                            <th class="px-4 py-4 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider text-center">Hadir</th>
                            <th class="px-4 py-4 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider text-center">Terlambat</th>
                            <th class="px-4 py-4 text-xs font-bold text-orange-500 dark:text-orange-400 uppercase tracking-wider text-center">Pulang Cepat</th>
                            <th class="px-4 py-4 text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Izin/Sakit</th>
                            <th class="px-4 py-4 text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Libur</th>
                            <th class="px-6 py-4 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider text-center bg-blue-50/50 dark:bg-slate-900/30">Uang Makan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-blue-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-sm text-blue-950 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-blue-500 dark:text-blue-400 font-medium">{{ $user->division->name ?? 'Tidak ada Divisi' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold text-sm">{{ $user->hadir_count }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold text-sm">{{ $user->terlambat_count }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 font-bold text-sm">{{ $user->pulang_cepat_count }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center" title="{{ $user->izin_details }}">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-sm cursor-help">{{ $user->izin_count }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center" title="{{ $user->libur_details }}">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 font-bold text-sm cursor-help">{{ $user->libur_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center bg-blue-50/30 dark:bg-slate-900/20">
                                    <div class="text-sm font-bold text-blue-600 dark:text-blue-400 mb-2">Rp {{ number_format($user->total_meal_allowance, 0, ',', '.') }}</div>
                                    @if($user->calculated_meal_allowance > 0)
                                        @if($user->is_meal_paid)
                                            <form action="{{ route('hrd.attendance.toggle-payment') }}" method="POST" class="inline" onsubmit="return confirm('Batalkan status lunas untuk {{ $user->name }}?')">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                                <input type="hidden" name="amount" value="{{ $user->calculated_meal_allowance }}">
                                                <button type="submit" class="inline-flex items-center space-x-1 text-xs bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1.5 rounded-md font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800/60 transition-colors">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                                                    <span>Lunas</span>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('hrd.attendance.toggle-payment') }}" method="POST" class="inline" onsubmit="return confirm('Tandai lunas untuk {{ $user->name }}?')">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                                <input type="hidden" name="amount" value="{{ $user->calculated_meal_allowance }}">
                                                <button type="submit" class="inline-flex items-center space-x-1 text-xs bg-slate-100 dark:bg-slate-700 px-2.5 py-1.5 rounded-md font-semibold text-slate-600 dark:text-slate-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span>Belum Lunas</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-blue-500 dark:text-blue-400 font-medium uppercase tracking-wider text-xs opacity-50">
                                    Data rekap tidak ditemukan untuk periode ini
                                </td>
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
                    <button onclick="saveToHistory()" style="display: flex; align-items: center; gap: 6px; background: #eab308; color: #fff; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13px;" onmouseover="this.style.background='#ca8a04'" onmouseout="this.style.background='#eab308'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Simpan ke Riwayat
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
                                <tr class="data-row" style="position: relative;" data-user-id="{{ $user->id }}">
                                    <td class="row-num" style="border: 1px solid #000; padding: 4px 6px; text-align: center; color: #000; position: relative;">
                                        <span class="num-text">{{ $index + 1 }}</span>
                                        <div class="print-hide action-btns" style="position: absolute; left: -45px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 4px; z-index: 50;">
                                            <button onclick="moveRowUp(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▲</button>
                                            <button onclick="moveRowDown(this)" style="font-size: 14px; background: #e2e8f0; border-radius: 6px; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">▼</button>
                                        </div>
                                    </td>
                                    <td class="name-cell" style="border: 1px solid #000; padding: 4px 6px; color: #000;" contenteditable="true">{{ $user->name }}</td>
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
                padding: 5mm 15mm 10mm 15mm !important;
                box-shadow: none !important;
                background: #fff !important;
                color: #000 !important;
            }
            @page {
                size: A4;
                margin: 5mm 10mm 10mm 10mm;
                @top-center { content: none; }
                @bottom-center { content: none; }
            }

            /* Chrome/Edge trick untuk hilangkan header-footer */
            html {
                -webkit-print-color-adjust: exact;
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

           @page {
    size: A4;
    margin: 5mm 10mm 10mm 10mm;
}

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
                <td class="name-cell" style="border: 1px solid #000; padding: 4px 6px; color: #000;" contenteditable="true"></td>
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

        // Fitur Simpan ke Riwayat
        function saveToHistory() {
            const rows = document.querySelectorAll('#printTable tbody tr.data-row');
            const payments = [];

            rows.forEach(row => {
                const nameCell = row.querySelector('.name-cell');
                const amountCell = row.querySelector('.uang-makan-cell');
                const userId = row.dataset.userId || null;
                
                if (nameCell && amountCell) {
                    const name = nameCell.innerText.trim();
                    const amountStr = amountCell.innerText.replace(/[^0-9]/g, '');
                    const amount = amountStr ? parseInt(amountStr, 10) : 0;
                    
                    if (name) {
                        payments.push({
                            name: name,
                            amount: amount,
                            user_id: userId
                        });
                    }
                }
            });

            if (payments.length === 0) {
                alert('Tidak ada data karyawan untuk disimpan.');
                return;
            }

            if (!confirm('Simpan data laporan ini ke riwayat pembayaran?')) {
                return;
            }

            fetch("{{ route('hrd.attendance.save-history') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    start_date: '{{ $startDate }}',
                    end_date: '{{ $endDate }}',
                    payments: payments
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan ke riwayat.');
            });
        }
    </script>

    <!-- Flatpickr for Custom Date Range -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('[x-ref="filterForm"]');

            flatpickr("#custom_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "id",
                maxDate: "{{ \Carbon\Carbon::now()->isFriday() ? \Carbon\Carbon::now()->addDay()->toDateString() : \Carbon\Carbon::today()->toDateString() }}",
                defaultDate: ["{{ $startDate }}", "{{ $endDate }}"],
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        setTimeout(() => form.submit(), 100);
                    }
                }
            });
        });
    </script>
@endsection