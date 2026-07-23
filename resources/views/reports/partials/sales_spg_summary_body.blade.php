@if($sales->count() > 0 && !$userId)
@php
    $grouped = $sales->groupBy(function($item) {
        return $item->user_id;
    });
@endphp
@foreach($grouped as $uid => $items)
@php
    $spg = $items->first()->user;
@endphp
<tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors">
    <td class="px-6 py-3.5">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white text-[10px] font-bold shadow-sm flex-shrink-0">
                {{ strtoupper(substr($spg->name ?? '?', 0, 1)) }}
            </div>
            <span class="font-bold text-blue-900 dark:text-white">{{ $spg->name ?? '-' }}</span>
        </div>
    </td>
    <td class="px-6 py-3.5">
        @if($spg->location)
            <span class="inline-flex items-center gap-1 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                {{ $spg->location->name }}
            </span>
        @else
            <span class="text-xs text-slate-400">-</span>
        @endif
    </td>
    <td class="px-6 py-3.5 text-right font-bold text-slate-700 dark:text-slate-300">{{ $items->count() }}</td>
    <td class="px-6 py-3.5 text-right">
        <span class="inline-flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-lg text-xs font-black">
            {{ number_format($items->sum('qty'), 0, ',', '.') }}
        </span>
    </td>
    <td class="px-6 py-3.5 text-right font-black text-blue-900 dark:text-blue-200">
        Rp {{ number_format($items->sum('nominal'), 0, ',', '.') }}
    </td>
</tr>
@endforeach
@endif
