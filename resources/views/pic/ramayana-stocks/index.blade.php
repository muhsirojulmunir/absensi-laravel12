@extends('layouts.master')
@section('title', 'Manajemen Stok Ramayana')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" x-data="{ showImportModal: false }">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Manajemen Stok Ramayana</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau dan Kelola Stok Real-time Counter</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showImportModal = true" class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Import Excel
            </button>
        </div>

        <!-- Import Modal -->
        <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showImportModal" x-transition.opacity class="fixed inset-0 bg-slate-900/75 transition-opacity" aria-hidden="true" @click="showImportModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showImportModal" x-transition.scale.origin.bottom class="relative z-10 inline-block align-bottom bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200 dark:border-slate-800">
                    <form action="{{ route('pic_ramayana.ramayana-stocks.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white dark:bg-slate-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">Import Data Stok Internal</h3>
                                    <div class="mt-2 text-left">
                                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">1. Pilih Counter Tujuan</label>
                                        <select name="import_location_id" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-3 transition-colors font-medium mb-4">
                                            <option value="">-- Pilih Counter Ramayana --</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                            @endforeach
                                        </select>

                                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">2. Mode Import / Jenis Transaksi</label>
                                        <div class="space-y-2 mb-4">
                                            <label class="flex items-start p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 transition-colors">
                                                <input type="radio" name="import_mode" value="add" checked class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                                <div class="ml-3">
                                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">➕ Tambah Stok (Barang Datang)</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menambahkan kuantitas barang dari Excel ke stok yang sudah ada. Barang belum ada akan otomatis dibuat.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-start p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-red-500 transition-colors">
                                                <input type="radio" name="import_mode" value="replace" class="mt-1 text-red-600 focus:ring-red-500">
                                                <div class="ml-3">
                                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">🔄 Ganti / Timpa Total Stok</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menghapus stok lama di toko ini dan menggantikannya secara menyeluruh dengan data Excel baru.</span>
                                                </div>
                                            </label>
                                        </div>

                                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">3. Upload File Excel (.xlsx)</label>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Upload langsung file export dari aplikasi internal (JAYA MANDIRI). Pastikan sudah di-Save As ke format <strong>.xlsx</strong>.</p>
                                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <input type="file" name="file" id="file" accept=".xlsx, .xls, .ods, .csv" required class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 cursor-pointer">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200 dark:border-slate-800">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Upload & Import
                            </button>
                            <button type="button" @click="showImportModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 dark:border-slate-700 shadow-sm px-4 py-2.5 bg-white dark:bg-slate-900 text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <th class="p-4 text-center">Terakhir Import Excel</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @php $no = 1; @endphp
                    @forelse($counterStats as $counter)
                    @php
                        $counterUser = \App\Models\User::find($counter['user_id']);
                        $counterLocId = $counterUser ? ($counterUser->location_id ?: $counterUser->id) : null;
                        $lastImportRaw = $counterLocId ? ($importTimestamps[$counterLocId] ?? null) : null;
                        $lastImportStr = $lastImportRaw
                            ? \Carbon\Carbon::parse($lastImportRaw)->translatedFormat('d M Y, H:i')
                            : null;
                    @endphp
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
                                @if($counter['total_stock'] == 0) bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                @else bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400
                                @endif">
                                {{ abs($counter['total_stock']) }} ITEMS
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($lastImportStr)
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        {{ $lastImportStr }}
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">Belum pernah import</span>
                            @endif
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
                        <td colspan="7" class="p-12 text-center">
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
