@if($sales->count() > 0 && !$userId)
    <tr class="bg-gradient-to-r from-violet-50 to-fuchsia-50 dark:from-slate-800/80 dark:to-slate-800/80 border-t-2 border-violet-200 dark:border-slate-700">
        <td colspan="3" class="px-6 py-4 text-right">
            <span class="text-xs font-black text-violet-800 dark:text-violet-200 uppercase tracking-widest">Grand Total</span>
        </td>
        <td class="px-6 py-4 text-right">
            <span class="inline-flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-3 py-1.5 rounded-lg text-sm font-black">
                {{ number_format($totalQty, 0, ',', '.') }}
            </span>
        </td>
        <td class="px-6 py-4 text-right">
            <span class="text-base font-black text-violet-900 dark:text-white">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
        </td>
    </tr>
@endif
