@extends('layouts.master')
@section('title', 'Pengajuan Izin')

@section('content')
    <div class="space-y-6 md:space-y-10 animate-[fadeIn_0.5s_ease-out]" x-data="{ 
        selectedType: '{{ old('type') }}',
        activeTab: new URLSearchParams(window.location.search).has('page') ? 'riwayat' : 'pengajuan'
    }">
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
                    <div class="p-6 md:p-10 relative z-10">
                        <form action="{{ route('karyawan.leave-requests.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                <label class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-[0.2em] ml-2">Pilih
                                    Kategori <span class="text-rose-500">*</span></label>
                                <input type="hidden" name="type" x-model="selectedType" required>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                    <template x-for="item in ['Sakit', 'Izin Tidak Masuk', 'Izin Masuk Siang', 'Libur', 'Lainnya']">
                                        <button type="button" @click="selectedType = item"
                                            :class="selectedType === item ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-600/20 -translate-y-0.5' : 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-slate-800/50'"
                                            class="p-3 md:p-4 rounded-3xl border-2 transition-all duration-300 flex flex-col items-center justify-center gap-2 group">
                                            <div :class="selectedType === item ? 'bg-white/20' : 'bg-blue-50 dark:bg-slate-800 group-hover:bg-blue-100 dark:group-hover:bg-slate-700'"
                                                class="p-2 rounded-2xl transition-colors">
                                                <template x-if="item === 'Sakit'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </template>
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
                                                <template x-if="item === 'Lainnya'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
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

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label
                                            class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Mulai</label>
                                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}"
                                            value="{{ old('start_date') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Sampai</label>
                                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}"
                                            value="{{ old('end_date') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-full text-blue-900 dark:text-blue-100 px-4 py-3 text-xs font-bold transition-all shadow-sm">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block font-black text-blue-950 dark:text-blue-100 text-[10px] uppercase tracking-widest ml-2">Alasan</label>
                                    <textarea name="reason" rows="2" required placeholder="Tulis alasan singkat..."
                                        class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-50 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 rounded-[1.5rem] text-blue-950 dark:text-blue-100 px-4 py-3 text-xs font-medium resize-none shadow-sm placeholder:text-slate-400 dark:placeholder:text-slate-600">{{ old('reason') }}</textarea>
                                </div>
                            </div>

                            <div class="flex justify-center md:justify-end pt-2">
                                <button type="submit"
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
                    <form action="{{ route('karyawan.leave-requests.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-3 w-full md:w-auto">
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
                                <a href="{{ route('karyawan.leave-requests.index') }}" class="flex-1 md:flex-none bg-slate-100 hover:bg-slate-200 text-slate-500 px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 active:scale-95">
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
                                    <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-colors group">
                                        <td class="px-6 md:px-10 py-5">
                                            <div class="flex items-center space-x-4">
                                                <div
                                                    class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-slate-900 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shadow-sm border border-blue-100 dark:border-slate-700 group-hover:rotate-12 transition-transform">
                                                    @if($leave->type == 'Sakit') 🤒 @elseif($leave->type == 'Libur') 🏝️ @elseif($leave->type == 'Izin Tidak Masuk') 📝 @elseif($leave->type == 'Izin Masuk Siang') 🌅 @else ✨ @endif
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm font-black text-blue-950 dark:text-blue-100 uppercase tracking-tighter">
                                                        {{ $leave->type == 'Cuti Tahunan' ? 'Libur' : $leave->type }}</p>
                                                    <p class="text-[10px] text-blue-400 dark:text-blue-500 font-bold truncate max-w-[200px] italic">
                                                        "{{ $leave->reason }}"</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 md:px-8 py-5 whitespace-nowrap text-center">
                                            <span
                                                class="bg-slate-50 dark:bg-slate-900/50 px-3 py-1.5 rounded-lg text-[10px] md:text-xs font-black text-blue-800 dark:text-blue-200 tracking-tighter border border-slate-100 dark:border-slate-800 uppercase">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} -
                                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                            </span>
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

    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection