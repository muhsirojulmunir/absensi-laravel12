{{--
    Panel Rincian 3 Kolom:
    1. KIRI: Masuk (Hari, Tanggal, Jam Masuk & Pulang, Status / Lupa Absen)
    2. TENGAH: Lupa Absen (Hari, Tanggal, Masuk 07.00 tidak absen pulang / sebaliknya)
    3. KANAN: Tidak Hadir / Libur (Hari, Tanggal, Keterangan)
--}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 my-3.5">

    {{-- ==================== KOLOM 1 (KIRI): MASUK ==================== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
        <div class="px-3.5 py-2.5 bg-emerald-50/80 dark:bg-emerald-950/30 border-b border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide">
                    Masuk &mdash; {{ count($masukList ?? []) }} Hari
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md bg-emerald-600 text-white shadow-xs">
                {{ count($masukList ?? []) }}
            </span>
        </div>
        <div class="p-3 max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/70">
            @if(count($masukList ?? []) === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic">Belum ada catatan masuk</p>
            @else
                @foreach($masukList as $item)
                    <div class="py-2 first:pt-0 last:pb-0 flex items-start justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 font-medium">{!! $item['jam_detail'] !!}</p>
                            @if(!empty($item['note']))
                                <p class="text-slate-400 dark:text-slate-500 text-[10px] italic mt-0.5">Catatan: {{ $item['note'] }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            @if(!empty($item['lupa_tag']))
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                                    {{ $item['lupa_tag'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ ($item['status'] ?? '') === 'Terlambat' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $item['status'] ?? 'Hadir' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ==================== KOLOM 2 (TENGAH): LUPA ABSEN ==================== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
        <div class="px-3.5 py-2.5 bg-amber-50/80 dark:bg-amber-950/30 border-b border-amber-100 dark:border-amber-900/40 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">
                    Lupa Absen &mdash; {{ count($lupaAbsenList ?? []) }} Hari
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($lupaAbsenList ?? []) > 0 ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' }}">
                {{ count($lupaAbsenList ?? []) }}
            </span>
        </div>
        <div class="p-3 max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/70">
            @if(count($lupaAbsenList ?? []) === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic">Tidak ada lupa absen ✓</p>
            @else
                @foreach($lupaAbsenList as $item)
                    <div class="py-2 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                            <p class="text-amber-700 dark:text-amber-400 text-[11px] font-medium mt-0.5">{{ $item['detail'] }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ==================== KOLOM 3 (KANAN): TIDAK HADIR / LIBUR ==================== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
        <div class="px-3.5 py-2.5 bg-rose-50/80 dark:bg-rose-950/30 border-b border-rose-100 dark:border-rose-900/40 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wide">
                    Tidak Hadir / Libur &mdash; {{ count($tidakHadirList ?? []) }} Hari
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($tidakHadirList ?? []) > 0 ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400' }}">
                {{ count($tidakHadirList ?? []) }}
            </span>
        </div>
        <div class="p-3 max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/70">
            @if(count($tidakHadirList ?? []) === 0)
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-5 italic">Tidak ada hari libur / tidak hadir</p>
            @else
                @foreach($tidakHadirList as $item)
                    <div class="py-2 first:pt-0 last:pb-0 flex items-start gap-2.5 text-xs">
                        <span class="mt-1 w-1.5 h-1.5 rounded-full {{ !empty($item['is_holiday']) ? 'bg-slate-400 dark:text-slate-500' : 'bg-rose-500' }} flex-shrink-0"></span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-slate-100 leading-tight">{{ $item['label'] }}</p>
                            <p class="{{ !empty($item['is_holiday']) ? 'text-slate-500 dark:text-slate-400' : 'text-rose-600 dark:text-rose-400 font-medium' }} text-[11px] mt-0.5">{{ $item['keterangan'] }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

</div>
