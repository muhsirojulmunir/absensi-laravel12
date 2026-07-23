@extends('layouts.master')
@section('title', 'Manajemen Lokasi Counter')
@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Lokasi Counter</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola data lokasi absensi geografis untuk Karyawan Ramayana.</p>
        </div>
        <a href="{{ route('super-admin.locations.create') }}" 
           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-900/10 hover:shadow-lg active:scale-[0.98]">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
            Tambah Lokasi
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-450 rounded-xl border border-emerald-100 dark:border-emerald-900/50 flex items-start shadow-xs animate-fade-in">
        <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-xs font-semibold leading-relaxed">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-800 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-900/50 flex items-start shadow-xs animate-fade-in">
        <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
        </svg>
        <p class="text-xs font-semibold leading-relaxed">{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <!-- Search bar -->
        <div class="p-4 border-b border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-950/20">
            <form id="searchForm" action="{{ route('super-admin.locations.index') }}" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z"></path>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $query }}" placeholder="Cari nama lokasi counter atau koordinat..."
                       id="locationSearch"
                       class="block w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-950 text-xs text-slate-900 dark:text-white placeholder-slate-450 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner">
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/40 border-b border-slate-150 dark:border-slate-850 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold select-none">
                        <th class="px-6 py-4">Nama Counter</th>
                        <th class="px-6 py-4">Latitude</th>
                        <th class="px-6 py-4">Longitude</th>
                        <th class="px-6 py-4">Radius (m)</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                    @forelse($locations as $loc)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $loc->name }}</div>
                            @if($loc->google_maps_url)
                                <a href="{{ $loc->google_maps_url }}" target="_blank" 
                                   class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 inline-flex items-center gap-1 mt-1.5 transition-colors font-medium">
                                    <span>Lihat di Maps</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"></path>
                                    </svg>
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-600 dark:text-slate-450">{{ $loc->latitude }}</td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-600 dark:text-slate-450">{{ $loc->longitude }}</td>
                        <td class="px-6 py-4 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $loc->radius }} m</td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center space-x-1.5">
                                <a href="{{ route('super-admin.locations.edit', $loc->id) }}" 
                                   class="p-2 text-slate-450 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" 
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path>
                                    </svg>
                                </a>
                                <form id="delete-form-{{ $loc->id }}" action="{{ route('super-admin.locations.delete', $loc->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <button onclick="if(confirm('Apakah Anda yakin ingin menghapus lokasi &quot;{{ addslashes($loc->name) }}&quot;?')) { document.getElementById('delete-form-{{ $loc->id }}').submit(); }" 
                                        class="p-2 text-slate-455 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors" 
                                        title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9 9m12 6a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-xs text-slate-450">
                            Belum ada data lokasi counter Ramayana.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="px-6 py-4 border-t border-slate-100 dark:border-slate-850 flex items-center justify-between flex-wrap gap-4 select-none">
            <div class="text-xs text-slate-500 dark:text-slate-455">
                <span id="paginationInfo">
                    @if($locations->total() > 0)
                        Menampilkan {{ $locations->firstItem() }} - {{ $locations->lastItem() }} dari {{ $locations->total() }} data
                    @else
                        Belum ada data
                    @endif
                </span>
            </div>
            <div class="flex gap-1" id="paginationButtons">
                {{ $locations->appends(request()->query())->render() }}
            </div>
        </div>
    </div>
</div>
@endsection
