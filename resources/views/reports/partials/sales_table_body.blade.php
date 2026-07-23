@forelse($sales as $index => $sale)
    <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors group">
        <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 font-medium">{{ $index + 1 }}</td>
        <td class="px-6 py-3.5 whitespace-nowrap">
            <span class="font-semibold text-blue-900 dark:text-white">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</span>
            <span class="block text-[10px] text-blue-400 dark:text-blue-500 font-medium">{{ \Carbon\Carbon::parse($sale->date)->locale('id')->translatedFormat('l') }}</span>
        </td>
        <td class="px-6 py-3.5 whitespace-nowrap">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white text-[10px] font-bold shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($sale->user->name ?? '?', 0, 1)) }}
                </div>
                <span class="font-semibold text-blue-900 dark:text-white text-sm">{{ $sale->user->name ?? '-' }}</span>
            </div>
        </td>
        <td class="px-6 py-3.5 whitespace-nowrap">
            @if($sale->user->location)
                <span class="inline-flex items-center gap-1 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-lg text-xs font-bold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    {{ $sale->user->location->name }}
                </span>
            @else
                <span class="text-xs text-slate-400">-</span>
            @endif
        </td>
        <td class="px-6 py-3.5">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $sale->sku }}</span>
        </td>
        <td class="px-6 py-3.5">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $sale->size ?: '-' }}</span>
        </td>
        <td class="px-6 py-3.5 text-right">
            <span class="inline-flex items-center justify-center min-w-[2.5rem] bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-lg text-xs font-black">
                {{ $sale->qty }}
            </span>
        </td>
        <td class="px-6 py-3.5 text-right">
            <span class="font-bold text-blue-900 dark:text-blue-200">Rp {{ number_format($sale->nominal, 0, ',', '.') }}</span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-16 text-center">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-slate-800 dark:to-slate-700 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-sm font-bold text-blue-400 dark:text-slate-500">Belum ada data penjualan</p>
                <p class="text-xs text-blue-300 dark:text-slate-600 mt-1">Coba ubah filter periode atau counter untuk melihat data</p>
            </div>
        </td>
    </tr>
@endforelse
