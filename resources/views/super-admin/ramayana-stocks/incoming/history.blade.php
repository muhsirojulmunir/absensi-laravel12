@extends('layouts.master')
@section('title', 'Riwayat Barang Masuk - ' . ($user->location->name ?? $user->name))
@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Kartu Stok
            </a>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Riwayat Barang Masuk</h1>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-2">
                {{ $user->name }} - {{ $user->location->name ?? 'Belum Ada Lokasi' }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('super-admin.ramayana-stocks.incoming.create', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Barang Masuk Baru
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 dark:from-blue-900 dark:to-blue-800 rounded-xl p-5 shadow-lg text-white">
            <p class="text-xs font-bold text-blue-100 uppercase tracking-wider mb-2">Total Transaksi</p>
            <h3 class="text-2xl md:text-3xl font-bold">{{ $totalRecords }}</h3>
            <p class="text-xs text-blue-200 mt-2 font-medium">Surat Jalan / Batch</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 dark:from-emerald-900 dark:to-emerald-800 rounded-xl p-5 shadow-lg text-white col-span-1 md:col-span-2">
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider mb-2">Total Seluruh Barang Masuk</p>
            <h3 class="text-2xl md:text-3xl font-bold">{{ number_format($grandTotalQty, 0, ',', '.') }} QTY</h3>
            <p class="text-xs text-emerald-200 mt-2 font-medium">Dari {{ $grandTotalItems }} jenis barang</p>
        </div>
    </div>

    @if($groupedByMonth->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 p-12 text-center shadow-sm">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 dark:bg-slate-700 mb-4">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <p class="text-blue-950 dark:text-white font-semibold text-lg mb-1">Tidak ada riwayat barang masuk</p>
            <p class="text-blue-500 dark:text-blue-400 text-sm">Mulai tambahkan barang masuk untuk melihat riwayat di sini.</p>
        </div>
    @else
        <!-- Accordion Cards by Month -->
        <div class="space-y-4" x-data="{ expanded: new Set([0]) }">
            @php $index = 0; @endphp
            @foreach($groupedByMonth as $month => $stocks)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <!-- Month Header / Toggle -->
                <button @click="expanded.has({{ $index }}) ? expanded.delete({{ $index }}) : expanded.add({{ $index }})"
                    class="w-full px-6 py-5 flex items-center justify-between hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors">

                    <div class="flex-1 text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-blue-950 dark:text-white">{{ $month }}</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stocks->count() }} transaksi • {{ number_format($stocks->sum('total_qty'), 0, ',', '.') }} QTY</p>
                            </div>
                        </div>
                    </div>

                    <svg :class="{ 'rotate-180': expanded.has({{ $index }}) }" class="w-5 h-5 text-blue-600 dark:text-blue-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7m0 0L5 14m7-7v12"></path>
                    </svg>
                </button>

                <!-- Period Details (Collapsible) -->
                <div x-show="expanded.has({{ $index }})" x-transition class="border-t border-blue-50 dark:border-slate-700 bg-blue-50/30 dark:bg-slate-900/20">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-blue-50/50 dark:bg-slate-800/50">
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Tanggal Input</th>
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Tanggal DO / Surat Jalan</th>
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase text-center">Jml Jenis Barang</th>
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase text-center">Total Qty</th>
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Oleh</th>
                                    <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                                @foreach($stocks as $stock)
                                <tr class="hover:bg-white dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ $stock->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-bold text-blue-700 dark:text-blue-300">
                                        {{ $stock->date->translatedFormat('l, d F Y') }}
                                        @if($stock->note)
                                        <p class="text-[10px] font-normal text-slate-400 mt-0.5">{{ $stock->note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                                            {{ $stock->total_items }} Item
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold text-sm">
                                            {{ number_format($stock->total_qty, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-medium text-blue-600 dark:text-blue-400">
                                        {{ $stock->createdBy->name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('super-admin.ramayana-stocks.incoming.show', [$user->id, $stock->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-800/50 rounded-lg text-xs font-bold transition-colors">
                                            Detail
                                        </a>
                                        <form action="{{ route('super-admin.ramayana-stocks.incoming.destroy', [$user->id, $stock->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus riwayat ini? Stok yang sudah bertambah akan dikurangi kembali.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-800/50 rounded-lg text-xs font-bold transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @php $index++; @endphp
            @endforeach
        </div>
    @endif
</div>

@endsection
