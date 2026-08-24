@extends('layouts.master')
@section('title', 'Pengajuan Izin')

@section('content')
    {{-- CATATAN: seluruh logika Alpine ditaruh di fungsi leaveFormHandler() pada blok
         <script> di bawah halaman ini, BUKAN ditulis panjang di dalam atribut x-data.
         Alasannya: teks di dalam atribut HTML tidak boleh mengandung tanda kutip ganda
         (") karena akan memutus atributnya di tengah jalan — akibatnya kode bocor
         tampil sebagai teks di halaman dan Alpine gagal jalan. --}}
    <div class="space-y-6 md:space-y-10 animate-[fadeIn_0.5s_ease-out]" x-data="leaveFormHandler()">
        <!-- Header: Bold & Minimalist -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
            <div class="space-y-1 transition-transform duration-300 group-hover:translate-x-1">
                <h1 class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Pengajuan <span
                        class="text-blue-500">Izin</span></h1>
                <p class="text-blue-600/80 dark:text-blue-400 font-medium tracking-wide">Kelola status kehadiran dan permohonan izin Anda.</p>
            </div>

            <div class="flex items-center space-x-4">
                <div
                    class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] px-6 py-3 rounded-2xl flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                    <div
                        class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-500 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 group-hover:rotate-12 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1">Status
                        </p>
                        <p class="text-sm font-bold text-blue-900 dark:text-blue-200 tracking-tight">Aktif ✨</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifikasi: Pesan baru dari Super Admin / PIC -->
        @if(isset($unreadMessages) && $unreadMessages->count() > 0)
            <div class="space-y-3">
                @foreach($unreadMessages as $msg)
                    {{-- Bisa diklik: langsung membuka tab Riwayat dan menyorot pengajuan yang dimaksud --}}
                    <a href="#leave-{{ $msg->id }}"
                       @click="activeTab = 'riwayat'; sorotPengajuan({{ $msg->id }})"
                       class="block rounded-3xl border-2 border-indigo-300 dark:border-indigo-500/70 bg-indigo-50 dark:bg-indigo-950/40 px-5 py-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:border-indigo-400 dark:hover:border-indigo-400 hover:shadow-[0_8px_30px_rgb(99,102,241,0.25)] transition-all cursor-pointer group/msg">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 shrink-0 rounded-xl bg-indigo-100 dark:bg-indigo-500/25 flex items-center justify-center text-base border border-indigo-200 dark:border-indigo-500/50">
                                💬
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-700 dark:text-indigo-200">
                                        Pesan dari Admin
                                    </p>
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[8px] font-black uppercase tracking-widest">Baru</span>
                                </div>
                                <p class="text-xs font-semibold text-indigo-950 dark:text-white leading-relaxed whitespace-pre-line break-words">
                                    {{ $msg->admin_message }}
                                </p>
                                <p class="text-[9px] font-bold text-indigo-500 dark:text-indigo-300 italic mt-2">
                                    Terkait pengajuan
                                    <span class="uppercase">{{ $msg->type }}</span>
                                    {{ \Carbon\Carbon::parse($msg->start_date)->translatedFormat('d M Y') }}
                                    @if($msg->admin_message_at)
                                        &middot; {{ $msg->admin_message_at->diffForHumans() }}
                                    @endif
                                </p>
                                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-300 mt-2 inline-flex items-center gap-1 group-hover/msg:gap-2 transition-all">
                                    Lihat pengajuan yang dimaksud
                                    <span aria-hidden="true">&rarr;</span>
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Stats Snapshot (Matching Attendance Style) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
                <p class="text-[8px] md:text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.2em]">Total</p>
                <p class="text-2xl md:text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">
                    {{ $totalCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
                <p class="text-[8px] md:text-[9px] font-black text-amber-400 uppercase tracking-[0.2em]">Pending</p>
                <p class="text-2xl md:text-3xl font-black text-amber-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">
                    {{ $pendingCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
                <p class="text-[8px] md:text-[9px] font-black text-emerald-400 uppercase tracking-[0.2em]">Disetujui</p>
                <p class="text-2xl md:text-3xl font-black text-emerald-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">
                    {{ $approvedCount }}</p>
            </div>
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
                <p class="text-[8px] md:text-[9px] font-black text-rose-400 uppercase tracking-[0.2em]">Ditolak</p>
                <p class="text-2xl md:text-3xl font-black text-rose-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">
                    {{ $rejectedCount }}</p>
            </div>
        </div>

        <!-- Choice: Pengajuan vs Riwayat -->
        <div class="flex items-center p-1 bg-blue-50 dark:bg-slate-900 rounded-3xl w-fit mx-auto shadow-sm border border-blue-100 dark:border-slate-800">
            <button @click="activeTab = 'pengajuan'" 
                    :class="activeTab === 'pengajuan' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-blue-400 hover:text-blue-600 dark:hover:text-blue-300'"
                    class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Buat Pengajuan</span>
            </button>
            <button @click="activeTab = 'riwayat'" 
                    :class="activeTab === 'riwayat' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-blue-400 hover:text-blue-600 dark:hover:text-blue-300'"
                    class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Riwayat Izin</span>
            </button>
        </div>

        @if(session('success'))
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 font-bold px-4 py-3 rounded-full flex items-center space-x-3 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-[11px] uppercase tracking-wider">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-600 font-bold px-4 py-3 rounded-2xl space-y-1 shadow-sm">
                @foreach($errors->all() as $error)
                    <p class="text-[10px] uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        <!-- Tab Content -->
        <div class="relative">
            <!-- Create Form Tab -->
            <div x-show="activeTab === 'pengajuan'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] md:rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative">
                    <div class="absolute -right-20 -top-20 w-48 h-48 bg-blue-50/30 dark:bg-blue-900/10 rounded-full blur-3xl"></div>
                        {{-- PENTING: enctype="multipart/form-data" wajib ada. Foto bukti dikirim
                             sebagai FILE UPLOAD asli (bukan teks base64 raksasa di field
                             tersembunyi) — lihat catatan lengkap di bagian JS confirmSubmit(). --}}
                        <form action="{{ Route::has(Auth::user()->role->slug . '.leave-requests.store') ? route(Auth::user()->role->slug . '.leave-requests.store') : route('karyawan.leave-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-[0.2em] ml-2">Pilih
                                    Kategori <span class="text-rose-500">*</span></label>
                                <input type="hidden" name="type" x-model="selectedType" required>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <template x-for="item in ['Izin Tidak Masuk', 'Izin Masuk Siang', 'Libur', 'Lupa Absen', 'Absen Diluar']">
                                        <button type="button" @click="selectedType = item"
                                            :class="selectedType === item ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-600/20 -translate-y-0.5' : 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-slate-800/50'"
                                            class="p-3 md:p-4 rounded-3xl border-2 transition-all duration-300 flex flex-col items-center justify-center gap-2 group">
                                            <div :class="selectedType === item ? 'bg-white/20' : 'bg-blue-50 dark:bg-slate-800 group-hover:bg-blue-100 dark:group-hover:bg-slate-700'"
                                                class="p-2 rounded-2xl transition-colors">
                                                <template x-if="item === 'Izin Tidak Masuk'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="item === 'Izin Masuk Siang'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="item === 'Libur'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9h9">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="item === 'Lupa Absen'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="item === 'Absen Diluar'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                    </svg>
                                                </template>
                                            </div>
                                            <span class="text-[9px] font-black tracking-widest uppercase italic text-center leading-tight"
                                                x-text="item"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <template x-if="selectedType === 'Izin Tidak Masuk'">
                                <div class="bg-blue-50/50 dark:bg-slate-900/50 p-5 rounded-3xl border border-blue-100 dark:border-slate-700 space-y-4">
                                    <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-[0.2em] ml-2">Kategori Izin <span class="text-rose-500">*</span></label>
                                    <input type="hidden" name="sub_type" x-model="subType" :required="selectedType === 'Izin Tidak Masuk'">
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <button type="button" @click="subType = 'Sakit'"
                                            :class="subType === 'Sakit' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Sakit</button>
                                        <button type="button" @click="subType = 'Izin Tidak Masuk'"
                                            :class="subType === 'Izin Tidak Masuk' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Izin Tidak Masuk</button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedType === 'Lupa Absen'">
                                <div class="bg-blue-50/50 dark:bg-slate-900/50 p-5 rounded-3xl border border-blue-100 dark:border-slate-700 space-y-4">
                                    {{-- Sisa jatah Lupa Absen bulan ini (gabungan Masuk + Pulang) --}}
                                    <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 {{ $lupaAbsenRemaining > 0 ? 'bg-white dark:bg-slate-800 border-blue-100 dark:border-slate-700' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-800/60' }}">
                                        <span class="text-base leading-none mt-0.5">{{ $lupaAbsenRemaining > 0 ? '🎫' : '🚫' }}</span>
                                        <div class="space-y-0.5">
                                            <p class="text-[10px] font-black uppercase tracking-widest {{ $lupaAbsenRemaining > 0 ? 'text-blue-700 dark:text-blue-300' : 'text-rose-700 dark:text-rose-300' }}">
                                                Sisa Jatah Bulan Ini: {{ $lupaAbsenRemaining }} dari {{ $lupaAbsenQuota }}
                                            </p>
                                            <p class="text-[11px] font-medium text-slate-600 dark:text-slate-300 leading-relaxed">
                                                @if($lupaAbsenRemaining > 0)
                                                    Sudah terpakai {{ $lupaAbsenUsed }}x. Jatah dihitung gabungan Absen Masuk dan Absen Pulang, dan direset otomatis tiap awal bulan.
                                                @else
                                                    Jatah bulan ini sudah habis. Silakan hubungi Admin bila ada kondisi mendesak.
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-[0.2em] ml-2">Jenis Lupa Absen <span class="text-rose-500">*</span></label>

                                    <input type="hidden" name="sub_type" x-model="subType" :required="selectedType === 'Lupa Absen'">

                                    <div class="grid grid-cols-2 gap-3">
                                        <button type="button" @click="subType = 'Absen Masuk'"
                                            :class="subType === 'Absen Masuk' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Absen Masuk</button>
                                        <button type="button" @click="subType = 'Absen Pulang'"
                                            :class="subType === 'Absen Pulang' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Absen Pulang</button>
                                    </div>
                                    <div class="space-y-1.5 mt-2" x-show="subType">
                                        <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Jam <span class="text-rose-500">*</span></label>
                                        <input type="time" name="time_start" value="{{ old('time_start') }}" :required="selectedType === 'Lupa Absen'" class="w-full md:w-1/2 bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>

                                    @if($wajibFotoLupaAbsen)
                                        {{-- ── Foto bukti WAJIB: diambil langsung dari kamera di counter ──
                                             Foto otomatis dicap tanggal, jam, dan nama counter. Galeri tidak
                                             bisa dipakai agar foto lama tidak digunakan ulang. --}}
                                        <div class="space-y-2 pt-2 border-t border-blue-100 dark:border-slate-700">
                                            <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">
                                                Foto Bukti di Counter <span class="text-rose-500">*</span>
                                            </label>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 ml-2 leading-relaxed">
                                                Ambil foto langsung di counter Anda &mdash; pastikan <span class="font-bold">wajah Anda dan area counter terlihat</span>.
                                                Bisa memakai kamera depan maupun belakang. Foto otomatis diberi cap jam, tanggal, nama counter, dan nama Anda sehingga tidak bisa dimanipulasi.
                                            </p>

                                            {{-- File asli yang benar-benar terkirim ke server (multipart).
                                                 Diisi otomatis dari hasil jepretan kamera saat form dikirim
                                                 — lihat confirmSubmit() di bagian JS. --}}
                                            <input type="file" name="photo" x-ref="photoFileInput" accept="image/*" class="hidden">
                                            {{-- Cadangan lama (base64) — dinonaktifkan saat file asli berhasil dibuat
                                                 agar tidak mengirim string base64 raksasa yang memicu 403 Forbidden di hosting. --}}
                                            <input type="hidden" name="photo_data" x-ref="photoDataInput" :value="photoData">

                                            <div x-show="!photoData" class="flex flex-col gap-2">
                                                <button type="button" @click="bukaKamera()"
                                                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-6 rounded-2xl text-[11px] uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-blue-600/20">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    Ambil Foto Sekarang
                                                </button>
                                                <p x-show="kameraError" x-text="kameraError" class="text-[10px] font-bold text-rose-500 ml-2"></p>
                                            </div>

                                            <div x-show="photoData" class="space-y-2">
                                                <div class="relative rounded-2xl overflow-hidden border-2 border-emerald-300 dark:border-emerald-700">
                                                    <img :src="photoData" alt="Foto bukti" class="w-full max-h-72 object-contain bg-slate-900">
                                                    <span class="absolute top-2 left-2 px-2 py-1 rounded-lg bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest shadow">Foto Siap</span>
                                                </div>
                                                <button type="button" @click="photoData = ''; bukaKamera()"
                                                    class="w-full bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 text-blue-600 dark:text-blue-400 font-black py-2.5 rounded-2xl text-[10px] uppercase tracking-widest transition-all active:scale-95">
                                                    Ambil Ulang Foto
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </template>

                            <template x-if="selectedType === 'Absen Diluar'">
                                <div class="bg-blue-50/50 dark:bg-slate-900/50 p-5 rounded-3xl border border-blue-100 dark:border-slate-700 space-y-4">
                                    <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-[0.2em] ml-2">Jenis Absen Diluar <span class="text-rose-500">*</span> <span class="text-blue-500 normal-case tracking-normal font-normal"> (Untuk karyawan bertugas diluar kantor) </span> </label>

                                    <input type="hidden" name="sub_type" x-model="subType" :required="selectedType === 'Absen Diluar'">

                                    <div class="grid grid-cols-2 gap-3">
                                        <button type="button" @click="subType = 'Absen Masuk'"
                                            :class="subType === 'Absen Masuk' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Absen Masuk</button>
                                        <button type="button" @click="subType = 'Absen Pulang'"
                                            :class="subType === 'Absen Pulang' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-slate-700'"
                                            class="p-3 rounded-2xl text-xs font-bold transition-all">Absen Pulang</button>
                                    </div>
                                    <div class="space-y-1.5 mt-2" x-show="subType">
                                        <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Jam <span class="text-rose-500">*</span></label>
                                        <input type="time" name="time_start" value="{{ old('time_start') }}" :required="selectedType === 'Absen Diluar'" class="w-full md:w-1/2 bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                </div>
                            </template>


                            <template x-if="selectedType === 'Izin Masuk Siang'">
                                <div class="bg-blue-50/50 dark:bg-slate-900/50 p-5 rounded-3xl border border-blue-100 dark:border-slate-700 grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Mulai Jam <span class="text-rose-500">*</span></label>
                                        <input type="time" name="time_start" value="{{ old('time_start') }}" :required="selectedType === 'Izin Masuk Siang'" class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Sampai Jam <span class="text-rose-500">*</span></label>
                                        <input type="time" name="time_end" value="{{ old('time_end') }}" :required="selectedType === 'Izin Masuk Siang'" class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label
                                            class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">
                                            Tanggal
                                            <template x-if="selectedType === 'Lupa Absen'">
                                                <span class="text-amber-500 normal-case tracking-normal font-normal text-[9px]">(pilih tanggal yang terlupa)</span>
                                            </template>
                                        </label>
                                        <input type="date" name="start_date" required
                                            :min="minStartDate"
                                            :max="maxStartDate"
                                            x-model="startDate"
                                            value="{{ old('start_date') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                    <div class="space-y-1.5" x-show="!isSingleDayType">
                                        <label
                                            class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Sampai</label>
                                        {{-- PENTING: pakai :disabled saat tipe satu-hari. x-show hanya menyembunyikan
                                             secara visual — input yang disembunyikan TETAP IKUT TERKIRIM. Input yang
                                             disabled tidak ikut terkirim, sehingga tanggal sisa dari tipe sebelumnya
                                             tidak nyasar terkirim untuk Lupa Absen / Absen Diluar. --}}
                                        <input type="date" name="end_date"
                                            x-model="endDate"
                                            :required="!isSingleDayType"
                                            :disabled="isSingleDayType"
                                            :min="minEndDate"
                                            value="{{ old('end_date') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                    {{-- Lupa Absen & Absen Diluar: end_date SELALU = start_date (kejadian 1 momen).
                                         Sisi server juga memaksa hal yang sama, ini hanya lapis tambahan di browser. --}}
                                    <template x-if="isSingleDayType">
                                        <input type="hidden" name="end_date" :value="startDate || today">
                                    </template>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Alasan</label>
                                    <textarea name="reason" rows="2" required placeholder="Tulis alasan singkat..."
                                        :readonly="selectedType === 'Libur'"
                                        x-effect="if(selectedType === 'Libur') { $el.value = 'Off day'; } else if($el.value === 'Off day') { $el.value = ''; }"
                                        class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-[1.5rem] text-blue-950 dark:text-blue-100 px-4 py-3 text-xs font-medium resize-none shadow-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:outline-none read-only:bg-slate-200 dark:read-only:bg-slate-800 read-only:text-slate-500">{{ old('reason') }}</textarea>
                                    
                                    <template x-if="selectedType === 'Izin Tidak Masuk'">
                                        <p class="text-[9px] font-bold text-rose-500 mt-2 ml-2 italic">
                                            Dilarang beralasan "Urusan Pribadi" atau "Keperluan Pribadi". Harap cantumkan alasan yang sebenarnya dan jelas.
                                        </p>
                                    </template>
                                </div>
                            </div>

                            {{-- Ringkasan durasi izin: biar karyawan langsung sadar berapa hari yang diajukan --}}
                            <template x-if="selectedType && !isSingleDayType && totalDays > 0">
                                <div class="flex items-start gap-3 rounded-2xl border px-4 py-3"
                                     :class="totalDays > 1
                                        ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/60'
                                        : 'bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800/60'">
                                    <span class="text-lg leading-none mt-0.5" x-text="totalDays > 1 ? '⚠️' : 'ℹ️'"></span>
                                    <div class="space-y-0.5">
                                        <p class="text-[11px] font-black uppercase tracking-widest"
                                           :class="totalDays > 1 ? 'text-amber-700 dark:text-amber-300' : 'text-blue-700 dark:text-blue-300'">
                                            Total <span x-text="totalDays"></span> Hari Izin
                                        </p>
                                        <p class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                            <span x-text="formatTanggal(startDate)"></span>
                                            <template x-if="totalDays > 1">
                                                <span> s/d <span x-text="formatTanggal(endDate)"></span></span>
                                            </template>
                                            <template x-if="totalDays > 1">
                                                <span class="block mt-0.5 font-bold text-amber-700 dark:text-amber-300">
                                                    Tanggal mulai dan tanggal akhir ikut terhitung sebagai hari izin.
                                                </span>
                                            </template>
                                        </p>
                                    </div>
                                </div>
                            </template>

                            {{-- Penegasan untuk tipe satu-momen: hanya berlaku 1 tanggal --}}
                            <template x-if="isSingleDayType && startDate">
                                <div class="flex items-start gap-3 rounded-2xl border border-blue-100 dark:border-blue-800/60 bg-blue-50 dark:bg-blue-900/20 px-4 py-3">
                                    <span class="text-lg leading-none mt-0.5">ℹ️</span>
                                    <div class="space-y-0.5">
                                        <p class="text-[11px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">
                                            Berlaku 1 Hari Saja
                                        </p>
                                        <p class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                            <span x-text="selectedType"></span> hanya untuk tanggal
                                            <span class="font-bold" x-text="formatTanggal(startDate)"></span>.
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <div class="flex justify-center md:justify-end pt-2">
                                <button type="submit"
                                    @click="confirmSubmit($event)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-10 rounded-full shadow-lg shadow-blue-600/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2 text-xs uppercase tracking-[0.15em]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    <span>Kirim Pengajuan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- History Table Tab -->
            <div x-show="activeTab === 'riwayat'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <!-- Filter Section -->
                <div class="mb-6 flex flex-col md:flex-row items-end justify-between gap-4 px-2">
                    <form action="{{ Route::has(Auth::user()->role->slug . '.leave-requests.index') ? route(Auth::user()->role->slug . '.leave-requests.index') : route('karyawan.leave-requests.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-3 w-full md:w-auto">
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="flex-1 md:w-44">
                                <label class="block text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest ml-2 mb-1.5">Dari Tanggal</label>
                                <input type="date" name="from" value="{{ request('from') }}" 
                                       class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl px-4 py-2.5 text-xs font-bold text-blue-900 dark:text-blue-100 focus:ring-2 focus:ring-blue-500 transition-all shadow-sm outline-none">
                            </div>
                            <div class="flex-1 md:w-44">
                                <label class="block text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest ml-2 mb-1.5">Sampai Tanggal</label>
                                <input type="date" name="to" value="{{ request('to') }}" 
                                       class="w-full bg-white dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl px-4 py-2.5 text-xs font-bold text-blue-900 dark:text-blue-100 focus:ring-2 focus:ring-blue-500 transition-all shadow-sm outline-none">
                            </div>
                        </div>
                        <div class="flex gap-2 w-full md:w-auto">
                            <button type="submit" class="flex-1 md:flex-none bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-blue-200 flex items-center justify-center gap-2 active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <span>Filter</span>
                            </button>
                            @if(request('from') || request('to'))
                                <a href="{{ Route::has(Auth::user()->role->slug . '.leave-requests.index') ? route(Auth::user()->role->slug . '.leave-requests.index') : route('karyawan.leave-requests.index') }}" class="flex-1 md:flex-none bg-slate-100 hover:bg-slate-200 text-slate-500 px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </div>
                    </form>
                    
                    <div class="hidden md:block text-right">
                        <p class="text-[9px] font-black text-blue-300 uppercase tracking-widest italic">Mode Pelacakan</p>
                        <p class="text-[10px] font-bold text-blue-500/60 uppercase">Rentang Kustom Aktif ✨</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] md:rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-blue-50/50 dark:bg-slate-900/50 border-b border-blue-100 dark:border-slate-700">
                                    <th
                                        class="px-6 md:px-10 py-5 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">
                                        Kategori & Detail</th>
                                    <th
                                        class="px-6 md:px-8 py-5 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-center">
                                        Periode</th>
                                    <th
                                        class="px-6 md:px-10 py-5 text-[9px] md:text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-right">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                                @forelse($leaveRequests as $leave)
                                    <tr id="leave-{{ $leave->id }}"
                                        :class="pengajuanDisorot === {{ $leave->id }} ? 'bg-indigo-100 dark:bg-indigo-500/20 ring-2 ring-inset ring-indigo-400 dark:ring-indigo-400' : ''"
                                        class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-colors group scroll-mt-24">
                                        <td class="px-6 md:px-10 py-5">
                                            <div class="flex items-center space-x-4">
                                                <div
                                                    class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-slate-900 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm border border-blue-100 dark:border-slate-700 group-hover:rotate-12 transition-transform">
                                                    @if($leave->type == 'Sakit' || ($leave->type == 'Izin Tidak Masuk' && $leave->sub_type == 'Sakit')) 🤒 @elseif($leave->type == 'Libur') 🏝️ @elseif($leave->type == 'Izin Tidak Masuk') 📝 @elseif($leave->type == 'Izin Masuk Siang') 🌅 @elseif($leave->type == 'Lupa Absen') ⏰ @elseif($leave->type == 'Absen Diluar') 📍 @else ✨ @endif
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm font-black text-blue-950 dark:text-blue-100 uppercase tracking-tighter">
                                                        @if($leave->type == 'Izin Tidak Masuk' && $leave->sub_type)
                                                            {{ $leave->sub_type }}
                                                        @else
                                                            {{ $leave->type == 'Cuti Tahunan' ? 'Libur' : $leave->type }}
                                                        @endif
                                                        @if(($leave->type === 'Lupa Absen' || $leave->type === 'Absen Diluar') && $leave->sub_type)
                                                            <span class="{{ $leave->type === 'Absen Diluar' ? 'text-teal-500' : 'text-blue-500' }}">({{ $leave->sub_type }})</span>
                                                        @endif
                                                    </p>
                                                    @if(($leave->type === 'Lupa Absen' || $leave->type === 'Absen Diluar') && $leave->time_start)
                                                        <p class="text-[10px] font-bold {{ $leave->type === 'Absen Diluar' ? 'text-teal-500 dark:text-teal-400' : 'text-rose-500 dark:text-rose-400' }} mt-0.5">Jam: {{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }}</p>
                                                    @elseif($leave->type === 'Izin Masuk Siang' && $leave->time_start && $leave->time_end)
                                                        <p class="text-[10px] font-bold text-amber-500 dark:text-amber-400 mt-0.5">Jam: {{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($leave->time_end)->format('H:i') }}</p>
                                                    @endif
                                                    <p class="text-[10px] text-blue-400 dark:text-blue-500 font-bold truncate max-w-[200px] italic mt-1">
                                                        "{{ $leave->reason }}"</p>

                                                    {{-- Pesan dari Super Admin / PIC (tanpa menu baru, tampil langsung di sini) --}}
                                                    @if($leave->admin_message)
                                                        <div class="mt-2 max-w-[260px] rounded-xl border border-indigo-300 dark:border-indigo-500/60 bg-indigo-50 dark:bg-indigo-950/50 px-3 py-2">
                                                            <div class="flex items-center gap-1.5 mb-1">
                                                                <span class="text-[11px] leading-none">💬</span>
                                                                <span class="text-[8px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-200">
                                                                    Pesan dari Admin
                                                                </span>
                                                                @if(is_null($leave->admin_message_read_at))
                                                                    <span class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[7px] font-black uppercase tracking-wider">Baru</span>
                                                                @endif
                                                            </div>
                                                            <p class="text-[10px] font-semibold text-indigo-900 dark:text-white leading-relaxed whitespace-pre-line break-words">{{ $leave->admin_message }}</p>
                                                            @if($leave->admin_message_at)
                                                                <p class="text-[8px] font-bold text-indigo-500 dark:text-indigo-300 italic mt-1">
                                                                    {{ $leave->admin_message_at->diffForHumans() }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 md:px-8 py-5 whitespace-nowrap text-center">
                                            <span
                                                class="bg-slate-50 dark:bg-slate-900/50 px-3 py-1.5 rounded-lg text-[10px] md:text-xs font-black text-blue-800 dark:text-blue-200 tracking-tighter border border-slate-100 dark:border-slate-800 uppercase">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M') }} -
                                                {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                                            </span>
                                            {{-- Durasi izin (inklusif): 13–14 = 2 hari --}}
                                            <p class="text-[9px] font-black uppercase tracking-widest mt-1.5 {{ $leave->total_days > 1 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">
                                                {{ $leave->total_days }} Hari
                                            </p>
                                        </td>
                                        <td class="px-6 md:px-10 py-5 whitespace-nowrap text-right">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/30',
                                                    'approved' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30',
                                                    'rejected' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/30',
                                                ];
                                                $colorClass = $statusColors[$leave->status] ?? 'bg-slate-50 dark:bg-slate-900/20 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-800';
                                            @endphp
                                            <span
                                                class="px-3 md:px-5 py-2 border {{ $colorClass }} text-[8px] md:text-[10px] font-black uppercase rounded-full tracking-widest shadow-sm">
                                                {{ $leave->status }}
                                            </span>
                                            <p class="text-[8px] font-bold text-slate-300 mt-2 italic">
                                                {{ $leave->created_at->diffForHumans() }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="px-6 md:px-10 py-20 md:py-32 text-center text-blue-400 font-black uppercase tracking-[0.3em] text-[10px] md:text-xs opacity-50 italic text-pretty">
                                            Belum ada riwayat permohonan. ✨</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-blue-50/30 dark:bg-slate-900/50 px-6 md:px-10 py-5 border-t border-blue-50 dark:border-slate-700">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <p class="text-[9px] md:text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic order-2 md:order-1">Total: {{ $totalCount }} Data</p>
                            <div class="order-1 md:order-2">
                                {{ $leaveRequests->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Modal Kamera untuk foto bukti Lupa Absen ────────────────────────
             Memakai kamera perangkat secara langsung (bukan galeri), lalu hasil
             jepretan dicap tanggal, jam, dan nama counter sebelum dikirim. --}}
        @if($wajibFotoLupaAbsen ?? false)
        <div x-show="kameraTerbuka" x-cloak class="fixed inset-0 z-[300] bg-black/90 flex flex-col" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between px-5 py-4 text-white flex-shrink-0">
                <div>
                    <p class="text-sm font-black uppercase tracking-widest">Ambil Foto Bukti</p>
                    <p class="text-[10px] text-white/60 font-medium">Pastikan wajah Anda dan area counter terlihat</p>
                </div>
                <button type="button" @click="tutupKamera()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Kamera bermasalah: tampilkan panduan yang jelas + tombol aksi,
                 BUKAN cuma teks merah kecil tanpa solusi. --}}
            <template x-if="kameraErrorJenis">
                <div class="flex-1 flex flex-col items-center justify-center px-6 py-8 text-center overflow-y-auto">
                    <div class="w-16 h-16 rounded-full bg-rose-500/15 border-2 border-rose-400/40 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <template x-if="kameraErrorJenis === 'ditolak'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM3 3l18 18"/>
                            </template>
                            <template x-if="kameraErrorJenis !== 'ditolak'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </template>
                        </svg>
                    </div>

                    <p class="text-sm font-black text-white uppercase tracking-widest mb-2">
                        <span x-show="kameraErrorJenis === 'ditolak'">Izin Kamera Diblokir</span>
                        <span x-show="kameraErrorJenis === 'tidak-aman'">Koneksi Belum Aman</span>
                        <span x-show="kameraErrorJenis === 'tidak-ada'">Kamera Tidak Ditemukan</span>
                        <span x-show="kameraErrorJenis === 'dipakai-lain'">Kamera Sedang Dipakai</span>
                        <span x-show="kameraErrorJenis === 'tidak-didukung'">Browser Tidak Mendukung</span>
                    </p>
                    <p class="text-[12px] text-white/70 font-medium max-w-sm leading-relaxed" x-text="kameraError"></p>

                    {{-- Langkah manual — hanya relevan kalau izinnya benar-benar diblokir --}}
                    <template x-if="kameraErrorJenis === 'ditolak'">
                        <div class="mt-5 w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl p-4 text-left space-y-2.5">
                            <p class="text-[10px] font-black text-white/80 uppercase tracking-widest mb-1">Cara Mengizinkan di Chrome:</p>
                            <p class="text-[11px] text-white/70 leading-relaxed flex gap-2"><span class="font-black text-white/90">1.</span> Tekan ikon 🔒 / ⓘ di sebelah alamat situs (di atas layar).</p>
                            <p class="text-[11px] text-white/70 leading-relaxed flex gap-2"><span class="font-black text-white/90">2.</span> Pilih <span class="font-bold text-white">"Izin"</span> / "Permissions", cari <span class="font-bold text-white">Kamera</span>.</p>
                            <p class="text-[11px] text-white/70 leading-relaxed flex gap-2"><span class="font-black text-white/90">3.</span> Ubah jadi <span class="font-bold text-emerald-300">"Izinkan"</span> / Allow.</p>
                            <p class="text-[11px] text-white/70 leading-relaxed flex gap-2"><span class="font-black text-white/90">4.</span> Kembali ke sini, tekan <span class="font-bold text-white">"Muat Ulang Halaman"</span> di bawah.</p>
                            <p class="text-[10px] text-amber-300/90 font-bold pt-1 border-t border-white/10 mt-2">
                                Sudah pernah mengizinkan tapi masih tertolak? Wajib tekan "Muat Ulang Halaman" — perubahan izin baru berlaku setelah halaman dimuat ulang.
                            </p>
                        </div>
                    </template>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3 w-full max-w-sm">
                        <template x-if="kameraErrorJenis !== 'tidak-aman' && kameraErrorJenis !== 'tidak-didukung'">
                            <button type="button" @click="mulaiStream()"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-2xl text-[11px] uppercase tracking-widest transition-all active:scale-95">
                                Coba Lagi
                            </button>
                        </template>
                        <button type="button" @click="window.location.reload()"
                            class="flex-1 bg-white/10 hover:bg-white/20 text-white font-black py-3 rounded-2xl text-[11px] uppercase tracking-widest transition-all active:scale-95">
                            Muat Ulang Halaman
                        </button>
                    </div>
                </div>
            </template>

            {{-- Tampilan kamera normal (tidak ada error) --}}
            <template x-if="!kameraErrorJenis">
                <div class="flex-1 flex items-center justify-center overflow-hidden px-3">
                    {{-- Pratinjau kamera depan dibuat seperti cermin agar terasa natural.
                         Foto yang TERSIMPAN tetap tidak dicerminkan supaya tulisan di
                         latar (papan nama counter, dll) tetap terbaca normal. --}}
                    <video x-ref="video" autoplay playsinline muted
                           :style="arahKamera === 'user' ? 'transform: scaleX(-1)' : ''"
                           class="max-h-full max-w-full rounded-2xl bg-black"></video>
                </div>
            </template>

            <template x-if="!kameraErrorJenis">
                <div class="px-5 py-6 flex-shrink-0 flex flex-col items-center gap-3">
                    <div class="flex items-center justify-center gap-8">
                        {{-- Tombol ganti kamera depan / belakang --}}
                        <button type="button" @click="gantiKamera()"
                            class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors active:scale-95"
                            :title="'Ganti ke ' + (arahKamera === 'user' ? 'kamera belakang' : 'kamera depan')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>

                        <button type="button" @click="jepretFoto()" :disabled="!kameraSiap"
                            :class="kameraSiap ? 'bg-white hover:bg-slate-100 active:scale-95' : 'bg-white/30 cursor-not-allowed'"
                            class="w-16 h-16 rounded-full border-4 border-white/50 flex items-center justify-center transition-all">
                            <span class="w-11 h-11 rounded-full bg-blue-600"></span>
                        </button>

                        {{-- Penyeimbang agar tombol jepret tetap di tengah --}}
                        <span class="w-12 h-12"></span>
                    </div>

                    <p class="text-[10px] text-white/60 font-bold uppercase tracking-widest">
                        <span x-text="labelKamera"></span> &middot; Tekan lingkaran untuk memotret
                    </p>
                </div>
            </template>
        </div>
        @endif

    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        function leaveFormHandler() {
            return {
                selectedType: @json(old('type', '')),
                activeTab: new URLSearchParams(window.location.search).has('page') ? 'riwayat' : 'pengajuan',
                subType: @json(old('sub_type', '')),
                today: @json(date('Y-m-d')),
                startOfMonth: @json(date('Y-m-01')),
                isStaffKantor: @json(Auth::user()->role->slug === 'karyawan'),
                startDate: @json(old('start_date', '')),
                endDate: @json(old('end_date', '')),

                // ID pengajuan yang sedang disorot (setelah pesan admin diklik)
                pengajuanDisorot: null,

                // ── Kamera untuk foto bukti "Lupa Absen" ──────────────────────
                wajibFoto: @json($wajibFotoLupaAbsen ?? false),
                namaCounter: @json(Auth::user()->location->name ?? (Auth::user()->division->name ?? '-')),
                namaKaryawan: @json(Auth::user()->name),
                photoData: '',
                kameraTerbuka: false,
                kameraSiap: false,
                kameraError: '',
                // Jenis masalah kamera, dipakai untuk menampilkan panduan & tombol yang
                // tepat sesuai penyebabnya (bukan cuma teks merah tanpa solusi):
                // 'ditolak' | 'tidak-aman' | 'tidak-ada' | 'dipakai-lain' | 'tidak-didukung' | ''
                kameraErrorJenis: '',
                _stream: null,

                // Kamera mana yang sedang dipakai: 'user' = depan (selfie bersama counter),
                // 'environment' = belakang. Karyawan bisa berganti sesuai kebutuhan, karena
                // foto harus memperlihatkan counter DAN karyawannya.
                arahKamera: 'user',
                get labelKamera() { return this.arahKamera === 'user' ? 'Kamera Depan' : 'Kamera Belakang'; },

                async bukaKamera() {
                    this.kameraError = '';
                    this.kameraErrorJenis = '';
                    this.kameraSiap = false;

                    // Kamera HANYA bisa diakses lewat HTTPS (atau localhost). Ini sering
                    // jadi sumber kebingungan: karyawan sudah mengizinkan kamera di
                    // pengaturan Chrome, tapi tetap ditolak karena alamat situsnya http://
                    // biasa — pengaturan izin Chrome tidak berlaku untuk koneksi tidak aman.
                    if (!window.isSecureContext) {
                        this.kameraTerbuka = true;
                        this.kameraErrorJenis = 'tidak-aman';
                        this.kameraError = 'Situs ini belum diakses lewat HTTPS, jadi kamera tidak bisa dibuka sama sekali — izin di pengaturan Chrome pun tidak akan berpengaruh. Mohon buka situs ini dengan alamat yang diawali "https://".';
                        return;
                    }

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.kameraTerbuka = true;
                        this.kameraErrorJenis = 'tidak-didukung';
                        this.kameraError = 'Perangkat/browser ini tidak mendukung kamera. Silakan buka lewat browser HP Anda (disarankan Chrome versi terbaru).';
                        return;
                    }

                    this.kameraTerbuka = true;
                    await this.mulaiStream();
                },

                async mulaiStream() {
                    // Hentikan stream lama dulu agar kamera tidak bentrok saat berganti arah
                    if (this._stream) {
                        this._stream.getTracks().forEach(t => t.stop());
                        this._stream = null;
                    }
                    this.kameraSiap = false;
                    this.kameraError = '';
                    this.kameraErrorJenis = '';

                    try {
                        this._stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: { ideal: this.arahKamera },
                                width: { ideal: 1280 },
                                height: { ideal: 960 }
                            },
                            audio: false
                        });
                        const video = this.$refs.video;
                        video.srcObject = this._stream;
                        await video.play();
                        this.kameraSiap = true;
                        this.kameraError = '';
                        this.kameraErrorJenis = '';
                    } catch (e) {
                        this.kameraSiap = false;
                        if (e && (e.name === 'NotAllowedError' || e.name === 'SecurityError')) {
                            this.kameraErrorJenis = 'ditolak';
                            this.kameraError = 'Izin kamera untuk situs ini masih diblokir di browser Anda.';
                        } else if (e && e.name === 'NotFoundError') {
                            this.kameraErrorJenis = 'tidak-ada';
                            this.kameraError = 'Kamera tidak ditemukan pada perangkat ini.';
                        } else if (e && (e.name === 'NotReadableError' || e.name === 'TrackStartError')) {
                            this.kameraErrorJenis = 'dipakai-lain';
                            this.kameraError = 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera/video call lain, lalu coba lagi.';
                        } else if (e && e.name === 'OverconstrainedError') {
                            this.kameraErrorJenis = 'ditolak';
                            this.kameraError = 'Kamera ' + this.labelKamera.toLowerCase() + ' tidak tersedia. Coba ganti ke kamera satunya di bawah.';
                        } else {
                            this.kameraErrorJenis = 'ditolak';
                            this.kameraError = 'Kamera gagal dibuka. Coba tekan "Coba Lagi" di bawah, atau muat ulang halaman ini.';
                        }
                    }
                },

                async gantiKamera() {
                    this.arahKamera = this.arahKamera === 'user' ? 'environment' : 'user';
                    await this.mulaiStream();
                },

                tutupKamera() {
                    if (this._stream) {
                        this._stream.getTracks().forEach(t => t.stop());
                        this._stream = null;
                    }
                    this.kameraSiap = false;
                    this.kameraTerbuka = false;
                },

                /**
                 * Ambil gambar dari kamera lalu CAP tanggal, jam, dan nama counter
                 * langsung ke gambarnya, supaya bukti tidak bisa dipakai ulang.
                 */
                jepretFoto() {
                    const video = this.$refs.video;
                    if (!video || !video.videoWidth) return;

                    // Batasi lebar maksimum gambar sebelum dikompres. Foto bukti absensi tidak perlu
                    // resolusi tinggi — 800px sudah sangat jelas terbaca dan ukuran berkas sangat ringan
                    // (±50-90 KB) sehingga aman dari batas upload dan proteksi firewall WAF hosting.
                    const LEBAR_MAKSIMAL = 800;
                    const rasio = video.videoWidth > LEBAR_MAKSIMAL ? LEBAR_MAKSIMAL / video.videoWidth : 1;

                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(video.videoWidth * rasio);
                    canvas.height = Math.round(video.videoHeight * rasio);
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // ── Cap waktu, lokasi & nama karyawan ──
                    // Dicap langsung ke piksel gambar (bukan metadata), sehingga tidak
                    // bisa dihapus tanpa merusak fotonya. Server juga mencatat waktu
                    // simpannya sendiri sebagai pembanding.
                    const now = new Date();
                    const tanggal = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    const jam = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                    const skala = canvas.width / 1000;
                    const pad = Math.round(18 * skala);
                    const fontBesar = Math.max(14, Math.round(30 * skala));
                    const fontKecil = Math.max(11, Math.round(22 * skala));
                    const tinggiBar = fontBesar + fontKecil * 3 + pad * 3.2;

                    // Gradasi gelap agar teks tetap terbaca di latar terang maupun gelap
                    const grad = ctx.createLinearGradient(0, canvas.height - tinggiBar, 0, canvas.height);
                    grad.addColorStop(0, 'rgba(0,0,0,0.30)');
                    grad.addColorStop(1, 'rgba(0,0,0,0.82)');
                    ctx.fillStyle = grad;
                    ctx.fillRect(0, canvas.height - tinggiBar, canvas.width, tinggiBar);

                    ctx.textBaseline = 'top';
                    ctx.shadowColor = 'rgba(0,0,0,0.9)';
                    ctx.shadowBlur = Math.round(4 * skala);
                    let y = canvas.height - tinggiBar + pad;

                    ctx.fillStyle = '#ffffff';
                    ctx.font = '700 ' + fontBesar + 'px sans-serif';
                    ctx.fillText(jam + ' WIB', pad, y);
                    y += fontBesar + Math.round(pad / 2);

                    ctx.fillStyle = '#e2e8f0';
                    ctx.font = '600 ' + fontKecil + 'px sans-serif';
                    ctx.fillText(tanggal, pad, y);
                    y += fontKecil + Math.round(pad / 4);

                    ctx.fillStyle = '#93c5fd';
                    ctx.font = '700 ' + fontKecil + 'px sans-serif';
                    ctx.fillText('📍 ' + this.namaCounter, pad, y);
                    y += fontKecil + Math.round(pad / 4);

                    ctx.fillStyle = '#fcd34d';
                    ctx.font = '700 ' + fontKecil + 'px sans-serif';
                    ctx.fillText(this.namaKaryawan, pad, y);

                    ctx.shadowBlur = 0;

                    // Kualitas 0.55 pada lebar 800px menghasilkan file ±50-90 KB —
                    // sangat tajam dan ringan, mencegah blokir 403 oleh WAF/ModSecurity hosting.
                    this.photoData = canvas.toDataURL('image/jpeg', 0.55);
                    this.tutupKamera();
                },

                /**
                 * Dipanggil saat karyawan mengklik pesan dari admin.
                 * Pindah ke tab Riwayat, lalu gulir + sorot baris pengajuan
                 * yang dimaksud supaya karyawan langsung tahu mana yang salah.
                 */
                sorotPengajuan(id) {
                    this.pengajuanDisorot = id;
                    this.$nextTick(() => {
                        const baris = document.getElementById('leave-' + id);
                        if (baris) {
                            baris.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                    // Hapus sorotan setelah beberapa detik supaya tidak mengganggu
                    setTimeout(() => {
                        if (this.pengajuanDisorot === id) this.pengajuanDisorot = null;
                    }, 5000);
                },

                get minStartDate() {
                    if (this.selectedType === 'Lupa Absen') return this.startOfMonth;
                    return this.isStaffKantor ? this.startOfMonth : this.today;
                },
                get maxStartDate() {
                    return this.selectedType === 'Lupa Absen' ? this.today : '';
                },
                get minEndDate() {
                    if (this.startDate) return this.startDate;
                    return this.isStaffKantor ? this.startOfMonth : this.today;
                },

                // Tipe yang sifatnya SATU MOMEN (satu tanggal + satu jam), bukan rentang tanggal
                get isSingleDayType() {
                    return this.selectedType === 'Lupa Absen' || this.selectedType === 'Absen Diluar';
                },

                // Hitung jumlah hari izin (inklusif). Contoh: 13 s/d 14 = 2 hari
                get totalDays() {
                    if (this.isSingleDayType) return 1;
                    if (!this.startDate || !this.endDate) return 0;
                    const a = new Date(this.startDate + 'T00:00:00');
                    const b = new Date(this.endDate + 'T00:00:00');
                    if (isNaN(a) || isNaN(b) || b < a) return 0;
                    return Math.round((b - a) / 86400000) + 1;
                },

                // Label tanggal yang enak dibaca, mis. 13 Agustus 2026
                formatTanggal(value) {
                    if (!value) return '-';
                    const d = new Date(value + 'T00:00:00');
                    if (isNaN(d)) return value;
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                /**
                 * Ubah data URL hasil jepretan kamera (base64) menjadi objek File asli.
                 *
                 * PENTING soal kenapa ini perlu: sebelumnya foto dikirim lewat field
                 * tersembunyi <input type="hidden"> berisi TEKS base64 raksasa (bisa
                 * >1MB teks) di dalam form biasa (bukan multipart). Banyak server hosting
                 * memasang proteksi keamanan (mod_security/WAF) yang mencurigai body
                 * request non-file berukuran besar seperti itu sebagai serangan, dan
                 * langsung MEMBLOKIR permintaannya dengan status 403 — sebelum request
                 * itu sempat sampai ke aplikasi Laravel sama sekali (makanya tidak ada
                 * jejaknya di log error).
                 *
                 * Solusinya: kirim foto sebagai FILE UPLOAD ASLI (multipart/form-data),
                 * persis seperti upload foto profil atau upload Excel — jalur ini jauh
                 * lebih dipercaya oleh proteksi keamanan hosting dan lebih hemat ukuran.
                 */
                dataUrlToFile(dataUrl, namaFile) {
                    const bagian = dataUrl.split(',');
                    const mime = (bagian[0].match(/:(.*?);/) || [])[1] || 'image/jpeg';
                    const biner = atob(bagian[1]);
                    const bytes = new Uint8Array(biner.length);
                    for (let i = 0; i < biner.length; i++) bytes[i] = biner.charCodeAt(i);
                    try {
                        return new File([bytes], namaFile, { type: mime });
                    } catch (e) {
                        const blob = new Blob([bytes], { type: mime });
                        blob.lastModifiedDate = new Date();
                        blob.name = namaFile;
                        return blob;
                    }
                },

                // Konfirmasi sebelum kirim, supaya karyawan benar-benar paham berapa hari
                // izin yang diajukan dan tidak salah pilih tanggal.
                confirmSubmit(event) {
                    if (!this.selectedType) return true;

                    // Foto bukti wajib untuk Lupa Absen (khusus Karyawan Ramayana)
                    if (this.selectedType === 'Lupa Absen' && this.wajibFoto && !this.photoData) {
                        window.alert('Foto bukti di counter wajib diambil terlebih dahulu.\n\nTekan tombol "Ambil Foto Sekarang" pada bagian Lupa Absen.');
                        event.preventDefault();
                        return false;
                    }

                    // Pasang foto sebagai file upload ASLI ke input file (lihat catatan
                    // lengkap di dataUrlToFile()) — inilah yang benar-benar mencegah 403.
                    if (this.photoData && this.$refs.photoFileInput) {
                        try {
                            const file = this.dataUrlToFile(this.photoData, 'bukti-lupa-absen.jpg');
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            this.$refs.photoFileInput.files = dt.files;

                            // PENTING: Karena file upload multipart asli sudah terpasang,
                            // nonaktifkan input `photo_data` agar browser TIDAK mengirimkan
                            // string base64 raksasa di body POST yang memicu pemblokiran 403 Forbidden
                            // oleh ModSecurity/WAF pada hosting.
                            if (this.$refs.photoDataInput) {
                                this.$refs.photoDataInput.disabled = true;
                                this.$refs.photoDataInput.value = '';
                            }
                        } catch (e) {
                            // Kalau browser lawas dan tidak mendukung DataTransfer,
                            // biarkan photo_data tetap aktif sebagai jalur cadangan base64.
                            console.warn('Gagal membuat file upload dari foto, memakai jalur cadangan base64.', e);
                        }
                    }

                    const jenis = this.selectedType + (this.subType ? ' (' + this.subType + ')' : '');
                    let pesan;

                    if (this.isSingleDayType) {
                        pesan =
                            'KONFIRMASI PENGAJUAN\n\n' +
                            'Jenis   : ' + jenis + '\n' +
                            'Tanggal : ' + this.formatTanggal(this.startDate) + '\n\n' +
                            'Pengajuan ini berlaku untuk 1 HARI saja (tanggal di atas).\n\n' +
                            'Sudah benar? Tekan OK untuk mengirim.';
                    } else {
                        const hari = this.totalDays;

                        if (hari === 0) {
                            window.alert('Tanggal belum lengkap, atau tanggal akhir lebih awal dari tanggal mulai. Mohon periksa kembali.');
                            event.preventDefault();
                            return false;
                        }

                        pesan =
                            'KONFIRMASI PENGAJUAN\n\n' +
                            'Jenis  : ' + jenis + '\n' +
                            'Dari   : ' + this.formatTanggal(this.startDate) + '\n' +
                            'Sampai : ' + this.formatTanggal(this.endDate) + '\n' +
                            'Total  : ' + hari + ' HARI izin\n\n' +
                            (hari > 1
                                ? 'PERHATIAN: Anda mengajukan izin selama ' + hari + ' hari penuh (tanggal mulai dan tanggal akhir ikut terhitung).\n\n'
                                : 'Pengajuan ini berlaku untuk 1 hari saja.\n\n') +
                            'Sudah benar? Tekan OK untuk mengirim.';
                    }

                    if (!window.confirm(pesan)) {
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            };
        }
    </script>
@endsection