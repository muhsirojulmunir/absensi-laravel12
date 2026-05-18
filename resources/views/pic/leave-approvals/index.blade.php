@extends('layouts.master')
@section('title', 'Persetujuan Izin')

@section('content')
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

    <!-- Section Title -->
    <div class="flex items-center gap-4 px-2">
        <h2 class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.3em] whitespace-nowrap">Daftar Pengajuan</h2>
        <div class="h-px bg-blue-100 dark:bg-slate-800 w-full"></div>
    </div>

    <!-- Approvals List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($leaveRequests as $leave)
        <div class="bg-white dark:bg-slate-800 border border-blue-100/50 dark:border-slate-700/50 rounded-[2.5rem] p-6 md:p-8 hover:shadow-[0_15px_50px_rgba(0,0,0,0.03)] dark:hover:shadow-[0_15px_50px_rgba(0,0,0,0.2)] hover:border-blue-200 dark:hover:border-slate-600 transition-all relative overflow-hidden group">
            <!-- Deco Label based on status -->
            <div class="absolute -right-12 -top-12 w-32 h-32 {{ $leave->status == 'pending' ? 'bg-amber-50/50 dark:bg-amber-900/10' : ($leave->status == 'approved' ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : 'bg-rose-50/50 dark:bg-rose-900/10') }} rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            
            <div class="flex flex-col lg:flex-row justify-between gap-8 relative z-10">
                <!-- User Profile & Basic Info -->
                <div class="flex items-start gap-5">
                    <div class="relative">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-[1.5rem] bg-blue-50 dark:bg-slate-700 border-2 border-white dark:border-slate-600 shadow-md overflow-hidden flex items-center justify-center group-hover:rotate-3 transition-transform duration-300">
                            @if($leave->user->avatar)
                                <img src="{{ asset('storage/' . $leave->user->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-black text-blue-500 dark:text-blue-400 uppercase">{{ substr($leave->user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="absolute -bottom-2 -right-2 bg-white dark:bg-slate-800 p-1.5 rounded-xl shadow-sm border border-blue-50 dark:border-slate-700">
                            @if($leave->type == 'Sakit') 🤒 @elseif($leave->type == 'Libur' || $leave->type == 'Cuti Tahunan') 🏝️ @elseif($leave->type == 'Izin') 📝 @else ✨ @endif
                        </div>
                    </div>
                    
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-black text-blue-950 dark:text-white uppercase tracking-tighter">{{ $leave->user->name }}</h3>
                            <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-black px-3 py-1 rounded-lg border border-blue-100 dark:border-blue-800/50 uppercase tracking-widest">{{ $leave->user->employee_id ?? 'N/A' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-[10px] font-black text-blue-400/80 dark:text-blue-500 uppercase tracking-widest italic">{{ $leave->user->division->name ?? 'Division N/A' }}</span>
                            <span class="w-1 h-1 bg-blue-200 dark:bg-slate-700 rounded-full"></span>
                            <div class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold text-xs uppercase tracking-tight">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} — {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason & Actions -->
                <div class="flex-1 flex flex-col justify-center gap-6">
                    <div class="bg-slate-50/50 dark:bg-slate-900/50 p-4 md:p-5 rounded-3xl border border-slate-100 dark:border-slate-800 group-hover:bg-white dark:group-hover:bg-slate-800 transition-all duration-300">
                        <p class="text-blue-400 dark:text-blue-500 text-[8px] font-black uppercase tracking-[0.2em] mb-2 leading-none italic">Alasan Pengajuan</p>
                        <p class="text-sm text-blue-900 dark:text-slate-300 font-medium leading-relaxed">"{{ $leave->reason }}"</p>
                    </div>
                </div>

                <!-- Status Badge & Action Buttons -->
                <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 border-t lg:border-t-0 border-blue-50 dark:border-slate-800 pt-6 lg:pt-0">
                    @if($leave->status === 'pending')
                        <div class="flex flex-col items-end gap-3 w-full">
                            <p class="text-[9px] font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest italic animate-pulse">Menunggu Tindakan PIC</p>
                            <div class="flex items-center gap-3 w-full lg:w-auto">
                                <form action="{{ route('pic.leave-approvals.reject', $leave) }}" method="POST" class="flex-1 lg:flex-none">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" onclick="return confirm('Tolak pengajuan ini?')" class="w-full bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-500 dark:text-rose-400 border-2 border-rose-100 dark:border-rose-900/30 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all active:scale-95 shadow-sm flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        <span>Tolak</span>
                                    </button>
                                </form>
                                <form action="{{ route('pic.leave-approvals.approve', $leave) }}" method="POST" class="flex-1 lg:flex-none">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" onclick="return confirm('Setujui pengajuan ini?')" class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white border-2 border-blue-600 dark:border-blue-500 px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-blue-200 dark:shadow-none flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Setujui</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-5 py-2.5 {{ $leave->status == 'approved' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30' : 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30' }} border-2 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm">
                                @if($leave->status == 'approved')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>DISETUJUI</span>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span>DITOLAK</span>
                                @endif
                            </span>
                            <p class="text-[9px] font-bold text-slate-300 dark:text-slate-600 italic uppercase">Direspon {{ $leave->updated_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[3rem] py-24 px-10 text-center shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div class="w-24 h-24 bg-blue-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-blue-50 dark:border-slate-800">
                <svg class="w-12 h-12 text-blue-200 dark:text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m4 6h4"></path></svg>
            </div>
            <h3 class="text-xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Inbox Bersih!</h3>
            <p class="text-blue-400 dark:text-blue-500 font-medium max-w-sm mx-auto mt-2">Semua pengajuan izin sudah Anda tinjau. Kerja bagus! ✨</p>
        </div>
        @endforelse
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
