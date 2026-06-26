@extends('layouts.master')
@section('title', 'Manajemen Hari Libur')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center space-x-3.5">
                <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md shadow-rose-900/10 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0118 11.25v7.5m-9-6h.008v.008H9v-.008zm0 2.25h.008v.008H9v-.008zm0 2.25h.008v.008H9v-.008zm2.25-4.5h.008v.008H11.25v-.008zm0 2.25h.008v.008H11.25v-.008zm0 2.25h.008v.008H11.25v-.008zm2.25-4.5h.008v.008H13.5v-.008zm0 2.25h.008v.008H13.5v-.008zm1.5-1.5h.008v.008H15V9.75zm0 2.25h.008v.008H15v-.008zm0 2.25h.008v.008H15v-.008zm2.25-4.5h.008v.008H17.25v-.008zm0 2.25h.008v.008H17.25v-.008z"></path>
                    </svg>
                </div>
                <span class="tracking-tight">Hari Libur (Tanggal Merah)</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 ml-[54px]">Kelola tanggal merah atau hari libur massal per divisi perusahaan.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-450 rounded-xl border border-emerald-100 dark:border-emerald-900/50 shadow-xs animate-fade-in" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form Tambah Libur --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-6 space-y-5" x-data="{ startDate: '', endDate: '' }">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-850">Tambah Libur</h3>
                
                <form action="{{ route('super-admin.holidays.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Tanggal Mulai</label>
                            <input type="date" name="date" required x-model="startDate"
                                   @change="if(endDate && endDate < startDate) { endDate = startDate; }"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-3 py-2.5 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all dark:[color-scheme:dark]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Tanggal Selesai</label>
                            <input type="date" name="end_date" x-model="endDate"
                                   :min="startDate" :disabled="!startDate"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-3 py-2.5 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all disabled:opacity-40 disabled:cursor-not-allowed dark:[color-scheme:dark]">
                            <p class="text-[9px] text-slate-400 dark:text-slate-550 italic pl-0.5">* Kosongkan jika 1 hari saja</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Keterangan / Nama Libur</label>
                        <input type="text" name="description" required placeholder="Misal: Idul Adha"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Berlaku Untuk Divisi</label>
                        <select name="division_id"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-3 py-2.5 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all cursor-pointer">
                            <option value="" class="dark:bg-slate-950">-- Semua Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" class="dark:bg-slate-950">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[9px] text-slate-400 dark:text-slate-550 italic pl-0.5">* Kosongkan jika libur nasional/semua divisi</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition-all shadow-md shadow-indigo-900/10 hover:shadow-lg active:scale-[0.98] text-xs">
                            Simpan Hari Libur
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar Libur --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/40 border-b border-slate-150 dark:border-slate-850 text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider select-none">
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Keterangan</th>
                                <th class="px-6 py-4">Divisi</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('d F Y') }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-450 mt-1 font-medium">
                                            {{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('l') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                                        {{ $holiday->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($holiday->division_id)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/50">
                                                {{ $holiday->division->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-650 dark:text-emerald-450 border border-emerald-100/50 dark:border-emerald-900/50">
                                                Semua Divisi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <form action="{{ route('super-admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus hari libur ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-rose-600 hover:text-rose-900 dark:text-rose-500 dark:hover:text-rose-400 p-2 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors"
                                                    title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9 9m12 6a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-xs text-slate-450">
                                        Belum ada data hari libur yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
