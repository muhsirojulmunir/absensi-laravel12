@extends('layouts.master')
@section('title', 'Manajemen Lokasi Counter')
@section('content')
<div class="space-y-6" x-data="{ search: '{{ $query }}' }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Lokasi Counter</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data lokasi absensi untuk Karyawan Ramayana.</p>
        </div>
        <a href="{{ route('super-admin.locations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Lokasi
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
            <form action="{{ route('super-admin.locations.index') }}" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400 transition-colors" id="searchIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input
                    type="text"
                    name="q"
                    x-model="search"
                    placeholder="Ketik untuk mencari lokasi counter..."
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl leading-5 bg-white dark:bg-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out shadow-sm dark:text-white"
                >
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                        <th class="p-4">Nama Counter</th>
                        <th class="p-4">Latitude</th>
                        <th class="p-4">Longitude</th>
                        <th class="p-4">Radius (m)</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($locations as $loc)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors" x-show="search === '' || '{{ strtolower(addslashes($loc->name) . ' ' . $loc->latitude . ' ' . $loc->longitude) }}'.includes(search.toLowerCase())">
                        <td class="p-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $loc->name }}</div>
                            @if($loc->google_maps_url)
                                <a href="{{ $loc->google_maps_url }}" target="_blank" class="text-xs text-blue-500 hover:underline mt-1 inline-block">Lihat di Maps</a>
                            @endif
                        </td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">{{ $loc->latitude }}</td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">{{ $loc->longitude }}</td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400 font-medium">{{ $loc->radius }}</td>
                        <td class="p-4 flex items-center justify-end space-x-2">
                            <a href="{{ route('super-admin.locations.edit', $loc->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                            <button onclick="deleteLocation({{ $loc->id }}, '{{ addslashes($loc->name) }}')" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                            Belum ada data lokasi counter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <span id="paginationInfo">
                    @if($locations->total() > 0)
                        Menampilkan {{ $locations->firstItem() }} - {{ $locations->lastItem() }} dari {{ $locations->total() }} data
                    @else
                        Belum ada data
                    @endif
                </span>
            </div>
            <div class="flex gap-2 flex-wrap" id="paginationButtons">
                {{ $locations->appends(request()->query())->render() }}
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function deleteLocation(locId, locName) {
    if (!confirm(`Apakah Anda yakin ingin menghapus lokasi "${locName}"?`)) {
        return;
    }

    fetch(`/super-admin/locations/${locId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Gagal menghapus lokasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus');
    });
}
</script>
@endsection
