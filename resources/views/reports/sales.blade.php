@extends('layouts.master')

@section('title', 'Laporan Penjualan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    <form method="GET" action="{{ route($routeName) }}" @submit.prevent="fetchData()">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                {{-- Periode --}}
                <div class="space-y-1">
                    <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Periode</label>
                    <select name="period" x-model="period" @change="onPeriodChange()"
                        class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer transition">
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                {{-- Rentang Tanggal (Flatpickr) --}}
                <div class="space-y-1" x-show="period === 'custom'" x-transition x-cloak>
                    <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Rentang Tanggal</label>
                    <input type="text" id="date_range_picker" name="custom_date_range" placeholder="Pilih rentang tanggal"
                        class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white outline-none cursor-pointer focus:ring-2 focus:ring-blue-500 transition">
                    <input type="hidden" name="start_date" x-model="startDate">
                    <input type="hidden" name="end_date" x-model="endDate">
                </div>

                {{-- Counter / Lokasi --}}
                <div class="space-y-1">
                    <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Counter / Lokasi</label>
                    <select name="location_id" x-model="locationId" @change="fetchData()"
                        class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer transition">
                        <option value="">-- Semua Counter --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ ($locationId == $loc->id) ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama SPG --}}
                <div class="space-y-1">
                    <label class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Nama SPG</label>
                    <select name="user_id" x-model="userId" @change="fetchData()"
                        class="w-full bg-blue-50 dark:bg-slate-700 border border-blue-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm font-medium text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer transition">
                        <option value="">-- Semua SPG --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($userId == $u->id) ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
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
                <h3 class="text-2xl md:text-3xl font-black tracking-tighter" x-text="totalNominal">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
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
                <h3 class="text-2xl md:text-3xl font-black text-emerald-700 dark:text-emerald-300 tracking-tighter" x-text="totalQty">{{ number_format($totalQty, 0, ',', '.') }}</h3>
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
                <h3 class="text-2xl md:text-3xl font-black text-blue-700 dark:text-blue-300 tracking-tighter" x-text="transactionCount">{{ $sales->count() }}</h3>
            </div>
        </div>
    </div>

    {{-- ═══ TAB SYSTEM ═══ --}}
    <div>
        {{-- Tab Buttons --}}
        <div class="flex items-center gap-2 mb-4">
            <button type="button" id="tab-btn-detail"
                onclick="switchTab('detail')"
                class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Detail Transaksi
            </button>
            <button type="button" id="tab-btn-ranking"
                x-show="!userId" x-cloak
                onclick="switchTab('ranking')"
                class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                🏆 Peringkat SPG
            </button>
        </div>

        {{-- Tab: Detail Transaksi --}}
        <div id="tab-detail" class="tab-panel bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Detail Penjualan</span>
                </h3>
                <span class="text-xs text-blue-500 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                    <span x-text="transactionCount">{{ $sales->count() }}</span> data
                </span>
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto relative">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-blue-100 dark:border-slate-700">
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">No</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Tanggal</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Nama SPG</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Counter</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Produk / SKU</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Size</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-right text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Qty (Psg)</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-6 py-3.5 text-right text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody" class="divide-y divide-blue-50 dark:divide-slate-800">
                        @include('reports.partials.sales_table_body', ['sales' => $sales])
                    </tbody>
                    <tfoot id="salesTableFoot">
                        @include('reports.partials.sales_table_foot', ['sales' => $sales, 'totalQty' => $totalQty, 'totalNominal' => $totalNominal])
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tab: Peringkat SPG --}}
        <div id="tab-ranking" class="tab-panel hidden bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                    <span class="text-lg">🏆</span>
                    <span>Peringkat Seluruh SPG</span>
                </h3>
                <span class="text-xs text-violet-500 dark:text-violet-400 font-semibold bg-violet-50 dark:bg-violet-900/30 px-3 py-1 rounded-full">
                    {{ count($spgRanking) }} SPG terdaftar
                </span>
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto relative">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-blue-100 dark:border-slate-700">
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 w-16 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Rank</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Nama SPG</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Transaksi</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Total Qty</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-left text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Total Nominal</th>
                            <th class="sticky top-0 bg-slate-50 dark:bg-slate-800 px-4 py-3.5 text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider z-10 shadow-[inset_0_-1px_0_rgba(219,234,254,1)] dark:shadow-[inset_0_-1px_0_rgba(51,65,85,1)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rankingTableBody" class="divide-y divide-blue-50 dark:divide-slate-800">
                        @include('reports.partials.sales_ranking_body', ['spgRanking' => $spgRanking, 'maxNominal' => $maxNominal, 'userId' => $userId])
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Per-SPG Summary (tersembunyi, masih ada untuk kompatibilitas AJAX) --}}
        <div id="spgSummarySection" x-show="hasSpgSummary" x-cloak class="hidden">
            <table class="hidden">
                <tbody id="spgSummaryTableBody">
                    @include('reports.partials.sales_spg_summary_body', ['sales' => $sales, 'userId' => $userId])
                </tbody>
                <tfoot id="spgSummaryTableFoot">
                    @include('reports.partials.sales_spg_summary_foot', ['sales' => $sales, 'totalQty' => $totalQty, 'totalNominal' => $totalNominal, 'userId' => $userId])
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ═══ MODAL DETAIL SPG ═══ --}}
<div id="spg-detail-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-blue-950/60 backdrop-blur-sm" onclick="closeSpgDetailModal()"></div>

    {{-- Drawer --}}
    <div id="spg-detail-drawer" class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white dark:bg-slate-900 shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-out">
        {{-- Drawer Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-blue-100 dark:border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h2 id="spg-modal-title" class="text-base font-extrabold text-blue-950 dark:text-white">Detail Penjualan SPG</h2>
                    <p class="text-xs text-blue-500 dark:text-blue-400 font-medium" id="spg-modal-subtitle">Semua transaksi pada periode yang dipilih</p>
                </div>
            </div>
            <button onclick="closeSpgDetailModal()" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center justify-center text-slate-500 hover:text-red-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Summary badges in modal --}}
        <div id="spg-modal-summary" class="px-6 py-3 border-b border-blue-50 dark:border-slate-800 flex items-center gap-4 flex-shrink-0 flex-wrap">
            <span class="text-xs font-bold text-slate-400">Memuat...</span>
        </div>

        {{-- Table --}}
        <div class="overflow-y-auto flex-1 px-2 py-2">
            <table class="w-full text-sm">
                <thead class="sticky top-0">
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-blue-100 dark:border-slate-700">
                        <th class="text-left px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">No</th>
                        <th class="text-left px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Produk / SKU</th>
                        <th class="text-left px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Size</th>
                        <th class="text-right px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Qty</th>
                        <th class="text-right px-4 py-3 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nominal</th>
                    </tr>
                </thead>
                <tbody id="spg-modal-tbody" class="divide-y divide-blue-50 dark:divide-slate-800">
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Footer total --}}
        <div id="spg-modal-footer" class="px-6 py-4 border-t border-blue-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between flex-shrink-0">
            <span class="text-xs text-slate-400 font-medium">Total</span>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <span class="block text-[10px] text-emerald-500 font-bold uppercase tracking-wider">Total Qty</span>
                    <span id="spg-modal-total-qty" class="text-sm font-black text-emerald-700 dark:text-emerald-300">—</span>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] text-violet-500 font-bold uppercase tracking-wider">Total Nominal</span>
                    <span id="spg-modal-total-nominal" class="text-sm font-black text-blue-900 dark:text-blue-200">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<style>
    /* Tab active / inactive */
    .tab-btn {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .dark .tab-btn {
        background: #1e293b;
        color: #94a3b8;
        border-color: #334155;
    }
    .tab-btn.active {
        background: linear-gradient(135deg, #7c3aed, #a21caf);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(124,58,237,0.3);
    }
</style>

<script>
// ── Tab Switcher ──────────────────────────────────────────────────────────────
function switchTab(name) {
    ['detail','ranking'].forEach(t => {
        const panel = document.getElementById('tab-' + t);
        const btn   = document.getElementById('tab-btn-' + t);
        if (t === name) {
            panel.classList.remove('hidden');
            btn.classList.add('active');
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('active');
        }
    });
    // Persist tab choice
    window._activeTab = name;
}

// ── SPG Detail Modal ──────────────────────────────────────────────────────────
// Store ranking data passed from server for modal use (client-side only)
window._spgRankingData = {!! json_encode($spgRanking->map(function($r) {
    return [
        'user_id'       => $r['user']->id,
        'name'          => $r['user']->name,
        'location'      => $r['user']->location?->name ?? '-',
        'total_qty'     => $r['total_qty'],
        'total_nominal' => $r['total_nominal'],
        'total_trx'     => $r['total_trx'],
        'transactions'  => $r['transactions']->map(function($t) {
            return [
                'date'    => $t->date,
                'sku'     => $t->sku,
                'size'    => $t->size,
                'qty'     => $t->qty,
                'nominal' => $t->nominal,
            ];
        })->values()->all(),
    ];
})->values()->all()) !!};

function openSpgDetailModal(spgId, spgName) {
    const modal  = document.getElementById('spg-detail-modal');
    const drawer = document.getElementById('spg-detail-drawer');

    // Set header
    document.getElementById('spg-modal-title').textContent = spgName;
    document.getElementById('spg-modal-subtitle').textContent = 'Detail transaksi pada periode yang dipilih';

    // Find data
    const spg = window._spgRankingData.find(s => s.user_id == spgId);

    if (!spg) {
        document.getElementById('spg-modal-tbody').innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Data tidak ditemukan.</td></tr>';
    } else {
        // Summary
        document.getElementById('spg-modal-summary').innerHTML = `
            <span class="inline-flex items-center gap-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-3 py-1.5 rounded-lg text-xs font-bold">
                📍 ${spg.location}
            </span>
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-3 py-1.5 rounded-lg text-xs font-bold">
                🛍️ ${spg.total_trx} Transaksi
            </span>
        `;

        // Render table rows
        let rows = '';
        if (spg.transactions.length === 0) {
            rows = '<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Tidak ada transaksi.</td></tr>';
        } else {
            spg.transactions.forEach((t, i) => {
                const d = new Date(t.date);
                const dateStr = `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
                rows += `<tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors">
                    <td class="px-4 py-3 text-slate-500 text-xs font-medium">${i+1}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="font-semibold text-blue-900 dark:text-white text-xs">${dateStr}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">${t.sku ?? '-'}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">${t.size ?? '-'}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-lg text-xs font-black">${t.qty}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-bold text-blue-900 dark:text-blue-200 text-xs">Rp ${Number(t.nominal).toLocaleString('id-ID')}</span>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('spg-modal-tbody').innerHTML = rows;

        // Footer totals
        document.getElementById('spg-modal-total-qty').textContent = Number(spg.total_qty).toLocaleString('id-ID') + ' psg';
        document.getElementById('spg-modal-total-nominal').textContent = 'Rp ' + Number(spg.total_nominal).toLocaleString('id-ID');
    }

    // Show modal + animate drawer in
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        drawer.style.transform = 'translateX(0)';
    });
    document.body.style.overflow = 'hidden';
}

function closeSpgDetailModal() {
    const modal  = document.getElementById('spg-detail-modal');
    const drawer = document.getElementById('spg-detail-drawer');
    drawer.style.transform = 'translateX(100%)';
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// Close on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeSpgDetailModal();
});

// Initialize tab
switchTab('detail');

// ── Alpine.js Sales Report ────────────────────────────────────────────────────
function salesReport() {
    return {
        period: '{{ request('period', request('start_date') ? 'custom' : 'today') }}',
        startDate: '{{ request('start_date', '') }}',
        endDate: '{{ request('end_date', '') }}',
        locationId: '{{ request('location_id', $locationId ?? '') }}',
        userId: '{{ request('user_id', $userId ?? '') }}',
        totalNominal: 'Rp {{ number_format($totalNominal, 0, ',', '.') }}',
        totalQty: '{{ number_format($totalQty, 0, ',', '.') }}',
        transactionCount: '{{ $sales->count() }}',
        hasSpgSummary: {{ ($sales->count() > 0 && !$userId) ? 'true' : 'false' }},
        fp: null,

        initFlatpickr() {
            const el = document.getElementById('date_range_picker');
            if (el && !this.fp) {
                this.fp = flatpickr(el, {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    locale: "id",
                    defaultDate: (this.startDate && this.endDate) ? [this.startDate, this.endDate] : [],
                    onChange: (selectedDates) => {
                        if (selectedDates.length === 2) {
                            const fmt = (d) => d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                            this.startDate = fmt(selectedDates[0]);
                            this.endDate   = fmt(selectedDates[1]);
                            this.fetchData();
                        }
                    }
                });
            }
        },

        init() {
            this.$watch('period', (val) => {
                if (val === 'custom') this.$nextTick(() => this.initFlatpickr());
            });
            if (this.period === 'custom') this.$nextTick(() => this.initFlatpickr());
        },

        onPeriodChange() {
            const today = new Date();
            const fmt = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

            if (this.period === 'today') {
                this.startDate = fmt(today); this.endDate = fmt(today);
            } else if (this.period === 'week') {
                const day  = today.getDay();
                const mon  = new Date(today); mon.setDate(today.getDate() + (day === 0 ? -6 : 1 - day));
                const sun  = new Date(mon);   sun.setDate(mon.getDate() + 6);
                this.startDate = fmt(mon); this.endDate = fmt(sun);
            } else if (this.period === 'month') {
                this.startDate = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
                this.endDate   = fmt(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            }

            if (this.period !== 'custom' && this.fp) this.fp.clear();
            else if (this.period === 'custom' && this.fp && this.startDate && this.endDate) this.fp.setDate([this.startDate, this.endDate]);

            this.fetchData();
        },

        fetchData() {
            const params = new URLSearchParams({
                period:      this.period,
                start_date:  this.startDate,
                end_date:    this.endDate,
                location_id: this.locationId,
                user_id:     this.userId
            });
            window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                this.totalNominal    = data.totalNominal;
                this.totalQty        = data.totalQty;
                this.transactionCount= data.transactionCount;
                this.hasSpgSummary   = data.hasSpgSummary;

                if (data.spgRankingData) {
                    window._spgRankingData = data.spgRankingData;
                }

                if (this.userId) {
                    switchTab('detail');
                }

                document.getElementById('salesTableBody').innerHTML = data.htmlTableBody;
                document.getElementById('salesTableFoot').innerHTML = data.htmlTableFoot;

                const rankBody = document.getElementById('rankingTableBody');
                if (rankBody && data.htmlRankingBody) rankBody.innerHTML = data.htmlRankingBody;

                const spgB = document.getElementById('spgSummaryTableBody');
                const spgF = document.getElementById('spgSummaryTableFoot');
                if (spgB && data.htmlSpgSummaryTableBody) spgB.innerHTML = data.htmlSpgSummaryTableBody;
                if (spgF && data.htmlSpgSummaryTableFoot) spgF.innerHTML = data.htmlSpgSummaryTableFoot;
            })
            .catch(err => console.error('Error fetching sales reports:', err));
        }
    };
}
</script>
@endsection
