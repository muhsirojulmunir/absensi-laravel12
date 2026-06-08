@extends('layouts.master')
@section('title', 'Persetujuan Izin')

@section('content')
@php
    $destroyRoute = auth()->user()->role->slug === 'super-admin' ? 'super-admin.leave-approvals.destroy' : 'pic.leave-approvals.destroy';
@endphp
<div class="max-w-5xl mx-auto space-y-8 pb-20 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <div class="space-y-1 transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Persetujuan <span class="text-blue-500">Izin</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 font-medium tracking-wide">Tinjau dan kelola permohonan izin divisi Anda.</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] px-6 py-3 rounded-2xl flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-500 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 italic font-black text-xs">PIC</div>
                <div>
                    <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1">Otoritas</p>
                    <p class="text-sm font-bold text-blue-900 dark:text-blue-200 tracking-tight">Manager Kehadiran ✨</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats Summary (Divisional Overview) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white dark:bg-slate-800 border border-blue-100/50 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.2em]">Total (Bulan Ini)</p>
            <p class="text-2xl md:text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $totalCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100/50 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-amber-400 uppercase tracking-[0.2em]">Pending</p>
            <p class="text-2xl md:text-3xl font-black text-amber-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $pendingCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100/50 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-emerald-400 uppercase tracking-[0.2em]">Disetujui</p>
            <p class="text-2xl md:text-3xl font-black text-emerald-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $approvedCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-blue-100/50 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 md:p-6 rounded-3xl space-y-2 hover:-translate-y-1 transition-transform group">
            <p class="text-[8px] md:text-[9px] font-black text-rose-400 uppercase tracking-[0.2em]">Ditolak</p>
            <p class="text-2xl md:text-3xl font-black text-rose-500 tracking-tighter group-hover:scale-110 origin-left transition-transform font-mono">{{ $rejectedCount }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 font-bold px-4 py-3 rounded-full flex items-center space-x-3 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-[11px] uppercase tracking-wider">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-100 text-rose-600 font-bold px-4 py-3 rounded-full flex items-center space-x-3 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span class="text-[11px] uppercase tracking-wider">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] p-4 flex flex-col xl:flex-row items-center justify-between gap-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-8 transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
        <div class="flex items-center gap-2 overflow-x-auto w-full xl:w-auto pb-2 xl:pb-0 scrollbar-hide">
            @php $currentStatus = request('status', 'pending'); @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $currentStatus == 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">
                Menunggu <span class="bg-white/20 px-2 py-0.5 rounded-full ml-1">{{ $pendingCount }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}" class="px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $currentStatus == 'approved' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">Disetujui</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" class="px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $currentStatus == 'rejected' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/30' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">Ditolak</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap {{ $currentStatus == 'all' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">Semua</a>
        </div>
        
        <form action="{{ route('pic.leave-approvals.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto" x-data="{ dateFilter: '{{ request('date_filter', 'all') }}' }">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="relative w-full sm:w-48">
                <select name="date_filter" x-model="dateFilter" onchange="if(this.value !== 'custom') this.form.submit()" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 text-blue-900 dark:text-white px-4 py-3 rounded-2xl text-[11px] font-bold uppercase tracking-widest focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer shadow-sm">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="custom">Pilih Tanggal...</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-blue-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            
            <template x-if="dateFilter === 'custom'">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto animate-[fadeIn_0.3s_ease-out]">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" required class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 text-blue-900 dark:text-white px-4 py-3 rounded-2xl text-[11px] font-bold uppercase tracking-widest focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm">
                    <span class="text-slate-400 font-bold hidden sm:block">-</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" required class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 text-blue-900 dark:text-white px-4 py-3 rounded-2xl text-[11px] font-bold uppercase tracking-widest focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm">
                    <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white p-3 rounded-2xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/30 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </template>
        </form>
    </div>

    <!-- Approvals Table -->
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all relative mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 border-b border-blue-50 dark:border-slate-700/50">
                        <th class="px-6 py-5 text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest whitespace-nowrap">Karyawan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest whitespace-nowrap">Pengajuan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest whitespace-nowrap w-64">Alasan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-700/50">
                    @forelse($leaveRequests as $leave)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-5 align-top">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-slate-700 border-2 border-white dark:border-slate-600 shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                    @if($leave->user->avatar)
                                        <img src="{{ asset('storage/' . $leave->user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-lg font-black text-blue-500 dark:text-blue-400 uppercase">{{ substr($leave->user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-sm font-black text-blue-950 dark:text-white uppercase tracking-tight leading-none">{{ $leave->user->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[9px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-md border border-blue-100 dark:border-blue-800/50">{{ $leave->user->employee_id ?? 'N/A' }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ $leave->user->division->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top">
                            <div class="space-y-2">
                                <div>
                                    @if($leave->type === 'Lupa Absen')
                                        <span class="bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-[9px] font-black px-2 py-1 rounded-lg border border-rose-100 dark:border-rose-800/50 uppercase tracking-widest whitespace-nowrap">
                                            ⏰ Lupa Absen: {{ $leave->sub_type ?? '-' }}
                                        </span>
                                    @elseif($leave->type === 'Absen Diluar')
                                        <span class="bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-[9px] font-black px-2 py-1 rounded-lg border border-teal-100 dark:border-teal-800/50 uppercase tracking-widest whitespace-nowrap">
                                            📍 Absen Diluar: {{ $leave->sub_type ?? '-' }}
                                        </span>
                                    @elseif($leave->type === 'Izin Masuk Siang')
                                        <span class="bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[9px] font-black px-2 py-1 rounded-lg border border-amber-100 dark:border-amber-800/50 uppercase tracking-widest whitespace-nowrap">
                                            🌅 Masuk Siang
                                        </span>
                                    @else
                                        <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[9px] font-black px-2 py-1 rounded-lg border border-blue-100 dark:border-blue-800/50 uppercase tracking-widest whitespace-nowrap">
                                            @if($leave->type == 'Sakit') 🤒 @elseif($leave->type == 'Libur' || $leave->type == 'Cuti Tahunan') 🏝️ @elseif($leave->type == 'Izin Tidak Masuk') 📝 @endif
                                            {{ $leave->type }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-bold text-[10px] uppercase tracking-tight">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="whitespace-nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} — {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</span>
                                </div>
                                @if(in_array($leave->type, ['Lupa Absen', 'Absen Diluar']) && $leave->time_start)
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Jam: <span class="text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }}</span>
                                    </div>
                                @elseif($leave->type === 'Izin Masuk Siang' && $leave->time_start && $leave->time_end)
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Jam: <span class="text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($leave->time_end)->format('H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top">
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 font-medium leading-relaxed max-w-xs break-words">"{{ $leave->reason }}"</p>
                        </td>
                        <td class="px-6 py-5 align-top">
                            @if($leave->status === 'pending')
                                <div class="flex flex-col gap-1.5">
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30 rounded-lg text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 w-max shadow-sm animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> PENDING
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold italic">{{ $leave->created_at->diffForHumans() }}</span>
                                </div>
                            @elseif($leave->status === 'approved')
                                <div class="flex flex-col gap-1.5">
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 rounded-lg text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 w-max shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> DISETUJUI
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold italic">Oleh PIC</span>
                                </div>
                            @else
                                <div class="flex flex-col gap-1.5">
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-lg text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 w-max shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg> DITOLAK
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold italic">Oleh PIC</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top text-right">
                            @if($leave->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('pic.leave-approvals.approve', $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" onclick="return confirm('Setujui pengajuan ini?')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-blue-600/20 flex items-center gap-1.5 active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="hidden xl:inline">Setujui</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('pic.leave-approvals.reject', $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" onclick="return confirm('Tolak pengajuan ini?')" class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 border border-rose-200 dark:border-rose-800/50 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm flex items-center gap-1.5 active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span class="hidden xl:inline">Tolak</span>
                                        </button>
                                    </form>
                                    @if(auth()->user()->role->slug === 'super-admin')
                                    <form action="{{ route($destroyRoute, $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin menghapus pengajuan ini secara permanen?')" class="bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm flex items-center gap-1.5 active:scale-95" title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->role->slug === 'super-admin')
                                    <form action="{{ route($destroyRoute, $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus riwayat pengajuan ini?')" class="bg-slate-50 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-900/20 text-slate-400 hover:text-rose-600 border border-slate-200 dark:border-slate-700 dark:hover:border-rose-800/50 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-1.5 active:scale-95" title="Hapus Riwayat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <span class="hidden xl:inline">Hapus</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-24 text-center">
                            <div class="w-20 h-20 bg-blue-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner border border-blue-100 dark:border-slate-800">
                                <svg class="w-10 h-10 text-blue-300 dark:text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m4 6h4"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Data Kosong</h3>
                            <p class="text-blue-400 dark:text-blue-500 font-medium max-w-sm mx-auto mt-2 text-xs">Tidak ada data pengajuan yang sesuai dengan filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="bg-blue-50/30 dark:bg-slate-900/50 px-8 py-6 rounded-[2.5rem] border border-blue-100 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest italic">Menampilkan {{ $leaveRequests->count() }} Data Tinjauan</p>
        <div>
            {{ $leaveRequests->links() }}
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
