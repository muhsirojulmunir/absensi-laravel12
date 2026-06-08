@extends('layouts.master')
@section('title', 'Data Tim')
@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-20 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <div class="space-y-1 transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic">Data <span class="text-blue-500">Anggota Tim</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 font-medium tracking-wide">Pantau profil dan performa kehadiran divisi Anda.</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] px-6 py-3 rounded-2xl flex items-center space-x-4 transition-all duration-300 hover:-translate-y-1">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-500 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 italic font-black text-xs">
                    {{ Auth::user()->division->name ?? 'N/A' }}
                </div>
                <div>
                    <p class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-1">Divisi Terdata</p>
                    <p class="text-sm font-bold text-blue-900 dark:text-blue-100 tracking-tight">{{ $employees->count() }} Anggota</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] shadow-[0_15px_50px_rgba(0,0,0,0.03)] overflow-hidden transition-all hover:shadow-[0_15px_50px_rgba(0,0,0,0.06)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-blue-50/30 dark:bg-slate-900/50 border-b border-blue-100/50 dark:border-slate-700">
                        <th class="px-8 py-6 text-[11px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">Profil Karyawan</th>
                        <th class="px-6 py-6 text-[11px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">Jabatan</th>
                        <th class="px-6 py-6 text-[11px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em]">Presensi Hari Ini</th>
                        <th class="px-8 py-6 text-[11px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50/50 dark:divide-slate-700">
                    @forelse($employees as $employee)
                        @php $todayAtt = $employee->attendances->first(); @endphp
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/30 transition-all duration-300 group/row">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-100/30 dark:bg-slate-700 border border-blue-100 dark:border-slate-600 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-lg shadow-sm group-hover/row:scale-110 group-hover/row:rotate-3 transition-transform overflow-hidden font-mono">
                                        @if($employee->avatar)
                                            <img src="{{ asset('storage/' . $employee->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($employee->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-[15px] font-black text-blue-950 dark:text-white uppercase tracking-tighter leading-tight">{{ $employee->name }}</div>
                                        <div class="text-[9px] text-blue-400 dark:text-blue-500 font-black uppercase tracking-widest mt-0.5">ID: {{ $employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="inline-flex items-center px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-900/50">
                                    {{ $employee->position ?? 'STAFF' }}
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                @if(!$todayAtt)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider border border-slate-100 dark:border-slate-800 italic">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                        Belum Absen
                                    </span>
                                @else
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm border {{ $todayAtt->status == 'Hadir' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30' : 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30' }}">
                                                {{ $todayAtt->status }}
                                            </span>
                                            <span class="text-[10px] font-bold text-blue-900 dark:text-blue-200 font-mono">{{ substr($todayAtt->check_in, 0, 5) }}</span>
                                        </div>
                                        @if($todayAtt->is_pulang_cepat)
                                            <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border border-orange-100 dark:border-orange-900/30 w-max tracking-tighter">
                                                Pulang Cepat ⚡
                                            </span>
                                        @endif
                                        @if($todayAtt->note === 'Absen Diluar')
                                            <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/30 w-max tracking-tighter">
                                                📍 Absen Diluar
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-right">
                                <a href="{{ route('pic.employees.show', $employee) }}" 
                                   class="inline-flex items-center justify-center p-2.5 bg-white dark:bg-slate-800 border-2 border-blue-100 dark:border-slate-700 text-blue-600 dark:text-blue-400 rounded-2xl hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white dark:hover:text-white hover:border-blue-600 dark:hover:border-blue-500 transition-all duration-300 shadow-sm active:scale-90 group/btn">
                                    <svg class="w-5 h-5 transition-transform group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="ml-2 text-[10px] font-black uppercase tracking-widest pr-1">Biodata</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-32 text-center">
                                <div class="w-20 h-20 bg-blue-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100 dark:border-slate-800 shadow-inner">
                                    <svg class="w-10 h-10 text-blue-200 dark:text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <h3 class="text-blue-900 dark:text-white font-bold text-lg mb-1 italic">Belum Ada Data Tim</h3>
                                <p class="text-blue-400 dark:text-blue-500 text-sm font-medium">Anggota tim divisi Anda akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
