@extends('layouts.master')
@section('title', 'Monitoring Absensi')
@section('content')
<div class="space-y-6 animate-fade-in" x-data="{
    showDeleteModal: false,
    deleteUrl: '',
    deleteLabel: '',
    showManualCheckinModal: false,
    showManualCheckoutModal: false,
    showManualLeaveModal: false,
    showImportModal: false,
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
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/25 text-rose-600 dark:text-rose-400 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 py-2">
        {{-- Judul --}}
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Monitoring Absensi</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pemantauan kehadiran seluruh karyawan.</p>
        </div>

        {{-- Tombol-tombol --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Backup Terhapus + Pilih Tanggal --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('super-admin.attendance.deleted-backups') }}"
                   class="btn-premium-secondary py-2.5 px-4 text-xs uppercase tracking-widest flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <span>Backup</span>
                </a>
                <form action="{{ route('super-admin.attendance.index') }}" method="GET"
                      class="flex items-center gap-2 bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-xl px-4 py-2 shadow-sm">
                    <label class="text-[9px] font-bold text-slate-450 uppercase tracking-widest whitespace-nowrap">Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                           class="bg-transparent border-none p-0 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-0 cursor-pointer [color-scheme:dark] outline-none w-[110px]">
                </form>
            </div>

            {{-- Tombol Absen Manual & Import (hanya Super Admin JMN) --}}
            <div class="flex items-center gap-2 border-t sm:border-t-0 sm:border-l border-slate-200/60 dark:border-slate-800/60 pt-3 sm:pt-0 sm:pl-3">
                <button type="button" @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-indigo-500/10 active:scale-95 cursor-pointer uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Absen</span>
                </button>
                @if(isset($employees))
                <button type="button" @click="showManualCheckinModal = true"
                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-emerald-500/10 active:scale-95 cursor-pointer uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Masuk</span>
                </button>
                <button type="button" @click="showManualCheckoutModal = true"
                    class="inline-flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-orange-500/10 active:scale-95 cursor-pointer uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                    <span>Pulang</span>
                </button>
                <button type="button" @click="showManualLeaveModal = true"
                    class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-purple-500/10 active:scale-95 cursor-pointer uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Atur Cuti</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-1.5">Karyawan Hadir</p>
            <p class="text-2xl font-bold text-emerald-500 dark:text-emerald-400">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-1.5">Pulang Cepat</p>
            <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-1.5">Izin / Sakit / Cuti</p>
            <p class="text-2xl font-bold text-blue-500 dark:text-blue-400">{{ $attendances->whereIn('status', ['Izin', 'Sakit', 'Cuti'])->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-1.5">Total Aktivitas</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $attendances->count() }}</p>
        </div>
    </div>

    <!-- Live Log Table -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-slate-50/50 dark:bg-slate-900/20 px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                <h2 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Log Langsung Kehadiran</h2>
            </div>
            <div class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wide">
                {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800/85">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Check In</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Check Out</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-slate-55 dark:hover:bg-slate-900/20 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold border border-slate-200/40 dark:border-slate-800 text-xs overflow-hidden shadow-inner">
                                        @if($attendance->user?->avatar)
                                            <img src="{{ asset('storage/' . $attendance->user->avatar) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            {{ substr($attendance->user?->name ?? 'K', 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-white">{{ $attendance->user?->name ?? 'User Tidak Ditemukan' }}</div>
                                        <div class="text-[9px] text-slate-400 dark:text-slate-550 font-bold uppercase tracking-wider">{{ $attendance->user?->division?->name ?? 'Tanpa Divisi' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $attendance->check_in ?? '--:--' }}</span>
                                @if($attendance->check_in)
                                    @include('super-admin.attendance.partials.method-badge', [
                                        'method' => $attendance->check_in_method ?? 'otomatis',
                                        'distance' => $attendance->check_in_distance_meters ?? null,
                                        'photo' => $attendance->check_in_photo ?? null,
                                    ])
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $attendance->check_out ?? '--:--' }}</span>
                                @if($attendance->check_out)
                                    @include('super-admin.attendance.partials.method-badge', [
                                        'method' => $attendance->check_out_method ?? 'otomatis',
                                        'distance' => $attendance->check_out_distance_meters ?? null,
                                        'photo' => $attendance->check_out_photo ?? null,
                                    ])
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                @php
                                    $statusColors = [
                                        'Hadir' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/20',
                                        'Terlambat' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/20',
                                        'Izin' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/20',
                                        'Sakit' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/20',
                                        'Cuti' => 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-900/20',
                                    ];
                                    $colorClass = $statusColors[$attendance->status] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                @endphp
                                <span class="px-2.5 py-1 border {{ $colorClass }} font-bold uppercase rounded-lg tracking-wider text-[9px]">{{ $attendance->status }}</span>
                                @if($attendance->is_pulang_cepat)
                                    <span class="block mt-1 text-[8px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wider">Pulang Cepat</span>
                                @endif
                                @if($attendance->note === 'Absen Diluar')
                                    <span class="block mt-1 text-[8px] text-teal-600 dark:text-teal-450 font-bold uppercase tracking-wider">📍 Diluar Radius</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button type="button"
                                    @click="openDelete('{{ route('super-admin.attendance.destroy', $attendance) }}?date={{ $date }}', @js($attendance->user?->name ?? 'Karyawan'), @js(\Carbon\Carbon::parse($attendance->date)->translatedFormat('d M Y')))"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 transition-colors cursor-pointer"
                                    title="Hapus absensi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-slate-400 dark:text-slate-500 text-xs font-semibold italic">Tidak ada catatan kehadiran pada tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeDelete()"></div>
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-800/80 w-full max-w-sm p-6 text-center z-10">
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/30 rounded-xl flex items-center justify-center mx-auto mb-4 text-rose-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider mb-2">Hapus Absensi?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Apakah Anda yakin mau menghapus absensi ini?</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-4" x-text="deleteLabel"></p>
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200/50 dark:border-amber-900/20 rounded-xl p-3 text-[10px] text-amber-600 dark:text-amber-400 text-left mb-6 leading-relaxed font-semibold">
                * Data akan dicadangkan di menu "Backup Terhapus" terlebih dahulu sebelum dihapus permanen.
            </div>
            <div class="flex gap-3 justify-center">
                <button type="button" @click="closeDelete()"
                    class="btn-premium-secondary py-2.5 px-4 text-xs uppercase tracking-widest flex-1">
                    Batal
                </button>
                <form :action="deleteUrl" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 cursor-pointer shadow-sm shadow-rose-500/10">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Absen Masuk Manual --}}
    @if(isset($employees))
    <div x-show="showManualCheckinModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showManualCheckinModal = false"></div>
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-800/80 w-full max-w-md overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider">Absen Masuk Manual</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-550 font-semibold tracking-wide">Digunakan saat server maintenance / kendala sistem</p>
                </div>
                <button @click="showManualCheckinModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-white bg-slate-100 dark:bg-slate-900 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('super-admin.attendance.manual-checkin') }}" method="POST" class="p-6 overflow-y-auto space-y-4" onsubmit="return confirm('PENTING: Menyimpan absen masuk manual ini akan menindih (overwrite) data absensi masuk yang sudah ada pada tanggal tersebut. Apakah Anda yakin ingin melanjutkan?')">
                @csrf
                <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-250/20 dark:border-amber-900/30 rounded-xl p-3.5 text-[10px] text-amber-800 dark:text-amber-300 leading-relaxed font-semibold">
                    <p class="font-bold text-amber-950 dark:text-amber-200 mb-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Perhatian:
                    </p>
                    Data yang sudah ada pada tanggal &amp; karyawan terpilih akan ditimpa (overwrite).
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Karyawan</label>
                    <select name="user_id" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} — {{ $emp->role->name }}{{ $emp->division ? ' (' . $emp->division->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Jam Masuk</label>
                        <input type="time" name="check_in" required>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Status</label>
                    <select name="status" required>
                        <option value="Hadir">Hadir</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Catatan (Opsional)</label>
                    <input type="text" name="note" placeholder="Contoh: Server error, absen manual">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showManualCheckinModal = false"
                        class="btn-premium-secondary py-3 px-4 text-xs uppercase tracking-widest flex-1">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 cursor-pointer shadow-sm shadow-emerald-500/10 flex-1">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Absen Pulang Manual --}}
    <div x-show="showManualCheckoutModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showManualCheckoutModal = false"></div>
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-800/80 w-full max-w-md overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider">Absen Pulang Manual</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-550 font-semibold tracking-wide">Catat jam pulang karyawan secara manual</p>
                </div>
                <button @click="showManualCheckoutModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-white bg-slate-100 dark:bg-slate-900 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('super-admin.attendance.manual-checkout') }}" method="POST" class="p-6 overflow-y-auto space-y-4" onsubmit="return confirm('PENTING: Menyimpan absen pulang manual ini akan menindih (overwrite) data absensi pulang yang sudah ada pada tanggal terpilih. Apakah Anda yakin ingin melanjutkan?')">
                @csrf
                <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-250/20 dark:border-amber-900/30 rounded-xl p-3.5 text-[10px] text-amber-800 dark:text-amber-300 leading-relaxed font-semibold">
                    <p class="font-bold text-amber-950 dark:text-amber-200 mb-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Perhatian:
                    </p>
                    Data jam pulang yang sudah ada pada tanggal &amp; karyawan terpilih akan ditimpa (overwrite).
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Karyawan</label>
                    <select name="user_id" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} — {{ $emp->role->name }}{{ $emp->division ? ' (' . $emp->division->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Tanggal Masuk</label>
                        <input type="date" name="date" value="{{ $date }}" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Jam Pulang</label>
                        <input type="time" name="check_out" required>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-150/25 dark:border-blue-900/30 rounded-xl p-3 text-[10px] text-blue-700 dark:text-blue-400 leading-relaxed font-semibold">
                    💡 <strong>Live Streaming:</strong> Masukkan <strong>tanggal masuk</strong> (kemarin), bukan tanggal hari ini. Sistem akan mendeteksi shift malamnya.
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Catatan (Opsional)</label>
                    <input type="text" name="note" placeholder="Contoh: Pulang cepat selesai shift">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showManualCheckoutModal = false"
                        class="btn-premium-secondary py-3 px-4 text-xs uppercase tracking-widest flex-1">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 cursor-pointer shadow-sm shadow-orange-500/10 flex-1">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Atur Cuti / Izin Karyawan oleh Super Admin --}}
    <div x-show="showManualLeaveModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showManualLeaveModal = false"></div>
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-800/80 w-full max-w-md overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-purple-50/50 dark:bg-purple-950/20">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider">Atur Cuti Karyawan</h3>
                        <p class="text-[10px] text-slate-400 dark:text-slate-550 font-semibold tracking-wide">Pengaturan cuti langsung oleh Super Admin</p>
                    </div>
                </div>
                <button @click="showManualLeaveModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-white bg-slate-100 dark:bg-slate-900 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('super-admin.attendance.manual-leave') }}" method="POST" class="p-6 overflow-y-auto space-y-4" onsubmit="return confirm('Apakah Anda yakin ingin menetapkan cuti/dispensasi ini untuk karyawan terpilih?')">
                @csrf
                <div class="bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-900/30 rounded-xl p-3.5 text-[11px] text-purple-900 dark:text-purple-300 leading-relaxed font-medium">
                    💡 Data cuti akan <strong>langsung disetujui (Approved)</strong>, otomatis tercatat di pengajuan cuti serta muncul dengan status <strong>Cuti</strong> pada log harian dan <strong>rekap absensi bulanan</strong>.
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Karyawan</label>
                    <select name="user_id" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} — {{ $emp->role->name }}{{ $emp->division ? ' (' . $emp->division->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Jenis Dispensasi</label>
                    <select name="type" required>
                        <option value="Cuti" selected>Cuti</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $date }}" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $date }}" required>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Keterangan / Alasan Cuti</label>
                    <input type="text" name="reason" placeholder="Contoh: Cuti Tahunan, keperluan keluarga..." required>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showManualLeaveModal = false"
                        class="btn-premium-secondary py-3 px-4 text-xs uppercase tracking-widest flex-1">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 cursor-pointer shadow-sm shadow-purple-500/10 flex-1">
                        Simpan Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Import Absensi Massal dengan Interactive Preview & Inline Correction --}}
    <div x-data="importAttendanceManager()" 
         @open-import-modal.window="openModal()"
         x-show="showModal" 
         x-cloak 
         style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="close()"></div>
        
        {{-- Modal Box (Dinamis: max-w-lg untuk Upload/Loading, max-w-6xl untuk Preview) --}}
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 transition-all duration-300 z-10 overflow-hidden flex flex-col"
             :class="step === 'preview' ? 'w-full max-w-6xl max-h-[92vh]' : 'w-full max-w-lg max-h-[90vh]'">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/40 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider" 
                            x-text="step === 'preview' ? 'Preview & Koreksi Data Absensi' : 'Import Absensi Massal'"></h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium"
                           x-text="step === 'preview' ? 'Cek validitas data & ubah langsung di tabel sebelum disimpan' : 'Upload file Excel / CSV data absensi karyawan'"></p>
                    </div>
                </div>
                <button type="button" @click="close()" 
                        class="text-slate-400 hover:text-slate-650 dark:hover:text-white bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 p-2 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- STEP 1: UPLOAD FORM --}}
            <div x-show="step === 'upload'" class="p-6 overflow-y-auto space-y-4 flex-1">
                {{-- Alert Error jika ada --}}
                <div x-show="errorMessage" x-cloak class="p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-start gap-2.5 text-xs text-rose-600 dark:text-rose-400 font-semibold">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="errorMessage"></span>
                </div>

                {{-- Ketentuan / Panduan --}}
                <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/50 dark:border-indigo-900/30 rounded-xl p-4 text-[11px] text-indigo-900 dark:text-indigo-300 leading-relaxed space-y-2">
                    <p class="font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Fitur Baru dengan Preview Interaktif:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-[10.5px] text-indigo-800 dark:text-indigo-300/90">
                        <li>File akan <strong>dianalisis & dicek terlebih dahulu</strong> sebelum masuk database.</li>
                        <li>Karyawan yang typo atau belum terdaftar dapat <strong>langsung dipilih/diubah di preview</strong> tanpa perlu edit master Excel!</li>
                        <li>Format kolom: <code>Nama Karyawan</code>, <code>Tanggal</code>, <code>Jam Masuk</code>, <code>Jam Pulang</code>, <code>Status</code>, <code>Catatan</code>.</li>
                    </ul>
                    <div class="pt-2 flex flex-wrap gap-2">
                        <a href="{{ route('super-admin.attendance.import-template', ['format' => 'xlsx']) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('super-admin.attendance.import-template', ['format' => 'csv']) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition-all shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Template CSV</span>
                        </a>
                    </div>
                </div>

                {{-- Input File --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Pilih File Excel / CSV (.xlsx, .xls, .csv)</label>
                    <input type="file" id="import-file-input" accept=".xlsx,.xls,.csv" @change="onFileSelected($event)"
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-300 cursor-pointer border border-slate-200 dark:border-slate-800 rounded-xl p-1">
                </div>

                <div class="flex gap-3 pt-3">
                    <button type="button" @click="close()"
                        class="btn-premium-secondary py-2.5 px-4 text-xs uppercase tracking-widest flex-1">
                        Batal
                    </button>
                    <button type="button" @click="uploadAndPreview()" :disabled="!file"
                        :class="!file ? 'opacity-50 cursor-not-allowed bg-slate-300 dark:bg-slate-700 text-slate-500' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-500/20 active:scale-95 cursor-pointer'"
                        class="font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-1.5 flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Preview & Validasi</span>
                    </button>
                </div>
            </div>

            {{-- STEP 2: LOADING PARSE STATE --}}
            <div x-show="step === 'loading'" x-cloak class="p-12 text-center space-y-4 flex-1 flex flex-col items-center justify-center">
                <div class="relative w-12 h-12">
                    <div class="w-12 h-12 rounded-full border-4 border-indigo-200 dark:border-indigo-900/40 animate-spin border-t-indigo-600"></div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Menganalisis File Excel...</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Membaca baris data dan mencocokkan karyawan terdaftar di sistem.</p>
                </div>
            </div>

            {{-- STEP 3: PREVIEW & INLINE EDITING TABLE --}}
            <div x-show="step === 'preview'" x-cloak class="p-5 overflow-hidden flex flex-col flex-1 space-y-3.5">
                {{-- Stats Bar & Tabs --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200/60 dark:border-slate-800/80">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Total: <span class="font-bold text-slate-900 dark:text-white" x-text="totalRows"></span> Baris
                        </div>
                        <div class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Siap Import: <span x-text="validCount"></span></span>
                        </div>
                        <div class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5"
                             :class="invalidCount > 0 ? 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 text-rose-700 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'">
                            <span class="w-2 h-2 rounded-full" :class="invalidCount > 0 ? 'bg-rose-500 animate-pulse' : 'bg-slate-400'"></span>
                            <span>Perlu Diperbaiki: <span x-text="invalidCount"></span></span>
                        </div>
                    </div>

                    {{-- Search & Filter Tabs --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <input type="text" x-model="searchQuery" placeholder="Cari nama/tanggal..." 
                               class="text-xs py-1 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-36 sm:w-44">
                        
                        <div class="inline-flex rounded-xl p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-medium">
                            <button type="button" @click="filterTab = 'all'"
                                    :class="filterTab === 'all' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg transition-all">
                                Semua (<span x-text="totalRows"></span>)
                            </button>
                            <button type="button" @click="filterTab = 'invalid'"
                                    :class="filterTab === 'invalid' ? 'bg-rose-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400'"
                                    class="px-2.5 py-1 rounded-lg transition-all">
                                ⚠️ Perlu Diperbaiki (<span x-text="invalidCount"></span>)
                            </button>
                            <button type="button" @click="filterTab = 'valid'"
                                    :class="filterTab === 'valid' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'"
                                    class="px-2.5 py-1 rounded-lg transition-all">
                                ✅ Valid (<span x-text="validCount"></span>)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Helper tip --}}
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between px-1">
                    <span>💡 <em>Anda dapat mengubah Karyawan, Tanggal, Jam, dan Status langsung di tabel. Baris yang diperbaiki akan otomatis menjadi valid.</em></span>
                    <span class="hidden sm:inline text-slate-400">File: <strong class="text-slate-700 dark:text-slate-300" x-text="fileName"></strong></span>
                </div>

                {{-- Table Container --}}
                <div class="overflow-y-auto max-h-[50vh] rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-dark-card shadow-xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-slate-900/90 backdrop-blur-xs text-[10.5px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[130px]">Status Validasi</th>
                                <th class="py-2.5 px-3 min-w-[220px]">Nama Karyawan</th>
                                <th class="py-2.5 px-3 w-[125px]">Tanggal</th>
                                <th class="py-2.5 px-3 w-[85px]">Masuk</th>
                                <th class="py-2.5 px-3 w-[85px]">Pulang</th>
                                <th class="py-2.5 px-3 w-[105px]">Status</th>
                                <th class="py-2.5 px-3 min-w-[140px]">Catatan</th>
                                <th class="py-2.5 px-2.5 w-10 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                            <template x-for="(row, idx) in filteredRows" :key="row.id || idx">
                                <tr :class="!row.is_valid ? 'bg-rose-50/40 dark:bg-rose-950/20 hover:bg-rose-50/70' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/40'" class="transition-colors">
                                    {{-- Row Number --}}
                                    <td class="py-2 px-3 text-center text-slate-400 text-[11px] tabular-nums" x-text="row.row_number"></td>

                                    {{-- Status Validasi --}}
                                    <td class="py-2 px-3">
                                        <template x-if="row.is_valid">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300/40">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Valid</span>
                                            </span>
                                        </template>
                                        <template x-if="!row.is_valid">
                                            <div class="inline-flex flex-col items-start gap-0.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-400 border border-rose-300/40">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Perlu Diperbaiki</span>
                                                </span>
                                                <span class="text-[10px] text-rose-600 dark:text-rose-400 line-clamp-1 max-w-[150px]" :title="row.error_message" x-text="row.error_message"></span>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Nama Karyawan --}}
                                    <td class="py-2 px-3">
                                        <div class="space-y-1">
                                            <select x-model.number="row.user_id" @change="validateRow(row)"
                                                    :class="!row.user_id ? 'border-rose-400 dark:border-rose-500 bg-rose-50/60 dark:bg-rose-900/30 text-rose-900 dark:text-rose-200' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100'"
                                                    class="w-full text-xs font-semibold rounded-lg py-1 px-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                                <option value="" disabled :selected="!row.user_id">
                                                    ⚠️ Pilih Karyawan...
                                                </option>
                                                <template x-for="emp in employees" :key="emp.id">
                                                    <option :value="emp.id" x-text="emp.label" :selected="row.user_id === emp.id"></option>
                                                </template>
                                            </select>
                                            <template x-if="row.raw_name">
                                                <p class="text-[9.5px] text-slate-400 dark:text-slate-500 truncate" title="Nama di Excel">
                                                    Excel: <span class="font-mono text-slate-600 dark:text-slate-300" x-text="row.raw_name"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="py-2 px-3">
                                        <input type="date" x-model="row.date" @change="validateRow(row)"
                                               :class="!row.date ? 'border-rose-400 bg-rose-50 text-rose-900' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100'"
                                               class="w-full text-[11px] font-semibold rounded-lg py-1 px-1.5 border focus:ring-1 focus:ring-indigo-500 focus:outline-none [color-scheme:dark]">
                                    </td>

                                    {{-- Jam Masuk --}}
                                    <td class="py-2 px-3">
                                        <input type="time" x-model="row.check_in" @change="validateRow(row)"
                                               class="w-full text-[11px] font-mono rounded-lg py-1 px-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                    </td>

                                    {{-- Jam Pulang --}}
                                    <td class="py-2 px-3">
                                        <input type="time" x-model="row.check_out" @change="validateRow(row)"
                                               class="w-full text-[11px] font-mono rounded-lg py-1 px-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-2 px-3">
                                        <select x-model="row.status" @change="validateRow(row)"
                                                class="w-full text-xs font-semibold rounded-lg py-1 px-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                            <option value="Hadir">Hadir</option>
                                            <option value="Terlambat">Terlambat</option>
                                            <option value="Izin">Izin</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Cuti">Cuti</option>
                                        </select>
                                    </td>

                                    {{-- Catatan --}}
                                    <td class="py-2 px-3">
                                        <input type="text" x-model="row.note" placeholder="Catatan..."
                                               class="w-full text-xs rounded-lg py-1 px-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-2 px-2.5 text-center">
                                        <button type="button" @click="removeRow(row)" title="Hapus baris ini dari import"
                                                class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            {{-- Kosong / Filter nihil --}}
                            <tr x-show="filteredRows.length === 0" x-cloak>
                                <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-bold text-xs">Tidak ada data untuk filter ini.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Preview Footer --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="step = 'upload'"
                            class="btn-premium-secondary py-2.5 px-4 text-xs uppercase tracking-widest flex items-center gap-1.5 w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Ganti File</span>
                    </button>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="validCount"></span> dari <span x-text="totalRows"></span> data siap disimpan
                        </span>
                        <button type="button" @click="confirmImport()" :disabled="validCount === 0"
                                :class="validCount === 0 ? 'opacity-50 cursor-not-allowed bg-slate-300 dark:bg-slate-700 text-slate-500' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-500/20 active:scale-95 cursor-pointer'"
                                class="font-bold py-2.5 px-5 rounded-xl text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan <span x-text="validCount"></span> Data Valid</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 4: SAVING TO DATABASE --}}
            <div x-show="step === 'saving'" x-cloak class="p-12 text-center space-y-4 flex-1 flex flex-col items-center justify-center">
                <div class="relative w-12 h-12">
                    <div class="w-12 h-12 rounded-full border-4 border-emerald-200 dark:border-emerald-900/40 animate-spin border-t-emerald-600"></div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Menyimpan Data Absensi...</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Mencatat data kehadiran, menghitung jam kerja dan keterlambatan.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function importAttendanceManager() {
    return {
        showModal: false,
        step: 'upload', // 'upload', 'loading', 'preview', 'saving'
        file: null,
        fileName: '',
        errorMessage: '',
        parsedRows: [],
        employees: [],
        filterTab: 'all', // 'all', 'invalid', 'valid'
        searchQuery: '',

        openModal() {
            this.showModal = true;
            this.reset();
        },

        close() {
            this.showModal = false;
            setTimeout(() => this.reset(), 300);
        },

        reset() {
            this.step = 'upload';
            this.file = null;
            this.fileName = '';
            this.errorMessage = '';
            this.parsedRows = [];
            this.employees = [];
            this.filterTab = 'all';
            this.searchQuery = '';
            const fi = document.getElementById('import-file-input');
            if (fi) fi.value = '';
        },

        get totalRows() {
            return this.parsedRows.length;
        },

        get validCount() {
            return this.parsedRows.filter(r => r.is_valid).length;
        },

        get invalidCount() {
            return this.parsedRows.filter(r => !r.is_valid).length;
        },

        get filteredRows() {
            let list = this.parsedRows;
            if (this.filterTab === 'invalid') {
                list = list.filter(r => !r.is_valid);
            } else if (this.filterTab === 'valid') {
                list = list.filter(r => r.is_valid);
            }
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(r => {
                    const raw = (r.raw_name || '').toLowerCase();
                    const date = (r.date || '').toLowerCase();
                    const emp = this.employees.find(e => e.id === r.user_id);
                    const empName = emp ? emp.name.toLowerCase() : '';
                    return raw.includes(q) || date.includes(q) || empName.includes(q);
                });
            }
            return list;
        },

        onFileSelected(e) {
            const files = e.target.files;
            if (files && files[0]) {
                this.file = files[0];
                this.fileName = files[0].name;
                this.errorMessage = '';
            }
        },

        async uploadAndPreview() {
            if (!this.file) {
                this.errorMessage = 'Silakan pilih file Excel / CSV terlebih dahulu.';
                return;
            }

            this.step = 'loading';
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch("{{ route('super-admin.attendance.import-preview') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal memproses file.');
                }

                // Urutkan baris yang invalid di atas agar pengguna langsung menyadari data yang perlu diperbaiki
                const rows = data.rows || [];
                rows.sort((a, b) => (a.is_valid === b.is_valid) ? 0 : a.is_valid ? 1 : -1);

                this.parsedRows = rows;
                this.employees = data.employees || [];
                this.step = 'preview';

                if (this.invalidCount > 0) {
                    this.filterTab = 'invalid';
                } else {
                    this.filterTab = 'all';
                }
            } catch (err) {
                this.step = 'upload';
                this.errorMessage = err.message || 'Terjadi kesalahan saat membaca file.';
            }
        },

        validateRow(row) {
            const errors = [];
            if (!row.user_id) {
                errors.push(row.raw_name ? `Karyawan '${row.raw_name}' tidak terdaftar` : 'Karyawan belum dipilih');
            }
            if (!row.date || !row.date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                errors.push('Tanggal tidak valid (YYYY-MM-DD)');
            }
            const hasCheckIn = row.check_in && row.check_in.trim() !== '';
            const hasCheckOut = row.check_out && row.check_out.trim() !== '';
            const isLeaveStatus = ['Izin', 'Sakit', 'Cuti'].includes(row.status);
            if (!hasCheckIn && !hasCheckOut && !isLeaveStatus) {
                errors.push('Jam masuk & pulang kosong');
            }

            row.is_valid = (errors.length === 0);
            row.error_message = errors.length > 0 ? errors.join('; ') : null;
        },

        removeRow(row) {
            const idx = this.parsedRows.findIndex(r => r === row || r.id === row.id);
            if (idx !== -1) {
                this.parsedRows.splice(idx, 1);
            }
        },

        async confirmImport() {
            const validRows = this.parsedRows.filter(r => r.is_valid);
            if (validRows.length === 0) {
                alert('Tidak ada data valid yang dapat di-import. Silakan periksa dan perbaiki data terlebih dahulu.');
                return;
            }

            if (this.invalidCount > 0) {
                const ok = confirm(`Perhatian: Terdapat ${this.invalidCount} baris yang belum valid.\n\nHanya ${validRows.length} baris yang VALID yang akan disimpan ke database.\nBaris yang tidak valid akan dilewati.\n\nApakah Anda yakin ingin melanjutkan?`);
                if (!ok) return;
            }

            this.step = 'saving';

            try {
                const res = await fetch("{{ route('super-admin.attendance.import-confirm') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rows: validRows
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyimpan data absensi.');
                }

                alert(data.message || 'Data absensi berhasil di-import!');
                window.location.reload();
            } catch (err) {
                this.step = 'preview';
                alert('Gagal: ' + (err.message || 'Terjadi kesalahan sistem.'));
            }
        }
    };
}
</script>
@endsection
