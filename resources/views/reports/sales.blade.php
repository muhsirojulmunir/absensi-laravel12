@extends('layouts.master')

@section('title', 'Laporan Penjualan')

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1; /* slate-300 */
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #334155; /* slate-700 */
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="salesReport()">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-blue-950 dark:text-white tracking-tight">Laporan Penjualan</h1>
                <p class="text-sm text-blue-600/70 dark:text-blue-400/70 font-medium">Rekap penjualan SPG / Counter Ramayana</p>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <form method="GET" action="{{ route($routeName) }}">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5">
            <div class="flex items-end gap-4 overflow-x-auto pb-2 custom-scrollbar">
                
                {{-- Periode --}}
                <div class="w-[160px] shrink-0">
                    <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Periode</label>
                    <select name="period" x-model="period"
                        class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                {{-- Bulan (Tampil jika period == 'month') --}}
                <div class="w-[180px] shrink-0" x-show="period === 'month'" x-cloak>
                    <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Bulan</label>
                    <input type="month" name="month" value="{{ request('month', date('Y-m')) }}"
                        class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all dark:[color-scheme:dark]">
                </div>

                {{-- Custom Date (Tampil jika period == 'custom') --}}
                <div class="w-[320px] shrink-0" x-show="period === 'custom'" x-cloak>
                    <div class="flex space-x-2">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Dari</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-3 py-2.5 focus:ring-2 focus:ring-blue-400 transition-all dark:[color-scheme:dark]">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Sampai</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-3 py-2.5 focus:ring-2 focus:ring-blue-400 transition-all dark:[color-scheme:dark]">
                        </div>
                    </div>
                </div>

                {{-- Counter / Lokasi --}}
                <div class="w-[200px] shrink-0">
                    <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Counter / Lokasi</label>
                    <select name="location_id"
                        class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                        <option value="" class="dark:bg-slate-900">-- Semua Counter --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }} class="dark:bg-slate-900">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama SPG --}}
                <div class="w-[200px] shrink-0">
                    <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1.5 uppercase tracking-wider">Nama SPG</label>
                    <select name="user_id"
                        class="w-full rounded-xl border border-blue-200 dark:border-slate-700 bg-blue-50/50 dark:bg-slate-800 text-sm text-blue-900 dark:text-white px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                        <option value="" class="dark:bg-slate-900">-- Semua SPG --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }} class="dark:bg-slate-900">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit --}}
                <div class="shrink-0">
                    <button type="submit"
                        class="w-full inline-flex justify-center items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-lg shadow-blue-600/20 hover:shadow-xl transition-all duration-200 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Total Nominal --}}
        <div class="bg-gradient-to-br from-violet-600 to-fuchsia-600 rounded-[1.5rem] p-6 text-white relative overflow-hidden group shadow-xl shadow-violet-600/20">
            <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-white/20 backdrop-blur-md rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/80">Total Nominal</p>
                </div>
                <h3 class="text-2xl md:text-3xl font-black tracking-tighter">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Total Qty --}}
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[1.5rem] p-6 relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-28 h-28 bg-emerald-50 dark:bg-emerald-900/10 rounded-full blur-2xl group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 dark:text-emerald-400">Total Qty (Psg)</p>
                </div>
                <h3 class="text-2xl md:text-3xl font-black text-emerald-700 dark:text-emerald-300 tracking-tighter">{{ number_format($totalQty, 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Total Transaksi --}}
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[1.5rem] p-6 relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-28 h-28 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-2xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-500 dark:text-blue-400">Total Transaksi</p>
                </div>
                <h3 class="text-2xl md:text-3xl font-black text-blue-700 dark:text-blue-300 tracking-tighter">{{ $sales->count() }}</h3>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span>Detail Penjualan</span>
            </h3>
            <span class="text-xs text-blue-500 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">{{ $sales->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-blue-100 dark:border-slate-700">
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">No</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nama SPG</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Counter</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Produk / SKU</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Size</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Warna</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Qty (Psg)</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-800">
                    @forelse($sales as $index => $sale)
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors group">
                            <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-blue-900 dark:text-white">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</span>
                                <span class="block text-[10px] text-blue-400 dark:text-blue-500 font-medium">{{ \Carbon\Carbon::parse($sale->date)->locale('id')->translatedFormat('l') }}</span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white text-[10px] font-bold shadow-sm flex-shrink-0">
                                        {{ strtoupper(substr($sale->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-blue-900 dark:text-white text-sm">{{ $sale->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                @if($sale->user->location)
                                    <span class="inline-flex items-center gap-1 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $sale->user->location->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $sale->sku }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $sale->size ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $sale->warna ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="inline-flex items-center justify-center min-w-[2.5rem] bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-lg text-xs font-black">
                                    {{ $sale->qty }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="font-bold text-blue-900 dark:text-blue-200">Rp {{ number_format($sale->nominal, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-slate-800 dark:to-slate-700 rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-blue-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-blue-400 dark:text-slate-500">Belum ada data penjualan</p>
                                    <p class="text-xs text-blue-300 dark:text-slate-600 mt-1">Coba ubah filter periode atau counter untuk melihat data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($sales->count() > 0)
                <tfoot>
                    <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/80 border-t-2 border-blue-200 dark:border-slate-700">
                        <td colspan="7" class="px-6 py-4 text-right">
                            <span class="text-xs font-black text-blue-800 dark:text-blue-200 uppercase tracking-widest">Grand Total</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-3 py-1.5 rounded-lg text-sm font-black">
                                {{ number_format($totalQty, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-base font-black text-blue-900 dark:text-white">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Per-SPG Summary (Grouped) --}}
    @if($sales->count() > 0 && !$userId)
    @php
        $grouped = $sales->groupBy(function($item) {
            return $item->user_id;
        });
    @endphp
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Ringkasan Per SPG</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-blue-100 dark:border-slate-700">
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nama SPG</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Counter</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Jumlah Transaksi</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Total Qty</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Total Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-800">
                    @foreach($grouped as $uid => $items)
                    @php
                        $spg = $items->first()->user;
                    @endphp
                    <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white text-[10px] font-bold shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($spg->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-bold text-blue-900 dark:text-white">{{ $spg->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($spg->location)
                                <span class="inline-flex items-center gap-1 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $spg->location->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-700 dark:text-slate-300">{{ $items->count() }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="inline-flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-lg text-xs font-black">
                                {{ number_format($items->sum('qty'), 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-black text-blue-900 dark:text-blue-200">
                            Rp {{ number_format($items->sum('nominal'), 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gradient-to-r from-violet-50 to-fuchsia-50 dark:from-slate-800/80 dark:to-slate-800/80 border-t-2 border-violet-200 dark:border-slate-700">
                        <td colspan="3" class="px-6 py-4 text-right">
                            <span class="text-xs font-black text-violet-800 dark:text-violet-200 uppercase tracking-widest">Grand Total</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-3 py-1.5 rounded-lg text-sm font-black">
                                {{ number_format($totalQty, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-base font-black text-violet-900 dark:text-white">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
function salesReport() {
    return {
        period: '{{ request('period', 'today') }}'
    };
}
</script>
@endsection
