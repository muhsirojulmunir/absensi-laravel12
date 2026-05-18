@extends('layouts.master')
@section('title', 'Pengaturan Kantor')
@section('content')
<div class="space-y-8" x-data="settingsHandler()">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Pengaturan Kantor</h1>
            <p class="text-blue-500 dark:text-blue-400 mt-1">Konfigurasi lokasi kantor dan radius absensi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 px-6 py-4 rounded-2xl text-sm font-semibold animate-in fade-in slide-in-from-top-4 duration-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Configuration Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-blue-50/30 dark:bg-slate-800/50 border border-blue-100 dark:border-slate-700 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.06)]">
                <form action="{{ route('super-admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest pl-1">Latitude</label>
                            <input type="text" name="office_latitude" x-model="lat" placeholder="-6.200000" 
                                   class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl py-4 px-6 text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder:text-slate-700 dark:placeholder:text-slate-500 font-mono">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest pl-1">Longitude</label>
                            <input type="text" name="office_longitude" x-model="long" placeholder="106.816666" 
                                   class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl py-4 px-6 text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder:text-slate-700 dark:placeholder:text-slate-500 font-mono">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest pl-1">Radius Absensi (Meter)</label>
                        <input type="number" name="office_radius" value="{{ $settings['office_radius'] ?? 10 }}" 
                               class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl py-4 px-6 text-blue-950 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none transition-all font-mono">
                        <p class="text-[10px] text-blue-400 dark:text-blue-500 font-medium pl-1 italic">* Default: 10 meter</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Paste Helper -->
            <div class="bg-blue-600/5 dark:bg-blue-900/10 border border-blue-600/20 dark:border-blue-800/30 rounded-3xl p-8 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-600/10 dark:bg-blue-500/20 rounded-lg text-blue-500 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-blue-950 dark:text-white tracking-tight">Tempel Link Google Maps</h3>
                </div>
                <p class="text-xs text-blue-600/80 dark:text-blue-400/80 leading-relaxed">
                    Salin link dari Google Maps (misal: https://www.google.com/maps/@-6.230756,106.81577) dan tempel di bawah ini untuk mengisi Latitude & Longitude secara otomatis.
                </p>
                <input type="text" @paste="handlePaste($event)" placeholder="Tempel link Google Maps di sini..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 shadow-inner border border-blue-600/20 dark:border-slate-700 rounded-2xl py-4 px-6 text-sm text-blue-900 dark:text-white focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder:text-slate-700 dark:placeholder:text-slate-500 italic">
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="space-y-6">
            <div class="bg-blue-50/30 dark:bg-slate-800/50 border border-blue-100 dark:border-slate-700 rounded-3xl p-8 h-full relative overflow-hidden group">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2.5 bg-amber-600/10 dark:bg-amber-500/20 rounded-xl text-amber-500 dark:text-amber-400 border border-amber-500/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-blue-950 dark:text-white tracking-tight">Panduan Lokasi</h2>
                </div>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-slate-900 flex items-center justify-center text-[10px] font-bold text-blue-600/80 dark:text-blue-400 mt-0.5 whitespace-nowrap">01</div>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80 leading-relaxed">Buka Google Maps di browser atau aplikasi HP Anda.</p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-slate-900 flex items-center justify-center text-[10px] font-bold text-blue-600/80 dark:text-blue-400 mt-0.5 whitespace-nowrap">02</div>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80 leading-relaxed">Klik kanan pada lokasi kantor Anda atau tahan lama di aplikasi HP.</p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-slate-900 flex items-center justify-center text-[10px] font-bold text-blue-600/80 dark:text-blue-400 mt-0.5 whitespace-nowrap">03</div>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80 leading-relaxed">Salin koordinat yang muncul atau bagikan tautan lokasinya.</p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-slate-900 flex items-center justify-center text-[10px] font-bold text-blue-600/80 dark:text-blue-400 mt-0.5 whitespace-nowrap">04</div>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80 leading-relaxed">Gunakan fitur "Tempel Link" untuk mempermudah pengisian data.</p>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-blue-100 dark:border-slate-700">
                    <div class="p-4 bg-red-600/5 dark:bg-red-900/20 border border-red-600/20 dark:border-red-800/30 rounded-2xl">
                        <p class="text-[10px] text-red-400 font-bold uppercase tracking-widest mb-1 italic">Peringatan Penting</p>
                        <p class="text-xs text-red-600/80 dark:text-red-400/80 leading-relaxed">
                            Pastikan koordinat sudah tepat. Kesalahan koordinat akan menyebabkan karyawan tidak bisa melakukan absensi biarpun sudah berada di lokasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settingsHandler() {
    return {
        lat: '{{ $settings['office_latitude'] ?? "" }}',
        long: '{{ $settings['office_longitude'] ?? "" }}',

        handlePaste(event) {
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            
            // Regex to match coordinates in URL: @lat,long
            const regex = /@(-?\d+\.\d+),(-?\d+\.\d+)/;
            const match = paste.match(regex);
            
            if (match) {
                this.lat = match[1];
                this.long = match[2];
                // Optional: Show feedback
            } else {
                // Try alternate format: q=lat,long
                const regex2 = /q=(-?\d+\.\d+),(-?\d+\.\d+)/;
                const match2 = paste.match(regex2);
                if (match2) {
                    this.lat = match2[1];
                    this.long = match2[2];
                }
            }
        }
    }
}
</script>
@endsection
