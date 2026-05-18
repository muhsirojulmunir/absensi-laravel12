@extends('layouts.master')
@section('title', 'Detail Karyawan - ' . $user->name)
@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-20 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header / Back Button -->
    <div class="flex items-center justify-between group">
        <a href="{{ route('pic.employees.index') }}" class="inline-flex items-center space-x-2 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-all group/back">
            <div class="p-2 bg-blue-50 dark:bg-slate-800 rounded-xl group-hover/back:-translate-x-1 transition-transform border border-blue-100 dark:border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            <span class="text-[11px] font-black uppercase tracking-[0.2em] italic">Kembali ke Tim</span>
        </a>
    </div>

    <!-- Profile Identity Header -->
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] p-8 md:p-10 shadow-[0_15px_50px_rgba(0,0,0,0.03)] relative overflow-hidden group">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-blue-50/50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8">
            <!-- Large Avatar -->
            <div class="w-32 h-40 md:w-36 md:h-48 bg-slate-50 dark:bg-slate-900 border-4 border-white dark:border-slate-800 shadow-2xl rounded-3xl overflow-hidden flex items-center justify-center text-blue-900 dark:text-blue-100 text-5xl font-black group-hover:scale-105 transition-transform duration-500 font-mono">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                @else
                    <span>{{ substr($user->name, 0, 1) }}</span>
                @endif
            </div>
            
            <div class="flex-1 text-center md:text-left space-y-4">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.3em] font-mono leading-none">Profil Anggota Tim</p>
                    <h1 class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic leading-tight">{{ $user->name }}</h1>
                    <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-900/50">{{ $user->employee_id }}</span>
                        <span class="w-1.5 h-1.5 bg-blue-100 dark:bg-slate-700 rounded-full hidden md:block"></span>
                        <span class="text-sm font-bold text-blue-400 dark:text-blue-500 capitalize">{{ $user->position ?? 'Professional Staff' }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-blue-50 dark:border-slate-700">
                    <div class="flex items-center gap-3 text-blue-950/70 dark:text-blue-200/70">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-slate-900 flex items-center justify-center text-blue-500 dark:text-blue-400 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1a1 1 0 011 1v5m-5 8V7a2 2 0 012-2h7a2 2 0 012 2v11m-3-3h.01M9 17h.01M9 13h.01M13 17h.01M13 13h.01M17 17h.01M17 13h.01"></path></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-tight">{{ $user->division->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-blue-950/70 dark:text-blue-200/70">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-500 dark:text-emerald-400 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-tight">{{ $user->role->name }} Member</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Section: Biodata Pribadi -->
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-8">
            <div class="flex items-center space-x-3">
                <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                <h2 class="text-xs font-black text-blue-950 dark:text-white uppercase tracking-[0.2em] italic">Informasi Pribadi</h2>
            </div>
            
            <div class="space-y-6">
                <div class="space-y-1.5">
                    <p class="text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic leading-none">Email Akses</p>
                    <p class="text-sm font-bold text-blue-950 dark:text-blue-100 break-all">{{ $user->email }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <p class="text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic leading-none">Nomer Telepon</p>
                        <p class="text-sm font-bold text-blue-950 dark:text-blue-100">{{ $user->phone ?? 'Belum Diisi' }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <p class="text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic leading-none">Tempat, Tanggal Lahir</p>
                        <p class="text-sm font-bold text-blue-950 dark:text-blue-100">
                            @if($user->birth_place || $user->birth_date)
                                {{ $user->birth_place ?? '-' }}, {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '-' }}
                            @else
                                Belum Diisi
                            @endif
                        </p>
                    </div>
                </div>
                <div class="space-y-2.5 pt-4 border-t border-blue-50 dark:border-slate-700">
                    <p class="text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic leading-none">Alamat Tempat Tinggal</p>
                    <p class="text-xs font-medium text-blue-900 dark:text-blue-200 leading-relaxed">{{ $user->address ?? 'Alamat lengkap belum dicantumkan oleh karyawan.' }}</p>
                </div>
            </div>
        </div>

        <!-- Section: Kontak Darurat -->
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-8">
            <div class="flex items-center space-x-3">
                <span class="w-1.5 h-6 bg-amber-500 rounded-full"></span>
                <h2 class="text-xs font-black text-blue-950 dark:text-white uppercase tracking-[0.2em] italic">Kontak Darurat</h2>
            </div>
            
            <div class="space-y-6">
                @if($user->emergency_name)
                    <div class="space-y-1.5">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest italic leading-none">Nama Kontak</p>
                        <p class="text-sm font-bold text-blue-950 dark:text-blue-100">{{ $user->emergency_name }}</p>
                    </div>
                    <div class="flex gap-8">
                        <div class="space-y-1.5">
                            <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest italic leading-none">Nomer HP</p>
                            <p class="text-sm font-bold text-blue-950 dark:text-blue-100">{{ $user->emergency_phone }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest italic leading-none">Hubungan</p>
                            <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg text-[9px] font-black uppercase tracking-tight border border-amber-100 dark:border-amber-900/50">{{ $user->emergency_relation }}</span>
                        </div>
                    </div>
                @else
                    <div class="py-10 text-center space-y-3 opacity-30 dark:opacity-20">
                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 leading-tight">Data Kontak Darurat<br>Belum Dilengkapi</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Footer Quick Glance -->
    <div class="bg-blue-950 rounded-[2.5rem] p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8 text-white relative overflow-hidden group">
        <div class="absolute inset-0 bg-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative z-10 flex items-center space-x-6">
            <div class="w-16 h-16 bg-white/10 rounded-3xl flex items-center justify-center border border-white/20">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <h4 class="text-xl font-black uppercase tracking-tighter italic">Quick Performance</h4>
                <p class="text-blue-200/60 text-xs font-medium tracking-wide">Ringkasan singkat aktivitas karyawan.</p>
            </div>
        </div>
        <div class="relative z-10 flex gap-10">
            <div class="text-center">
                <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1 leading-none">Hadir Bulan Ini</p>
                <p class="text-3xl font-black font-mono leading-none">{{ $user->attendances()->whereMonth('date', now()->month)->count() }}</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1 leading-none">Izin Terpakai</p>
                <p class="text-3xl font-black font-mono leading-none">{{ $user->leaveRequests()->where('status', 'approved')->whereMonth('created_at', now()->month)->count() }}</p>
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
