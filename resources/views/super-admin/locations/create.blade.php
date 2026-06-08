@extends('layouts.master')
@section('title', 'Tambah Lokasi Counter')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('super-admin.locations.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Tambah Lokasi Counter</h2>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('super-admin.locations.store') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Counter <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 transition-colors" placeholder="Contoh: Ramayana Sidoarjo">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Link Google Maps</label>
                <p class="text-xs text-slate-500 mb-2">Masukkan link lokasi Google Maps (contoh: https://maps.app.goo.gl/... atau https://www.google.com/maps/place/...). Sistem akan <strong>otomatis</strong> melacak Latitude dan Longitude dari link ini.</p>
                <input type="url" name="google_maps_url" value="{{ old('google_maps_url') }}" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 transition-colors" placeholder="https://maps.app.goo.gl/...">
                @error('google_maps_url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Atau isi koordinat secara manual (jika link Google Maps kosong):
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 transition-colors text-sm" placeholder="Contoh: -7.4478">
                        @error('latitude') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 transition-colors text-sm" placeholder="Contoh: 112.7183">
                        @error('longitude') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Radius (Meter) <span class="text-red-500">*</span></label>
                <input type="number" name="radius" value="{{ old('radius', 100) }}" required min="1" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 transition-colors" placeholder="Contoh: 100">
                @error('radius') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
                    Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Latitude and longitude are now processed in the backend. 
    // No frontend extraction logic needed.
</script>
@endpush
@endsection
