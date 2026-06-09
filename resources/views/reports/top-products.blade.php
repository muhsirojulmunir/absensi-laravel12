@extends('layouts.master')

@section('title', 'Analisis Top Produk')

@section('content')
<div class="min-h-screen" style="background-color: #0b1120; color: #e2e8f0; font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;" x-data="topProductsReport()">
    <div class="max-w-7xl mx-auto px-4 py-8 md:px-6 lg:px-8">

        {{-- ═══════ HEADER ═══════ --}}
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #f59e0b, #ea580c);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Analisis Top Produk</h1>
                <p class="text-sm font-medium" style="color: #475569;">Tracking produk paling laku berdasarkan penjualan SPG</p>
            </div>
        </div>

        {{-- ═══════ FILTER ═══════ --}}
        <form method="GET" action="{{ route($routeName) }}">
            <div class="flex flex-wrap items-end gap-5 mb-8">

                {{-- Periode --}}
                <div>
                    <span class="block text-xs mb-2" style="color: #e2e8f0;">Periode</span>
                    <div class="flex items-center gap-2 h-10 px-3 rounded-lg" style="border: 1px solid #2a3a55; background-color: #151e32;">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <select name="period" x-model="period" onchange="this.form.submit()"
                            style="background: transparent; border: none; color: #e2e8f0; font-size: 13px; outline: none; cursor: pointer; appearance: auto;">
                            <option value="today" style="background-color: #0b1120;">Hari Ini</option>
                            <option value="week" style="background-color: #0b1120;">Minggu Ini</option>
                            <option value="month" style="background-color: #0b1120;">Bulan Ini</option>
                            <option value="year" style="background-color: #0b1120;">Tahun Ini</option>
                            <option value="custom" style="background-color: #0b1120;">Custom</option>
                        </select>
                    </div>
                </div>

                {{-- Custom Date --}}
                <div class="flex gap-3" x-show="period === 'custom'" x-cloak>
                    <div>
                        <span class="block text-xs mb-2" style="color: #e2e8f0;">Dari</span>
                        <div class="flex items-center h-10 px-3 rounded-lg" style="border: 1px solid #2a3a55; background-color: #151e32;">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()"
                                style="background: transparent; border: none; color: #e2e8f0; font-size: 13px; outline: none; color-scheme: dark; width: 130px;">
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs mb-2" style="color: #e2e8f0;">Sampai</span>
                        <div class="flex items-center h-10 px-3 rounded-lg" style="border: 1px solid #2a3a55; background-color: #151e32;">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()"
                                style="background: transparent; border: none; color: #e2e8f0; font-size: 13px; outline: none; color-scheme: dark; width: 130px;">
                        </div>
                    </div>
                </div>

                {{-- Counter --}}
                <div>
                    <span class="block text-xs mb-2" style="color: #e2e8f0;">Fokus Counter</span>
                    <div class="flex items-center gap-2 h-10 px-3 rounded-lg" style="border: 1px solid #2a3a55; background-color: #151e32;">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <select name="location_id" onchange="this.form.submit()"
                            style="background: transparent; border: none; color: #e2e8f0; font-size: 13px; outline: none; cursor: pointer; appearance: auto;">
                            <option value="" style="background-color: #0b1120;">-- Semua Counter (Global) --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }} style="background-color: #0b1120;">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </form>

        {{-- ═══════ SECTION TITLE ═══════ --}}
        <h2 class="text-lg font-extrabold text-white mb-5">
            {{ $locationId ? 'Top Produk di Counter Terpilih' : 'Top 10 Produk Paling Laku (Global)' }}
        </h2>

        @if($globalSales->isEmpty())
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-10 rounded-xl text-center" style="background: linear-gradient(145deg, #18233a 0%, #111827 100%); border: 1px solid #2a3a55;">
                <svg class="w-14 h-14 mb-4" style="color: #1f2e47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="font-medium" style="color: #475569;">Belum ada data penjualan pada periode ini.</p>
            </div>
        @else

        {{-- ═══════ TOP 3 CARDS ═══════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($globalSales->take(3) as $index => $item)
                @php
                    $rank = $index + 1;
                    $trendVal = $item->trend ?? 0;
                    $trendColor = $trendVal > 0 ? '#22c55e' : ($trendVal < 0 ? '#ef4444' : '#64748b');
                    $trendSign = $trendVal > 0 ? '+' : '';
                @endphp

                <div class="flex flex-col justify-between p-6 rounded-xl transition-all duration-300"
                    style="background: linear-gradient(145deg, #18233a 0%, #111827 100%); border: 1px solid #2a3a55; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">

                    {{-- Top: Badge + Name + Trend --}}
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            {{-- Rank Badge --}}
                            <div class="flex-shrink-0 w-8 h-8 rounded-md flex items-center justify-center text-xs font-bold text-white"
                                style="background-color: #2563eb;">#{{ $rank }}</div>
                            <div class="min-w-0 pt-0.5">
                                <h3 class="text-sm font-bold text-white leading-snug">{{ $item->sku }}</h3>
                                @if($item->size)
                                    <p class="text-xs mt-1.5" style="color: #94a3b8;">Size: {{ $item->size }}</p>
                                @endif
                            </div>
                        </div>
                        {{-- Trend --}}
                        <div class="flex-shrink-0 flex items-center gap-1 ml-4 pt-0.5" style="color: {{ $trendColor }};">
                            @if($trendVal > 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            @elseif($trendVal < 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            @endif
                            <span class="text-xs font-bold">{{ $trendSign }}{{ $trendVal }}%</span>
                        </div>
                    </div>

                    {{-- Bottom: Pendapatan + Unit Terjual --}}
                    <div class="flex justify-between items-end pt-4" style="border-top: 1px solid #1e293b;">
                        <div>
                            <p class="text-xs mb-1.5" style="color: #94a3b8;">Pendapatan</p>
                            <p class="text-base font-bold text-white">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs mb-1.5" style="color: #94a3b8;">Unit Terjual</p>
                            <p class="text-base font-bold text-white">{{ number_format($item->total_qty, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ═══════ RANK 4–10 TABLE ═══════ --}}
        @if($globalSales->count() > 3)
            <div x-data="{ expanded: false }">
                <h3 class="text-base font-bold text-white mb-4">Peringkat 4–10</h3>

                <div class="rounded-xl overflow-hidden" style="background: linear-gradient(145deg, #18233a 0%, #111827 100%); border: 1px solid #2a3a55; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr style="background-color: #111827; border-bottom: 1px solid #2a3a55;">
                                    <th class="px-5 py-3 text-xs font-medium" style="color: #94a3b8;">Rank</th>
                                    <th class="px-5 py-3 text-xs font-medium" style="color: #94a3b8;">Nama Produk</th>
                                    <th class="px-5 py-3 text-xs font-medium" style="color: #94a3b8;">Size</th>
                                    <th class="px-5 py-3 text-xs font-medium text-center" style="color: #94a3b8;">Unit Terjual</th>
                                    <th class="px-5 py-3 text-xs font-medium text-right" style="color: #94a3b8;">Pendapatan</th>
                                    <th class="px-5 py-3 text-xs font-medium text-right" style="color: #94a3b8;">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($globalSales->skip(3)->values() as $index => $item)
                                    @php
                                        $rank = $index + 4;
                                        $trendVal = $item->trend ?? 0;
                                        $trendColor = $trendVal > 0 ? '#22c55e' : ($trendVal < 0 ? '#ef4444' : '#64748b');
                                        $trendSign = $trendVal > 0 ? '+' : '';
                                        $info = collect([
                                            $item->warna ?: null,
                                            $item->size ? 'Size ' . $item->size : null,
                                        ])->filter()->implode(' · ');
                                    @endphp
                                    <tr
                                        @if($rank > 6) x-show="expanded" x-cloak
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                        @endif
                                        style="border-bottom: 1px solid #1f2b42; transition: background-color 0.2s;"
                                        onmouseover="this.style.backgroundColor='#1e293b'"
                                        onmouseout="this.style.backgroundColor=''"
                                    >
                                        <td class="px-5 py-4 font-bold" style="color: #64748b;">#{{ $rank }}</td>
                                        <td class="px-5 py-4 font-bold text-white">{{ $item->sku }}</td>
                                        <td class="px-5 py-4" style="color: #475569;">{{ $info ?: '-' }}</td>
                                        <td class="px-5 py-4 text-center font-semibold text-white">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-right font-medium" style="color: #94a3b8;">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-right font-bold" style="color: {{ $trendColor }};">{{ $trendSign }}{{ $trendVal }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($globalSales->count() > 6)
                        <div class="py-3 text-center" style="border-top: 1px solid #1e293b;">
                            <button @click="expanded = !expanded" type="button"
                                class="text-xs font-bold transition-colors duration-200"
                                style="color: #22c55e;"
                                onmouseover="this.style.color='#4ade80'"
                                onmouseout="this.style.color='#22c55e'">
                                <span x-text="expanded ? 'Tutup Ranking Lainnya ↑' : 'Lihat Ranking Lainnya ↓'"></span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @endif {{-- end isEmpty --}}

        {{-- ═══════ INFO BANNER ═══════ --}}
        @if(!$locationId && !empty($topPerCounter))
            <div class="flex items-center gap-4 p-5 rounded-xl mt-8" style="background: linear-gradient(145deg, #18233a 0%, #111827 100%); border: 1px solid #2a3a55; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #1e293b;">
                    <svg class="w-5 h-5" style="color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">Ingin melihat Top Produk untuk tiap Counter?</h2>
                    <p class="text-xs mt-0.5" style="color: #94a3b8;">
                        Pilih salah satu counter di <strong class="text-white">Fokus Counter</strong> di atas untuk melihat laporan spesifik.
                    </p>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
function topProductsReport() {
    return {
        period: '{{ request('period', 'month') }}'
    };
}
</script>
@endsection