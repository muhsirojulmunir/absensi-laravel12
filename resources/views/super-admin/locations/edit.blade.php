@extends('layouts.master')
@section('title', 'Edit Lokasi Counter')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('super-admin.locations.index') }}" 
           class="p-2.5 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Lokasi Counter</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui detail lokasi counter Ramayana dan radius jangkauan.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('super-admin.locations.update', $location->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Counter Name -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">
                    Nama Counter <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $location->name) }}" required 
                       class="w-full bg-slate-50 dark:bg-slate-950 border @error('name') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:bg-white dark:focus:bg-slate-900">
                @error('name') <p class="text-xs text-rose-500 mt-1.5 pl-0.5">{{ $message }}</p> @enderror
            </div>

            <!-- Google Maps Link -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Link Google Maps</label>
                <p class="text-[11px] text-slate-500 dark:text-slate-450 leading-relaxed pl-0.5">
                    Masukkan link lokasi Google Maps (contoh: <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-550">https://maps.app.goo.gl/...</code>). Sistem akan <strong class="text-slate-700 dark:text-slate-300">melacak koordinat secara otomatis</strong> saat form disimpan di backend.
                </p>
                <input type="url" name="google_maps_url" value="{{ old('google_maps_url', $location->google_maps_url) }}" 
                       class="w-full bg-slate-50 dark:bg-slate-950 border @error('google_maps_url') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-800 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all placeholder-slate-450 focus:bg-white dark:focus:bg-slate-900">
                @error('google_maps_url') <p class="text-xs text-rose-500 mt-1.5 pl-0.5">{{ $message }}</p> @enderror
            </div>

            <!-- Manual Coordinates -->
            <div class="p-5 bg-indigo-50/40 dark:bg-indigo-950/10 border border-indigo-100/50 dark:border-indigo-900/40 rounded-xl space-y-4">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider flex items-center pl-0.5">
                    <svg class="w-4 h-4 mr-1.5 shrink-0 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                    </svg>
                    Atau Perbarui Koordinat Secara Manual
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-350 pl-0.5">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $location->latitude) }}" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-mono">
                        @error('latitude') <p class="text-xs text-rose-500 mt-1 pl-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-350 pl-0.5">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $location->longitude) }}" 
                               class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-mono">
                        @error('longitude') <p class="text-xs text-rose-500 mt-1 pl-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Radius -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">
                    Radius Absensi (Meter) <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="radius" value="{{ old('radius', $location->radius) }}" required min="1" 
                       class="w-full bg-slate-50 dark:bg-slate-950 border @error('radius') border-rose-500 focus:ring-rose-500 @else border-slate-200 dark:border-slate-805 focus:ring-indigo-500 focus:border-indigo-500 @enderror rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:bg-white dark:focus:bg-slate-900 font-mono">
                @error('radius') <p class="text-xs text-rose-500 mt-1.5 pl-0.5">{{ $message }}</p> @enderror
            </div>

            <!-- Actions Footer -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end items-center space-x-3">
                <a href="{{ route('super-admin.locations.index') }}" 
                   class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-900/10 hover:shadow-lg transition-all active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
