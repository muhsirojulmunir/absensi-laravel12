@extends('layouts.master')
@section('title', 'PIC Dashboard')
@section('content')
<div class="space-y-8 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <div class="transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl font-extrabold text-blue-950 dark:text-white tracking-tight">Technical Lead Dashboard <span class="text-blue-500">.</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 mt-1 font-medium">Monitoring periksa kinerja divisi & manajemen cuti.</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 px-6 py-3 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 transition-transform duration-300 hover:rotate-12 hover:scale-110">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1.5">Ukuran Tim</p>
                <p class="text-sm font-extrabold text-blue-950 dark:text-blue-100 tracking-tight leading-none">{{ $stats['total_employees'] }} Anggota</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400 mb-5 shadow-sm group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-blue-400 dark:text-blue-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Karyawan</h3>
                <p class="text-4xl font-black text-blue-700 dark:text-blue-400 tracking-tighter">{{ $stats['total_employees'] }}</p>
            </div>
        </div>

        <div class="bg-amber-500 p-8 rounded-3xl shadow-[0_20px_40px_-15px_rgba(245,158,11,0.5)] relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(245,158,11,0.6)]">
             <svg class="absolute -right-10 -bottom-10 w-48 h-48 text-white/10 group-hover:translate-y-4 group-hover:rotate-6 transition-transform duration-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
             <div class="relative z-10 flex flex-col items-center text-center">
                <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl text-white mb-5 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-amber-100 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Menunggu Cuti</h3>
                <p class="text-4xl font-black text-white tracking-tighter">{{ $stats['pending_requests'] }}</p>
                <a href="{{ route(Auth::user()->role->slug . '.leave-approvals.index') }}" class="mt-5 text-[10px] font-bold text-white uppercase tracking-widest bg-white/20 px-4 py-2 rounded-xl hover:bg-white/30 transition-all">Lihat Detail</a>
             </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-50 dark:bg-emerald-900/10 rounded-full blur-3xl group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl text-emerald-600 dark:text-emerald-400 mb-5 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-blue-400 dark:text-blue-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Disetujui Hari Ini</h3>
                <p class="text-4xl font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">{{ $stats['approved_today'] }}</p>
            </div>
        </div>

        <div class="bg-orange-500 p-8 rounded-3xl shadow-[0_20px_40px_-15px_rgba(249,115,22,0.5)] relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(249,115,22,0.6)]">
             <div class="relative z-10 flex flex-col items-center text-center">
                <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl text-white mb-5 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <h3 class="text-orange-100 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Pulang Lebih Awal</h3>
                <p class="text-4xl font-black text-white tracking-tighter">{{ $stats['pulang_cepat_today'] }}</p>
                <p class="mt-5 text-[10px] font-bold text-white uppercase tracking-widest bg-white/20 px-4 py-2 rounded-xl">Hari Ini</p>
             </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <a href="{{ route(Auth::user()->role->slug . '.leave-approvals.index') }}" class="group relative bg-blue-600 rounded-3xl p-10 overflow-hidden shadow-[0_20px_40px_-15px_rgba(37,99,235,0.5)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(37,99,235,0.6)]">
             <div class="relative z-10 space-y-5">
                <h4 class="text-2xl font-extrabold text-white tracking-tight">Persetujuan Izin</h4>
                <p class="text-blue-100 font-medium text-sm leading-relaxed max-w-xs">Tinjau dan proses permintaan cuti tim Anda dengan pelacakan data presisi secara real-time.</p>
                <div class="mt-8 flex items-center space-x-2 text-white font-bold text-sm bg-white/10 w-max px-4 py-2 rounded-xl backdrop-blur-md">
                    <span>Kelola Permintaan</span>
                    <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
             </div>
        </a>

        <a href="{{ route(Auth::user()->role->slug . '.employees.index') }}" class="group relative bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-3xl p-10 overflow-hidden transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] hover:-translate-y-1 hover:border-blue-300 dark:hover:border-slate-500">
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-all duration-500"></div>
            <div class="relative z-10 space-y-5">
                <h4 class="text-2xl font-extrabold text-blue-950 dark:text-white tracking-tight">Monitoring Tim</h4>
                <p class="text-blue-600/80 dark:text-blue-400 font-medium text-sm leading-relaxed max-w-xs">Tinjauan profil tim, data kinerja divisi, dan log kehadiran masa lalu untuk analisis lengkap.</p>
                <div class="mt-8 flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-bold text-sm bg-blue-50 dark:bg-slate-700 w-max px-4 py-2 rounded-xl group-hover:bg-blue-100 dark:group-hover:bg-slate-600 transition-colors">
                    <span>Lihat Direktori Tim</span>
                    <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </div>
        </a>
    </div>
</div>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
