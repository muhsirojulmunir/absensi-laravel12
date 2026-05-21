@extends('layouts.master')

@section('title', 'Manajemen Hari Libur')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span>Hari Libur (Tanggal Merah)</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-[52px]">Kelola tanggal merah atau hari libur massal per divisi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-xl bg-green-50 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Form Tambah Libur --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tambah Libur</h3>
                <form action="{{ route('super-admin.holidays.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                            <input type="date" name="date" required
                                class="w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Keterangan / Nama Libur</label>
                            <input type="text" name="description" required placeholder="Misal: Idul Adha"
                                class="w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Berlaku Untuk Divisi</label>
                            <select name="division_id"
                                class="w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Semua Divisi --</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">Biarkan kosong jika libur untuk semua karyawan.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition-colors">
                            Simpan Hari Libur
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar Libur --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Keterangan</th>
                                <th class="px-6 py-4">Divisi</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                            @forelse($holidays as $holiday)
                                <tr class="bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('d F Y') }}
                                        <div class="text-xs text-gray-500 font-normal">{{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('l') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        {{ $holiday->description }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($holiday->division_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                {{ $holiday->division->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                Semua Divisi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('super-admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus hari libur ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400 p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada data hari libur.
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
