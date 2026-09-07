@php
    $masukCount = count($masukList ?? []);
    $lupaCount  = count($lupaAbsenList ?? []);
    $tidakCount = count($tidakHadirList ?? []);
    $maxCount   = max($masukCount, $lupaCount, $tidakCount);
@endphp

<div x-data="{ showAll: false }">
    {{-- Grid 3 Kolom Akumulasi Presensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ==================== KOLOM 1 (KIRI): MASUK ==================== --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-3.5 py-2.5 bg-emerald-500/10 dark:bg-emerald-950/40 border-b border-emerald-500/20 dark:border-emerald-800/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide">
                        Masuk &mdash; {{ $masukCount }} Hari
                    </span>
                </div>
                <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md bg-emerald-600 text-white shadow-xs">
                    {{ $masukCount }}
                </span>
            </div>
            @if($masukCount === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-6 italic px-3">Belum ada catatan masuk</p>
            @else
                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($masukList as $index => $item)
                        <div x-show="showAll || {{ $index }} < 5"
                             class="py-2.5 first:pt-0 last:pb-0 flex items-start justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 font-medium">{!! $item['jam_detail'] !!}</p>
                                @if(!empty($item['note']))
                                    <p class="text-slate-400 dark:text-slate-500 text-[10px] italic mt-0.5">Catatan: {{ $item['note'] }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                @if(!empty($item['lupa_tag']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                                        {{ $item['lupa_tag'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ ($item['status'] ?? '') === 'Terlambat' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' }}">
                                        {{ $item['status'] ?? 'Hadir' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ==================== KOLOM 2 (TENGAH): LUPA ABSEN ==================== --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-3.5 py-2.5 bg-amber-500/10 dark:bg-amber-950/40 border-b border-amber-500/20 dark:border-amber-800/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">
                        Lupa Absen &mdash; {{ $lupaCount }} Hari
                    </span>
                </div>
                <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ $lupaCount > 0 ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/20' }}">
                    {{ $lupaCount }}
                </span>
            </div>
            @if($lupaCount === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-6 italic px-3">Tidak ada lupa absen ✓</p>
            @else
                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($lupaAbsenList as $index => $item)
                        <div x-show="showAll || {{ $index }} < 5"
                             class="py-2.5 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                <p class="text-amber-700 dark:text-amber-400 text-[11px] font-medium mt-0.5">{{ $item['detail'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ==================== KOLOM 3 (KANAN): TIDAK HADIR / LIBUR ==================== --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-3.5 py-2.5 bg-red-500/10 dark:bg-red-950/40 border-b border-red-500/20 dark:border-red-900/40 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                    <span class="text-xs font-bold text-red-800 dark:text-red-300 uppercase tracking-wide">
                        Tidak Hadir / Libur &mdash; {{ $tidakCount }} Hari
                    </span>
                </div>
                <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ $tidakCount > 0 ? 'bg-red-600 text-white shadow-xs' : 'bg-red-500/15 text-red-700 dark:text-red-400 border border-red-500/20' }}">
                    {{ $tidakCount }}
                </span>
            </div>
            @if($tidakCount === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-6 italic px-3">Tidak ada hari libur / tidak hadir</p>
            @else
                <div class="p-3 divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($tidakHadirList as $index => $item)
                        <div x-show="showAll || {{ $index }} < 5"
                             class="py-2.5 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full {{ !empty($item['is_holiday']) ? 'bg-slate-400' : 'bg-red-500' }} flex-shrink-0"></span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                                <p class="{{ !empty($item['is_holiday']) ? 'text-slate-500 dark:text-slate-400' : 'text-red-600 dark:text-red-400 font-medium' }} text-[11px] mt-0.5">{{ $item['keterangan'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Tombol Lihat Semua / Ringkas: Di tengah di bawah kolom Lupa Absen dengan padding khusus --}}
    @if($masukCount > 5 || $lupaCount > 5 || $tidakCount > 5)
        <div class="mt-4 pt-3 pb-1 flex items-center justify-center">
            <div class="p-1 bg-slate-100/90 dark:bg-slate-800/90 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs inline-flex items-center">
                <button type="button" 
                        @click="showAll = !showAll"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 bg-white dark:bg-slate-900 shadow-xs hover:shadow border border-slate-200/70 dark:border-slate-700 transition-all duration-200 active:scale-95 cursor-pointer">
                    <svg x-show="!showAll" class="w-3.5 h-3.5 text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg x-show="showAll" class="w-3.5 h-3.5 text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                    </svg>
                    <span x-text="showAll ? 'Ringkas Tampilan ↑' : 'Lihat Semua ({{ $maxCount }} Hari) ↓'"></span>
                </button>
            </div>
        </div>
    @endif
</div>

