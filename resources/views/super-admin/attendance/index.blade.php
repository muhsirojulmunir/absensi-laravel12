@extends('layouts.master')
@section('title', 'Monitoring Absensi')
@section('content')
<div class="space-y-8" x-data="{
    showDeleteModal: false,
    deleteUrl: '',
    deleteLabel: '',
    openDelete(url, name, dateStr) {
        this.deleteUrl = url;
        this.deleteLabel = name + ' — ' + dateStr;
        this.showDeleteModal = true;
    },
    closeDelete() {
        this.showDeleteModal = false;
        this.deleteUrl = '';
        this.deleteLabel = '';
    }
}">
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-5 py-3 rounded-2xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-3 rounded-2xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Monitoring Absensi</h1>
            <p class="text-blue-500 dark:text-blue-400 mt-1">Pemantauan kehadiran seluruh karyawan — Super Admin dapat menghapus & mencadangkan data.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('super-admin.attendance.deleted-backups') }}"
               class="inline-flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Backup Terhapus
            </a>
            <form action="{{ route('super-admin.attendance.index') }}" method="GET" class="flex items-center space-x-3 bg-slate-50 dark:bg-slate-900 shadow-inner border border-blue-100 dark:border-slate-800 rounded-2xl px-4 py-2 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <label class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-[0.15em]">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                       class="bg-transparent border-none text-sm font-bold text-blue-950 dark:text-white focus:ring-0 cursor-pointer [color-scheme:dark] px-2 outline-none">
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Hadir</p>
            <p class="text-xl font-bold text-emerald-500 dark:text-emerald-400">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Pulang Cepat</p>
            <p class="text-xl font-bold text-amber-500 dark:text-amber-400">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Izin/Sakit</p>
            <p class="text-xl font-bold text-blue-500 dark:text-blue-400">{{ $attendances->whereIn('status', ['Izin', 'Sakit'])->count() }}</p>
        </div>
        <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 p-4 rounded-2xl">
            <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">Total Log</p>
            <p class="text-xl font-bold text-blue-950 dark:text-white">{{ $attendances->count() }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-blue-100 dark:border-slate-700 rounded-3xl overflow-hidden">
        <div class="bg-blue-50/30 dark:bg-slate-900/50 px-8 py-5 border-b border-blue-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                <h2 class="text-sm font-bold text-blue-950 dark:text-white uppercase tracking-wider">Log Langsung</h2>
            </div>
            <div class="text-[11px] font-medium text-blue-600/80 dark:text-blue-400/80">
                Data untuk {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 shadow-inner/30 border-b border-blue-50 dark:border-slate-800">
                        <th class="px-8 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Check In</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Check Out</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-right">Status</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-all group">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <div class="w-9 h-9 bg-blue-50 dark:bg-slate-900 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold border border-blue-200/50 dark:border-slate-700 overflow-hidden">
                                        @if($attendance->user->avatar)
                                            <img src="{{ asset('storage/' . $attendance->user->avatar) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            {{ substr($attendance->user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-blue-950 dark:text-white">{{ $attendance->user->name }}</div>
                                        <div class="text-[10px] text-blue-500 dark:text-blue-400 font-bold uppercase tracking-wider">{{ $attendance->user->division->name ?? 'No Division' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <span class="text-sm font-bold text-blue-400">{{ $attendance->check_in ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <span class="text-sm font-bold text-blue-500">{{ $attendance->check_out ?? '--:--' }}</span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right text-xs">
                                @php
                                    $statusColors = [
                                        'Hadir' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                        'Terlambat' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                        'Izin' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                        'Sakit' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                    ];
                                    $colorClass = $statusColors[$attendance->status] ?? 'bg-blue-50 text-blue-500 border-blue-200';
                                @endphp
                                <span class="px-3 py-1.5 border {{ $colorClass }} font-bold uppercase rounded-lg tracking-wide">{{ $attendance->status }}</span>
                                @if($attendance->is_pulang_cepat)
                                    <span class="block mt-1 text-[10px] text-amber-600 dark:text-amber-400 font-bold">Pulang Cepat</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <button type="button"
                                    @click="openDelete('{{ route('super-admin.attendance.destroy', $attendance) }}?date={{ $date }}', @js($attendance->user->name), @js(\Carbon\Carbon::parse($attendance->date)->translatedFormat('d M Y')))"
                                    class="p-2 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all active:scale-95"
                                    title="Hapus absensi">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <p class="text-blue-500 dark:text-blue-400 text-sm font-semibold opacity-60">Tidak ada catatan kehadiran pada tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal konfirmasi hapus (tengah layar) --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-blue-950/50 backdrop-blur-sm" @click="closeDelete()"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl border border-blue-100 dark:border-slate-700 w-full max-w-md p-8 text-center z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-14 h-14 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-5 text-red-500">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-blue-950 dark:text-white mb-2">Hapus Absensi?</h3>
            <p class="text-sm text-blue-600/80 dark:text-blue-400/80 mb-1">Apakah Anda yakin mau menghapus absensi ini?</p>
            <p class="text-xs font-bold text-blue-950 dark:text-blue-200 mb-6" x-text="deleteLabel"></p>
            <p class="text-[10px] text-amber-600 dark:text-amber-400 mb-6 font-medium">Data akan dicadangkan di menu Backup Terhapus sebelum dihapus permanen.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" @click="closeDelete()"
                    class="px-6 py-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                    Batal
                </button>
                <form :action="deleteUrl" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-lg shadow-red-600/20 transition-all active:scale-95">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
