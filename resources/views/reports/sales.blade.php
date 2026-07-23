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

                {{-- Rentang Tanggal (Flatpickr) - tampil hanya saat Custom --}}
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
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
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
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
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

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-blue-900 dark:text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span>Detail Penjualan</span>
            </h3>
            <span class="text-xs text-blue-500 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full"><span x-text="transactionCount">{{ $sales->count() }}</span> data</span>
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
                        <!-- removed warna th -->
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Qty (Psg)</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Nominal (Rp)</th>
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

    {{-- Per-SPG Summary (Grouped) --}}
    @if($sales->count() > 0 && !$userId)
    @php
        $grouped = $sales->groupBy(function($item) {
            return $item->user_id;
        });
    @endphp
    <div id="spgSummarySection" x-show="hasSpgSummary" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-blue-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
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
                <tbody id="spgSummaryTableBody" class="divide-y divide-blue-50 dark:divide-slate-800">
                    @include('reports.partials.sales_spg_summary_body', ['sales' => $sales, 'userId' => $userId])
                </tbody>
                <tfoot id="spgSummaryTableFoot">
                    @include('reports.partials.sales_spg_summary_foot', ['sales' => $sales, 'totalQty' => $totalQty, 'totalNominal' => $totalNominal, 'userId' => $userId])
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Flatpickr JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
function salesReport() {
    return {
        period: '{{ request('period', request('start_date') ? 'custom' : 'today') }}',
        startDate: '{{ request('start_date', $startDate ?? '') }}',
        endDate: '{{ request('end_date', $endDate ?? '') }}',
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
                    onChange: (selectedDates, dateStr, instance) => {
                        if (selectedDates.length === 2) {
                            const fmt = (d) => {
                                return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                            };
                            this.startDate = fmt(selectedDates[0]);
                            this.endDate = fmt(selectedDates[1]);
                            this.fetchData();
                        }
                    }
                });
            }
        },

        init() {
            this.$watch('period', (val) => {
                if (val === 'custom') {
                    this.$nextTick(() => this.initFlatpickr());
                }
            });

            if (this.period === 'custom') {
                this.$nextTick(() => this.initFlatpickr());
            }
        },

        onPeriodChange() {
            const today = new Date();
            const formatDate = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (this.period === 'today') {
                this.startDate = formatDate(today);
                this.endDate = formatDate(today);
            } else if (this.period === 'week') {
                const dayOfWeek = today.getDay();
                const distanceToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
                const monday = new Date(today);
                monday.setDate(today.getDate() + distanceToMonday);
                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                this.startDate = formatDate(monday);
                this.endDate = formatDate(sunday);
            } else if (this.period === 'month') {
                const firstDate = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                this.startDate = formatDate(firstDate);
                this.endDate = formatDate(lastDate);
            }

            if (this.period !== 'custom' && this.fp) {
                this.fp.clear();
            } else if (this.period === 'custom' && this.fp && this.startDate && this.endDate) {
                this.fp.setDate([this.startDate, this.endDate]);
            }

            this.fetchData();
        },

        fetchData() {
            const params = new URLSearchParams({
                period: this.period,
                start_date: this.startDate,
                end_date: this.endDate,
                location_id: this.locationId,
                user_id: this.userId
            });

            window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.totalNominal = data.totalNominal;
                this.totalQty = data.totalQty;
                this.transactionCount = data.transactionCount;
                this.hasSpgSummary = data.hasSpgSummary;

                document.getElementById('salesTableBody').innerHTML = data.htmlTableBody;
                document.getElementById('salesTableFoot').innerHTML = data.htmlTableFoot;

                const spgSectionBody = document.getElementById('spgSummaryTableBody');
                const spgSectionFoot = document.getElementById('spgSummaryTableFoot');
                if (spgSectionBody && data.htmlSpgSummaryTableBody) {
                    spgSectionBody.innerHTML = data.htmlSpgSummaryTableBody;
                }
                if (spgSectionFoot && data.htmlSpgSummaryTableFoot) {
                    spgSectionFoot.innerHTML = data.htmlSpgSummaryTableFoot;
                }
            })
            .catch(err => console.error('Error fetching filtered sales reports:', err));
        }
    };
}
</script>
@endsection
