@php
    $medal = ['🥇', '🥈', '🥉'];
    $rankColors = [
        ['bg' => 'from-yellow-400 to-amber-500',   'text' => 'text-amber-700',  'badge' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',  'bar' => 'bg-gradient-to-r from-yellow-400 to-amber-500'],
        ['bg' => 'from-slate-400 to-slate-500',    'text' => 'text-slate-600',  'badge' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',      'bar' => 'bg-gradient-to-r from-slate-400 to-slate-500'],
        ['bg' => 'from-orange-400 to-amber-600',   'text' => 'text-orange-700', 'badge' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300','bar' => 'bg-gradient-to-r from-orange-400 to-amber-600'],
    ];
    $defaultColor = ['bg' => 'from-violet-500 to-fuchsia-500', 'text' => 'text-violet-600', 'badge' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300', 'bar' => 'bg-gradient-to-r from-violet-500 to-fuchsia-500'];
@endphp

@forelse($spgRanking as $index => $item)
@php
    $rank  = $index + 1;
    $spg   = $item['user'];
    $color = $rank <= 3 ? $rankColors[$rank - 1] : $defaultColor;
    $pct   = $maxNominal > 0 ? round(($item['total_nominal'] / $maxNominal) * 100) : 0;
    $hasTrx = $item['total_trx'] > 0;
@endphp
<tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors group ranking-row" data-spg-id="{{ $spg->id }}">
    {{-- Rank --}}
    <td class="px-4 py-3.5 text-center">
        @if($rank <= 3)
            <div class="flex flex-col items-center gap-0.5">
                <span class="text-2xl leading-none">{{ $medal[$rank - 1] }}</span>
                <span class="text-[9px] font-black uppercase tracking-wider {{ $color['text'] }}">Rank {{ $rank }}</span>
            </div>
        @else
            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto">
                <span class="text-xs font-black text-slate-500 dark:text-slate-400">{{ $rank }}</span>
            </div>
        @endif
    </td>

    {{-- SPG Info --}}
    <td class="px-4 py-3.5">
        <div class="flex items-center gap-3">
            <div class="relative flex-shrink-0">
                @if($spg->avatar)
                    <img src="{{ asset('storage/' . $spg->avatar) }}" class="w-10 h-10 rounded-xl object-cover border-2 border-white dark:border-slate-700 shadow-sm">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center text-white font-black text-sm shadow-sm border-2 border-white dark:border-slate-700">
                        {{ strtoupper(substr($spg->name ?? '?', 0, 1)) }}
                    </div>
                @endif
                @if($rank <= 3)
                    <span class="absolute -bottom-1 -right-1 text-sm">{{ $medal[$rank-1] }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <p class="font-bold text-blue-900 dark:text-white text-sm truncate">{{ $spg->name ?? '-' }}</p>
                @if($spg->location)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold {{ $color['badge'] }} px-2 py-0.5 rounded-md mt-0.5">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $spg->location->name }}
                    </span>
                @else
                    <span class="text-[10px] text-slate-400">-</span>
                @endif
            </div>
        </div>
    </td>

    {{-- Transaksi --}}
    <td class="px-4 py-3.5 text-center">
        <span class="text-sm font-bold {{ $hasTrx ? 'text-blue-900 dark:text-blue-200' : 'text-slate-400 dark:text-slate-500' }}">
            {{ number_format($item['total_trx'], 0, ',', '.') }}
        </span>
        <span class="block text-[9px] text-slate-400 font-medium">transaksi</span>
    </td>

    {{-- Qty --}}
    <td class="px-4 py-3.5 text-center">
        @if($hasTrx)
            <span class="inline-flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-lg text-sm font-black">
                {{ number_format($item['total_qty'], 0, ',', '.') }}
            </span>
            <span class="block text-[9px] text-slate-400 font-medium mt-0.5">psg</span>
        @else
            <span class="text-slate-300 dark:text-slate-600 text-sm font-bold">—</span>
        @endif
    </td>

    {{-- Nominal + Progress Bar --}}
    <td class="px-4 py-3.5 min-w-[180px]">
        <div class="space-y-1.5">
            <span class="font-black text-sm {{ $hasTrx ? 'text-blue-900 dark:text-blue-100' : 'text-slate-400 dark:text-slate-500' }}">
                {{ $hasTrx ? 'Rp ' . number_format($item['total_nominal'], 0, ',', '.') : '—' }}
            </span>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $hasTrx ? $color['bar'] : 'bg-slate-200 dark:bg-slate-600' }} transition-all duration-700 ease-out"
                     style="width: {{ $pct }}%">
                </div>
            </div>
            <span class="text-[9px] font-bold text-slate-400">{{ $pct }}% dari tertinggi</span>
        </div>
    </td>

    {{-- Aksi --}}
    <td class="px-4 py-3.5 text-center">
        @if($hasTrx)
            <button type="button"
                    onclick="openSpgDetailModal({{ $spg->id }}, '{{ addslashes($spg->name) }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-600 hover:bg-violet-500 text-white text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm shadow-violet-600/20 transition-all hover:scale-105 active:scale-95">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Detail
            </button>
        @else
            <span class="text-[10px] text-slate-300 dark:text-slate-600 font-medium">Belum ada</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-16 h-16 bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-slate-800 dark:to-slate-700 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-violet-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <p class="text-sm font-bold text-slate-400 dark:text-slate-500">Tidak ada data SPG</p>
        </div>
    </td>
</tr>
@endforelse
