@if($sales->count() > 0)
    <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/80 border-t-2 border-blue-200 dark:border-slate-700">
        <td colspan="6" class="px-6 py-4 text-right">
            <span class="text-xs font-black text-blue-800 dark:text-blue-200 uppercase tracking-widest">Grand Total</span>
        </td>
        <td class="px-6 py-4 text-right">
            <span class="inline-flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 px-3 py-1.5 rounded-lg text-sm font-black">
                {{ number_format($totalQty, 0, ',', '.') }}
            </span>
        </td>
        <td class="px-6 py-4 text-right">
            <span class="text-base font-black text-blue-900 dark:text-white">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
        </td>
    </tr>
@endif
