@extends('layouts.master')
@section('title', 'Persetujuan Izin')

@section('content')
@php
    $destroyRoute = auth()->user()->role->slug === 'super-admin' ? 'super-admin.leave-approvals.destroy' : 'pic.leave-approvals.destroy';
@endphp
<div class="max-w-5xl mx-auto space-y-6 pb-20 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Persetujuan Izin</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Tinjau dan kelola permohonan izin divisi Anda.</p>
        </div>
        
        <div class="inline-flex items-center space-x-3.5 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 px-5 py-3 rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <div class="bg-blue-50 dark:bg-blue-950/30 p-2.5 rounded-xl text-blue-600 dark:text-blue-400 font-bold text-xs uppercase tracking-wider">PIC</div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Otoritas</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 tracking-tight">Manager Kehadiran ✨</p>
            </div>
        </div>
    </div>

    <!-- Monthly Stats Summary (Divisional Overview) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[100px]">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total (Bulan Ini)</p>
            <p class="text-2xl font-bold text-slate-850 dark:text-slate-100 tracking-tight">{{ $totalCount }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[100px]">
            <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest">Pending</p>
            <p class="text-2xl font-bold text-amber-500 tracking-tight">{{ $pendingCount }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[100px]">
            <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest">Disetujui</p>
            <p class="text-2xl font-bold text-emerald-500 tracking-tight">{{ $approvedCount }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[100px]">
            <p class="text-[9px] font-bold text-rose-500 uppercase tracking-widest">Ditolak</p>
            <p class="text-2xl font-bold text-rose-500 tracking-tight">{{ $rejectedCount }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/25 text-rose-600 dark:text-rose-400 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Section -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-4 flex flex-col xl:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto w-full xl:w-auto pb-2 xl:pb-0 scrollbar-hide">
            @php $currentStatus = request('status', 'pending'); @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="px-4 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $currentStatus == 'pending' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-250/20 dark:border-slate-850' }}">
                Menunggu <span class="bg-white/20 px-2 py-0.5 rounded-full ml-1 text-[9px]">{{ $pendingCount }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}" class="px-4 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $currentStatus == 'approved' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-250/20 dark:border-slate-850' }}">Disetujui</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" class="px-4 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $currentStatus == 'rejected' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-250/20 dark:border-slate-850' }}">Ditolak</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="px-4 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $currentStatus == 'all' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-250/20 dark:border-slate-850' }}">Semua</a>
        </div>
        
        <form action="{{ route('pic.leave-approvals.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto" x-data="{ dateFilter: '{{ request('date_filter', 'all') }}' }">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="relative w-full sm:w-48">
                <select name="date_filter" x-model="dateFilter" onchange="if(this.value !== 'custom') this.form.submit()" class="cursor-pointer">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="custom">Pilih Tanggal...</option>
                </select>
            </div>
            
            <template x-if="dateFilter === 'custom'">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto animate-fade-in">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" required class="w-full sm:w-auto text-xs font-bold uppercase">
                    <span class="text-slate-400 font-bold hidden sm:block">-</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" required class="w-full sm:w-auto text-xs font-bold uppercase">
                    <button type="submit" class="btn-premium-primary p-3 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </template>
        </form>
    </div>

    <!-- Approvals Table -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800/85">
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">Karyawan</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">Pengajuan</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap w-64">Alasan</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @forelse($leaveRequests as $leave)
                    <tr class="hover:bg-slate-55 dark:hover:bg-slate-900/20 transition-colors group">
                        <td class="px-6 py-5 align-top">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-650 dark:text-slate-350 font-bold border border-slate-200/40 dark:border-slate-800 text-xs overflow-hidden shadow-inner flex items-center justify-center shrink-0">
                                    @if($leave->user->avatar)
                                        <img src="{{ asset('storage/' . $leave->user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ substr($leave->user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="space-y-0.5">
                                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase leading-none">{{ $leave->user->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider bg-slate-100 dark:bg-slate-900/50 px-2 py-0.5 rounded border border-slate-200/30 dark:border-slate-800">{{ $leave->user->employee_id ?? 'N/A' }}</span>
                                        <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">{{ $leave->user->division->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top">
                            <div class="space-y-1.5">
                                <div>
                                    @if($leave->type === 'Lupa Absen')
                                        <span class="bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-[9px] font-bold px-2 py-1 rounded-lg border border-rose-100 dark:border-rose-900/20 uppercase tracking-wider whitespace-nowrap">
                                            ⏰ Lupa Absen: {{ $leave->sub_type ?? '-' }}
                                        </span>
                                    @elseif($leave->type === 'Absen Diluar')
                                        <span class="bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 text-[9px] font-bold px-2 py-1 rounded-lg border border-teal-100 dark:border-teal-900/20 uppercase tracking-wider whitespace-nowrap">
                                            📍 Diluar Radius
                                        </span>
                                    @elseif($leave->type === 'Izin Masuk Siang')
                                        <span class="bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-[9px] font-bold px-2 py-1 rounded-lg border border-amber-100 dark:border-amber-900/20 uppercase tracking-wider whitespace-nowrap">
                                            🌅 Masuk Siang
                                        </span>
                                    @else
                                        <span class="bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-450 text-[9px] font-bold px-2 py-1 rounded-lg border border-blue-100 dark:border-blue-900/20 uppercase tracking-wider whitespace-nowrap">
                                            @if($leave->type == 'Sakit') 🤒 @elseif($leave->type == 'Libur' || $leave->type == 'Cuti Tahunan') 🏝️ @elseif($leave->type == 'Izin Tidak Masuk') 📝 @endif
                                            {{ $leave->type }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-450 dark:text-slate-500 font-bold text-[9px] uppercase tracking-wide">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="whitespace-nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M') }} — {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}</span>
                                </div>
                                @if(in_array($leave->type, ['Lupa Absen', 'Absen Diluar']) && $leave->time_start)
                                    <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        Jam Kejadian: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }}</span>
                                    </div>
                                @elseif($leave->type === 'Izin Masuk Siang' && $leave->time_start && $leave->time_end)
                                    <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        Jam Izin: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($leave->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($leave->time_end)->format('H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top">
                            <p class="text-xs text-slate-600 dark:text-slate-350 font-medium leading-relaxed max-w-xs break-words">"{{ $leave->reason }}"</p>
                        </td>
                        <td class="px-6 py-5 align-top">
                            @if($leave->status === 'pending')
                                <div class="flex flex-col gap-1.5">
                                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/20 rounded-lg text-[9px] font-bold uppercase tracking-wider inline-flex items-center gap-1 w-max shadow-sm">
                                        <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span> PENDING
                                    </span>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-550 font-bold italic">{{ $leave->created_at->diffForHumans() }}</span>
                                </div>
                            @elseif($leave->status === 'approved')
                                <div class="flex flex-col gap-1">
                                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-450 border border-emerald-100 dark:border-emerald-900/25 rounded-lg text-[9px] font-bold uppercase tracking-wider inline-flex items-center gap-1 w-max">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> DISETUJUI
                                    </span>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-550 font-bold italic">Oleh PIC</span>
                                </div>
                            @else
                                <div class="flex flex-col gap-1">
                                    <span class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/25 rounded-lg text-[9px] font-bold uppercase tracking-wider inline-flex items-center gap-1 w-max">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg> DITOLAK
                                    </span>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-550 font-bold italic">Oleh PIC</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top text-right">
                            @if($leave->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('pic.leave-approvals.approve', $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" onclick="return confirm('Setujui pengajuan ini?')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl text-[9px] font-bold uppercase tracking-widest transition-all shadow-sm shadow-blue-500/10 flex items-center gap-1 active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="hidden sm:inline">Setujui</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('pic.leave-approvals.reject', $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" onclick="return confirm('Tolak pengajuan ini?')" class="bg-white dark:bg-slate-850 hover:bg-slate-50 dark:hover:bg-slate-800 text-rose-600 border border-slate-200 dark:border-slate-800 px-3 py-2 rounded-xl text-[9px] font-bold uppercase tracking-widest transition-all shadow-sm flex items-center gap-1 active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span class="hidden sm:inline">Tolak</span>
                                        </button>
                                    </form>
                                    @if(auth()->user()->role->slug === 'super-admin')
                                    <form action="{{ route($destroyRoute, $leave) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin menghapus pengajuan ini secara permanen?')" class="bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 border border-slate-200 dark:border-slate-800 px-3 py-2 rounded-xl text-[9px] font-bold uppercase tracking-widest transition-all shadow-sm flex items-center gap-1 active:scale-95 cursor-pointer" title="Hapus">
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
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus riwayat pengajuan ini?')" class="bg-slate-50 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-900/20 text-slate-400 hover:text-rose-650 border border-slate-200 dark:border-slate-800 px-3 py-2 rounded-xl text-[9px] font-bold uppercase tracking-widest transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer" title="Hapus Riwayat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-center mx-auto mb-4 border border-slate-200/50 dark:border-slate-800/80 text-slate-350">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m4 6h4"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Data Kosong</h3>
                            <p class="text-slate-400 dark:text-slate-500 font-medium max-w-sm mx-auto mt-1 text-xs">Tidak ada data pengajuan yang sesuai dengan filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="bg-slate-50/50 dark:bg-slate-900/20 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
        <p class="font-medium text-slate-450 dark:text-slate-500 italic">Menampilkan {{ $leaveRequests->count() }} Data Tinjauan</p>
        <div>
            {{ $leaveRequests->links() }}
        </div>
    </div>
</div>
@endsection
