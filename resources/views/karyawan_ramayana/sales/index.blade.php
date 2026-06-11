@extends('layouts.master')
@section('title', 'Data Penjualan')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Data Penjualan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data input penjualan harian Anda.</p>
        </div>
        <a href="{{ route('karyawan_ramayana.sales.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Input Penjualan
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('warning'))
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-xl border border-yellow-200 dark:border-yellow-800 flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div class="text-sm font-medium">{!! session('warning') !!}</div>
    </div>
    @endif

    <!-- Filter Bulan -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 md:p-6 shadow-sm">
        <form action="{{ route('karyawan_ramayana.sales.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors dark:[color-scheme:dark]">
            </div>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-slate-800 dark:bg-slate-700 text-white font-medium rounded-xl hover:bg-slate-900 dark:hover:bg-slate-600 transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/50 rounded-2xl p-6 flex flex-col justify-center items-center text-center">
            <p class="text-xs font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Total Nominal Bulan Ini</p>
            <p class="text-2xl font-black text-blue-700 dark:text-blue-300">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl p-6 flex flex-col justify-center items-center text-center">
            <p class="text-xs font-black text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-1">Total Qty (Psg) Bulan Ini</p>
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ number_format($totalQty, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Table Data -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">SKU / Produk</th>
                        <th class="p-4">Size</th>
                        <th class="p-4">Qty (Psg)</th>
                        <th class="p-4">Nominal (Rp)</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="p-4 text-sm font-medium text-slate-800 dark:text-slate-200">
                            {{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}
                        </td>
                        <td class="p-4 text-sm font-bold text-slate-800 dark:text-slate-200">
                            {{ $sale->sku }}
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                            <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded text-xs ml-1 border border-slate-200 dark:border-slate-700">{{ $sale->size ?? '-' }}</span>
                        </td>
                        <td class="p-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{ $sale->qty }}
                        </td>
                        <td class="p-4 text-sm font-bold text-green-600 dark:text-green-400">
                            {{ number_format($sale->nominal, 0, ',', '.') }}
                        </td>
                        <td class="p-4 flex items-center justify-end space-x-2">
                            <form action="{{ route('karyawan_ramayana.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Hapus data penjualan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                            Belum ada data penjualan di bulan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
