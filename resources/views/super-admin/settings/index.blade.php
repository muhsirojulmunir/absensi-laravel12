@extends('layouts.master')
@section('title', 'Pengaturan Kantor')
@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="settingsHandler()">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Pengaturan Kantor</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Konfigurasi lokasi kantor pusat dan radius jangkauan absensi karyawan.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-250 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-450 px-6 py-4 rounded-xl text-sm font-semibold animate-fade-in">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Configuration Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs">
                <form action="{{ route('super-admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Koordinat Kantor</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Latitude</label>
                            <input type="text" name="office_latitude" x-model="lat" placeholder="-6.200000" required
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all font-mono">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Longitude</label>
                            <input type="text" name="office_longitude" x-model="long" placeholder="106.816666" required
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all font-mono">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Radius Absensi (Meter)</label>
                        <input type="number" name="office_radius" value="{{ $settings['office_radius'] ?? 10 }}" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-805 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all font-mono">
                        <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium pl-0.5 italic">* Default disarankan: 10 - 50 meter</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-md shadow-indigo-900/10 hover:shadow-lg active:scale-[0.98]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Paste Helper -->
            <div class="bg-indigo-50/40 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/40 rounded-2xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-indigo-100/50 dark:bg-indigo-950/50 border border-indigo-200/50 dark:border-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-205 tracking-tight">Tempel Link Google Maps</h3>
                </div>
                <p class="text-xs text-slate-550 dark:text-slate-400 leading-relaxed">
                    Salin tautan dari Google Maps (misal: <code class="bg-slate-200/50 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">https://www.google.com/maps/@-6.230756,106.81577...</code>) dan tempel di kotak input bawah ini untuk mengekstrak Latitude & Longitude secara otomatis.
                </p>
                <input type="text" @paste="handlePaste($event)" placeholder="Tempel link Google Maps di sini..." 
                       class="w-full bg-white dark:bg-slate-950 border border-indigo-200/50 dark:border-slate-800 rounded-xl py-3.5 px-4 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-450 dark:placeholder:text-slate-650 italic">
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-6 sm:p-8 h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/20 rounded-xl text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Panduan Koordinat</h2>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="flex items-start space-x-3">
                            <div class="w-5 h-5 rounded-md bg-indigo-50 dark:bg-slate-950 border border-indigo-100/50 dark:border-slate-850 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">1</div>
                            <p class="text-xs text-slate-550 dark:text-slate-400 leading-relaxed">Buka Google Maps di browser laptop atau aplikasi HP Anda.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-5 h-5 rounded-md bg-indigo-50 dark:bg-slate-950 border border-indigo-100/50 dark:border-slate-850 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">2</div>
                            <p class="text-xs text-slate-550 dark:text-slate-400 leading-relaxed">Klik kanan pada titik lokasi kantor Anda, atau ketuk & tahan jika menggunakan HP.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-5 h-5 rounded-md bg-indigo-50 dark:bg-slate-950 border border-indigo-100/50 dark:border-slate-850 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">3</div>
                            <p class="text-xs text-slate-550 dark:text-slate-400 leading-relaxed">Salin koordinat angka yang muncul atau bagikan tautan URL lokasi tersebut.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-5 h-5 rounded-md bg-indigo-50 dark:bg-slate-950 border border-indigo-100/50 dark:border-slate-850 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">4</div>
                            <p class="text-xs text-slate-550 dark:text-slate-400 leading-relaxed">Gunakan kotak "Tempel Link" di samping untuk mengekstrak data koordinat secara instan.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-850">
                    <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 rounded-xl">
                        <p class="text-[9px] text-rose-600 dark:text-rose-400 font-bold uppercase tracking-widest mb-1 flex items-center">
                            ⚠️ PENTING
                        </p>
                        <p class="text-[11px] text-rose-700/95 dark:text-rose-350 leading-relaxed">
                            Pastikan koordinat lokasi sudah akurat. Kesalahan penentuan koordinat dapat berakibat karyawan tidak bisa melakukan absensi meskipun sudah berada di kantor.
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
