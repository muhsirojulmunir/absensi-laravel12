@extends('layouts.master')
@section('title', 'PIC Dashboard')
@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ Auth::user()->role->name }} Dashboard <span class="text-blue-500">.</span></h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Monitoring kinerja divisi, kehadiran, &amp; manajemen cuti tim.</p>
        </div>
        <div class="inline-flex items-center space-x-3.5 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 px-5 py-3 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <div class="bg-blue-50 dark:bg-blue-950/30 p-2.5 rounded-xl text-blue-600 dark:text-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Ukuran Tim</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 tracking-tight leading-none">{{ $stats['total_employees'] }} Anggota</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Anggota Aktif -->
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="text-[9px] font-bold text-slate-450 uppercase tracking-wider">Divisi</span>
            </div>
            <div>
                <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Anggota Tim</h3>
                <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $stats['total_employees'] }}</p>
            </div>
        </div>

        <!-- Card 2: Menunggu Cuti (Link) -->
        <a href="{{ route(Auth::user()->role->slug . '.leave-approvals.index') }}" class="group bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm hover:border-amber-500/30 dark:hover:border-amber-500/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
             <div class="flex items-center justify-between mb-4">
                 <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl text-amber-600 dark:text-amber-400">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </div>
                 @if($stats['pending_requests'] > 0)
                 <span class="relative flex h-2 w-2">
                     <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                     <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                 </span>
                 @endif
             </div>
             <div>
                 <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Menunggu Cuti</h3>
                 <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-2">{{ $stats['pending_requests'] }}</p>
                 <span class="text-[9px] text-amber-600 dark:text-amber-450 font-bold uppercase tracking-wider flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                     Tinjau Cuti &rarr;
                 </span>
             </div>
        </a>

        <!-- Card 3: Disetujui Hari Ini -->
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-[9px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest">Hari Ini</span>
            </div>
            <div>
                <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Disetujui Hari Ini</h3>
                <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $stats['approved_today'] }}</p>
            </div>
        </div>

        <!-- Card 4: Pulang Lebih Awal -->
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between min-h-[160px]">
             <div class="flex items-center justify-between mb-4">
                 <div class="p-2.5 bg-orange-50 dark:bg-orange-950/30 rounded-xl text-orange-600 dark:text-orange-450">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                 </div>
                 <span class="text-[9px] font-bold text-orange-500 dark:text-orange-400 uppercase tracking-widest">Sistem</span>
             </div>
             <div>
                 <h3 class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Pulang Cepat</h3>
                 <p class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $stats['pulang_cepat_today'] }}</p>
             </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route(Auth::user()->role->slug . '.leave-approvals.index') }}" class="group bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-8 hover:border-blue-500/30 dark:hover:border-blue-500/30 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
             <div class="space-y-4">
                 <div class="p-3 bg-blue-50 dark:bg-blue-950/30 rounded-2xl text-blue-600 dark:text-blue-400 inline-block">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                 </div>
                 <h4 class="text-lg font-bold text-slate-800 dark:text-white tracking-tight">Persetujuan Izin</h4>
                 <p class="text-slate-500 dark:text-slate-400 text-xs font-medium leading-relaxed">Tinjau, setujui, atau tolak permintaan cuti dari anggota tim Anda secara cepat dan terdokumentasi.</p>
                 <div class="pt-2 flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-bold text-xs">
                     <span>Kelola Permintaan</span>
                     <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                 </div>
             </div>
        </a>

        <a href="{{ route(Auth::user()->role->slug . '.employees.index') }}" class="group bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-8 hover:border-blue-500/30 dark:hover:border-blue-500/30 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-4">
                 <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 rounded-2xl text-indigo-600 dark:text-indigo-400 inline-block">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                 </div>
                 <h4 class="text-lg font-bold text-slate-800 dark:text-white tracking-tight">Monitoring Tim</h4>
                 <p class="text-slate-500 dark:text-slate-400 text-xs font-medium leading-relaxed">Pantau aktivitas kehadiran harian, statistik ketepatan waktu, dan data log lengkap anggota tim Anda.</p>
                 <div class="pt-2 flex items-center space-x-2 text-indigo-600 dark:text-indigo-455 font-bold text-xs">
                     <span>Lihat Direktori Tim</span>
                     <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                 </div>
            </div>
        </a>
    </div>
</div>
@endsection
