@extends('layouts.master')
@section('title', 'Riwayat Pembayaran Lunas')
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Riwayat Pembayaran Lunas</h1>
                <p class="text-sm text-blue-500 dark:text-blue-400 mt-2">Histori pembayaran uang makan karyawan per periode</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 dark:from-blue-900 dark:to-blue-800 rounded-xl p-5 shadow-lg text-white">
                    <p class="text-xs font-bold text-blue-100 uppercase tracking-wider mb-2">Total Pembayaran</p>
                    <h3 class="text-2xl md:text-3xl font-bold">{{ $totalPayments }}</h3>
                    <p class="text-xs text-blue-200 mt-2 font-medium">Transaksi</p>
                </div>

                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 dark:from-emerald-900 dark:to-emerald-800 rounded-xl p-5 shadow-lg text-white col-span-1 md:col-span-2">
                    <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider mb-2">Total Dana Terbayar</p>
                    <h3 class="text-2xl md:text-3xl font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                    <p class="text-xs text-emerald-200 mt-2 font-medium">Seluruh riwayat pembayaran</p>
                </div>
            </div>
        </div>

        @if($groupedByPeriod->isEmpty())
            <!-- Empty State -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 p-12 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 dark:bg-slate-700 mb-4">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <p class="text-blue-950 dark:text-white font-semibold text-lg mb-1">Tidak ada riwayat pembayaran</p>
                <p class="text-blue-500 dark:text-blue-400 text-sm">Mulai tandai pembayaran uang makan untuk melihat riwayat di sini</p>
            </div>
        @else
            <!-- Accordion Cards by Period -->
            <div class="space-y-4" x-data="{
                expanded: @if($groupedByPeriod->count() === 1) new Set([0]) @else new Set() @endif
            }">
                @foreach($groupedByPeriod as $index => $period)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Period Header / Toggle -->
                    <button @click="expanded.has({{ $loop->index }}) ? expanded.delete({{ $loop->index }}) : expanded.add({{ $loop->index }})"
                        class="w-full px-6 py-5 flex items-center justify-between hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors">

                        <div class="flex-1 text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-blue-950 dark:text-white">{{ $period['label'] }}</p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $period['totalCount'] }} pembayaran • Rp {{ number_format($period['totalAmount'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <svg :class="{ 'rotate-180': expanded.has({{ $loop->index }}) }" class="w-5 h-5 text-blue-600 dark:text-blue-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7m0 0L5 14m7-7v12"></path>
                        </svg>
                    </button>

                    <!-- Period Details (Collapsible) -->
                    <div x-show="expanded.has({{ $loop->index }})" @click.outside="expanded.delete({{ $loop->index }})" class="border-t border-blue-50 dark:border-slate-700 bg-blue-50/30 dark:bg-slate-900/20">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-blue-50/50 dark:bg-slate-800/50">
                                        <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Karyawan</th>
                                        <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Periode</th>
                                        <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase text-right">Jumlah</th>
                                        <th class="px-6 py-3 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Pembayar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                                    @foreach($period['items'] as $payment)
                                    <tr class="hover:bg-white dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-3.5 whitespace-nowrap text-xs font-medium text-blue-600 dark:text-blue-400">
                                            {{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d M Y, H:i') }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                                                {{ $payment->user->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-xs text-blue-600 dark:text-blue-400">
                                            {{ \Carbon\Carbon::parse($payment->start_date)->translatedFormat('d M') }} — {{ \Carbon\Carbon::parse($payment->end_date)->translatedFormat('d M') }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold text-sm">
                                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-xs font-medium text-blue-600 dark:text-blue-400">
                                            {{ $payment->paidBy->name ?? '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Period Summary Footer -->
                        <div class="px-6 py-4 bg-emerald-50/30 dark:bg-emerald-900/10 border-t border-emerald-100 dark:border-emerald-900/20 flex justify-between items-center">
                            <span class="text-sm font-semibold text-blue-950 dark:text-white">Subtotal Periode</span>
                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($period['totalAmount'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
