@extends('layouts.master')
@section('title', 'Manajemen Stok Ramayana')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Manajemen Stok Ramayana</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau dan Kelola Stok Real-time Counter</p>
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show" x-transition.duration.500ms
        class="p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-start shadow-sm">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
        class="p-4 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-xl border border-red-200 dark:border-red-800 flex items-start shadow-sm">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Filter: Search + Tanggal -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 md:p-6 shadow-sm mb-6" x-data="{ search: '' }">
        <div class="flex flex-col sm:flex-row items-end gap-3 mb-4">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pencarian Counter / SPG</label>
                <input type="text" x-model="search" placeholder="Ketik nama counter atau SPG..." class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors font-medium">
            </div>
            <div class="w-full sm:w-auto">
                <form action="{{ route('pic_ramayana.ramayana-stocks.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Tanggal
                        </label>
                        <input type="date" name="date" value="{{ $filterDate }}" onchange="this.form.submit()" class="w-full sm:w-48 bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors font-medium cursor-pointer [color-scheme:light_dark]">
                    </div>
                </form>
            </div>
        </div>

        <!-- Info tanggal yang aktif -->
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Menampilkan data stok per tanggal: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($filterDate)->translatedFormat('d F Y') }}</span>
            @if($filterDate !== \Carbon\Carbon::today()->toDateString())
                <a href="{{ route('pic_ramayana.ramayana-stocks.index') }}" class="ml-2 text-blue-500 hover:text-blue-700 font-semibold underline">Reset ke Hari Ini</a>
            @endif
        </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <div class="rounded-2xl p-6 text-white shadow-lg flex items-center justify-between" style="background: linear-gradient(135deg, #2563eb, #4f46e5);">
            <div>
                <p class="text-xs font-black uppercase tracking-widest mb-1 opacity-80 text-blue-100">Total Counter</p>
                <p class="text-3xl font-black drop-shadow-md">{{ count($counterStats) }} <span class="text-lg opacity-80 font-medium">Counter</span></p>
            </div>
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>
        
        <div class="rounded-2xl p-6 text-white shadow-lg flex items-center justify-between" style="background: linear-gradient(135deg, #c026d3, #7e22ce); box-shadow: 0 10px 15px -3px rgba(126, 34, 206, 0.3);">
            <div>
                <p class="text-xs font-black uppercase tracking-widest mb-1 opacity-80 text-fuchsia-100">Total Keseluruhan Stok</p>
                <p class="text-3xl font-black drop-shadow-md">{{ number_format($totalOverallStock, 0, ',', '.') }} <span class="text-lg opacity-80 font-medium">ITEMS</span></p>
            </div>
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
            </div>
        </div>
    </div>

    <!-- Data Table inside the x-data scope -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mt-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Nama SPG</th>
                        <th class="p-4">Counter</th>
                        <th class="p-4 text-center">Jumlah SKU</th>
                        <th class="p-4 text-center">Total Stok</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @php $no = 1; @endphp
                    @forelse($counterStats as $counter)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors" 
                        x-show="search === '' || '{{ strtolower($counter['spg_name'] . ' ' . $counter['location']) }}'.includes(search.toLowerCase())">
                        <td class="p-4 text-sm font-medium text-slate-400 text-center">{{ $no++ }}</td>
                        <td class="p-4">
                            <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $counter['spg_name'] }}</div>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                                {{ $counter['location'] }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold border border-slate-200 dark:border-slate-700">
                                {{ $counter['total_sku'] }} SKU
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-[64px] px-3 py-1.5 rounded-lg text-sm font-black
                                @if($counter['total_stock'] < 0) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @elseif($counter['total_stock'] == 0) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                @else bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400
                                @endif">
                                {{ $counter['total_stock'] }} ITEMS
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('pic_ramayana.ramayana-stocks.show', $counter['user_id']) }}?date={{ $filterDate }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-800/50 rounded-xl text-xs font-bold transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-slate-300 dark:text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-slate-500 dark:text-slate-400 font-medium">Belum ada Karyawan Ramayana.</p>
                                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Tambahkan akun Karyawan terlebih dahulu.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div> <!-- Close the x-data div -->
</div>
@endsection
