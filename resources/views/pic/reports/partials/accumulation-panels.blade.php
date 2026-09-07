{{--
    Panel Rincian Akumulasi:
    1. Libur / Tidak Hadir (Tanggal & Hari apa saja)
    2. Lupa Absen Masuk (Tanggal & Hari apa saja)
    3. Lupa Absen Pulang (Tanggal & Hari apa saja)
--}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 my-3.5">

    {{-- 1. Panel Libur & Tidak Hadir --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-rose-200/90 dark:border-rose-900/40 shadow-xs overflow-hidden">
        <div class="px-3.5 py-2.5 bg-rose-50/80 dark:bg-rose-950/40 border-b border-rose-100 dark:border-rose-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wide">
                    Libur & Tidak Hadir
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($liburDetails) > 0 ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400' }}">
                {{ count($liburDetails) }}
            </span>
        </div>
        <div class="p-3 max-h-52 overflow-y-auto">
            @if(count($liburDetails) === 0)
                <div class="py-5 text-center text-xs text-slate-400 dark:text-slate-500 font-medium">
                    Tidak ada hari libur / tidak hadir
                </div>
            @else
                <ul class="space-y-2">
                    @foreach($liburDetails as $item)
                        <li class="flex items-start justify-between gap-2 text-xs pb-1.5 border-b border-slate-100 dark:border-slate-800/80 last:border-b-0 last:pb-0">
                            <div class="flex items-start space-x-2 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 flex-shrink-0"></span>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200 leading-tight">{{ $item['label'] }}</p>
                                    <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium mt-0.5">{{ $item['reason'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- 2. Panel Lupa Absen Masuk --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-amber-200/90 dark:border-amber-900/40 shadow-xs overflow-hidden">
        <div class="px-3.5 py-2.5 bg-amber-50/80 dark:bg-amber-950/40 border-b border-amber-100 dark:border-amber-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">
                    Lupa Absen Masuk
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($lupaMasuk) > 0 ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400' }}">
                {{ count($lupaMasuk) }}
            </span>
        </div>
        <div class="p-3 max-h-52 overflow-y-auto">
            @if(count($lupaMasuk) === 0)
                <div class="py-5 text-center text-xs text-slate-400 dark:text-slate-500 font-medium">
                    Tidak ada lupa absen masuk ✓
                </div>
            @else
                <ul class="space-y-2">
                    @foreach($lupaMasuk as $item)
                        <li class="flex items-start justify-between gap-2 text-xs pb-1.5 border-b border-slate-100 dark:border-slate-800/80 last:border-b-0 last:pb-0">
                            <div class="flex items-start space-x-2 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 flex-shrink-0"></span>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200 leading-tight">{{ $item['label'] }}</p>
                                    <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-0.5">{{ $item['detail'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- 3. Panel Lupa Absen Pulang --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-orange-200/90 dark:border-orange-900/40 shadow-xs overflow-hidden">
        <div class="px-3.5 py-2.5 bg-orange-50/80 dark:bg-orange-950/40 border-b border-orange-100 dark:border-orange-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0"></span>
                <span class="text-xs font-bold text-orange-800 dark:text-orange-300 uppercase tracking-wide">
                    Lupa Absen Pulang
                </span>
            </div>
            <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-extrabold rounded-md {{ count($lupaPulang) > 0 ? 'bg-orange-600 text-white shadow-xs' : 'bg-orange-100 dark:bg-orange-900/60 text-orange-600 dark:text-orange-400' }}">
                {{ count($lupaPulang) }}
            </span>
        </div>
        <div class="p-3 max-h-52 overflow-y-auto">
            @if(count($lupaPulang) === 0)
                <div class="py-5 text-center text-xs text-slate-400 dark:text-slate-500 font-medium">
                    Tidak ada lupa absen pulang ✓
                </div>
            @else
                <ul class="space-y-2">
                    @foreach($lupaPulang as $item)
                        <li class="flex items-start justify-between gap-2 text-xs pb-1.5 border-b border-slate-100 dark:border-slate-800/80 last:border-b-0 last:pb-0">
                            <div class="flex items-start space-x-2 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-400 mt-1.5 flex-shrink-0"></span>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200 leading-tight">{{ $item['label'] }}</p>
                                    <p class="text-[11px] text-orange-600 dark:text-orange-400 font-medium mt-0.5">{{ $item['detail'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>
