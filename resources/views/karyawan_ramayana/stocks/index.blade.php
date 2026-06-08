@extends('layouts.master')
@section('title', 'Stok Counter Anda')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ search: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Stok Counter Anda</h2>
            <p class="text-sm font-semibold text-fuchsia-600 dark:text-fuchsia-400 mt-1">Ramayana {{ $user->location->name ?? 'Counter' }}</p>
        </div>
        <div class="px-4 py-2 bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-700 dark:text-fuchsia-400 rounded-full text-sm font-bold border border-fuchsia-200 dark:border-fuchsia-800/50">
            Total: {{ number_format($totalOverallStock, 0, ',', '.') }} ITEMS
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
        class="p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-start shadow-sm">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        
        <!-- Search Bar -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" x-model="search" placeholder="Cari produk atau kode barang..." style="padding-left: 2.75rem;"
                class="w-full bg-slate-50 dark:bg-slate-800 border-0 text-slate-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-blue-500 p-4 transition-colors font-medium">
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-500 dark:text-slate-400 px-1">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full" style="background-color: #22c55e;"></span> Stok Aman (> 5)
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full" style="background-color: #eab308;"></span> Stok Menipis (1 - 5)
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full" style="background-color: #ef4444;"></span> Stok Habis (0) / Minus
            </div>
        </div>

        <!-- Stok Cards -->
        <div class="space-y-4">
            @forelse($groupedStocks as $stock)
            <div class="stock-card bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 relative transition-transform hover:-translate-y-0.5" 
                 x-show="'{{ strtolower($stock['sku'] . ' ' . ($stock['kode_barang'] ?? '')) }}'.includes(search.toLowerCase()) || search === ''">
                
                <div class="flex justify-between items-start mb-1">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight truncate">{{ $stock['sku'] }}</h3>
                        @if(!empty($stock['kode_barang']))
                        <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">Kode: {{ $stock['kode_barang'] }}</p>
                        @endif
                        @if(!empty($stock['warna']))
                        <p class="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5">{{ $stock['warna'] }}</p>
                        @endif
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div class="px-3 py-1 bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-700 dark:text-fuchsia-400 rounded-full text-xs font-bold whitespace-nowrap border border-fuchsia-200 dark:border-fuchsia-800/50">
                            {{ $stock['total_stock'] }} ITEMS
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 mt-3">
                    @foreach($stock['sizes'] as $size => $sizeData)
                        @php
                            $sQty = is_array($sizeData) ? $sizeData['qty'] : $sizeData;
                            $sSatuan = is_array($sizeData) ? ($sizeData['satuan'] ?? 'PSG') : 'PSG';
                            if ($sQty <= 0) {
                                $color = '#ef4444';
                            } elseif ($sQty <= 5) {
                                $color = '#eab308';
                            } else {
                                $color = '#22c55e';
                            }
                        @endphp
                        <span style="border: 1px solid {{ $color }}; color: {{ $color }}; background-color: {{ $color }}1A; padding: 0.2rem 0.5rem; border-radius: 0.375rem; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center;">
                            @if(!empty($size)){{ $size }}@else -@endif: {{ $sQty }} {{ $sSatuan }}
                        </span>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-10">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Stok Anda masih kosong.</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Stok akan muncul setelah Super Admin mengimport data.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
