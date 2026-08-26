@extends('layouts.master')
@section('title', 'Monitoring Absensi')
@section('content')
<div class="space-y-6 animate-fade-in" x-data="{
    showDeleteModal: false,
    deleteUrl: '',
    deleteLabel: '',
    showManualCheckinModal: false,
    showManualCheckoutModal: false,
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
                <button type="button" @click="showImportModal = true"
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
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Karyawan Hadir</p>
            <p class="text-2xl font-bold text-emerald-500 dark:text-emerald-400">{{ $attendances->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Pulang Cepat</p>
            <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ $attendances->where('is_pulang_cepat', true)->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Izin / Sakit</p>
            <p class="text-2xl font-bold text-blue-500 dark:text-blue-400">{{ $attendances->whereIn('status', ['Izin', 'Sakit'])->count() }}</p>
        </div>
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Total Aktivitas</p>
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
    @endif

    {{-- Modal Import Absensi Massal --}}
    <div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showImportModal = false"></div>
        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-slate-200/60 dark:border-slate-800/80 w-full max-w-md overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider">Import Absensi Massal</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-550 font-semibold tracking-wide">Upload Excel / CSV untuk mencatat data absen sekaligus</p>
                </div>
                <button @click="showImportModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-white bg-slate-100 dark:bg-slate-900 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('super-admin.attendance.import') }}" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-4">
                @csrf
                <div class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200/50 dark:border-indigo-900/30 rounded-xl p-3.5 text-[11px] text-indigo-900 dark:text-indigo-300 leading-relaxed space-y-2">
                    <p class="font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Ketentuan Import:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-[10px] text-indigo-800 dark:text-indigo-300">
                        <li>Gunakan template CSV/Excel agar format kolom sesuai.</li>
                        <li><strong class="text-indigo-950 dark:text-indigo-100">Karyawan yang tidak terdaftar</strong> di sistem akan <strong>otomatis dilewati (skipped)</strong>.</li>
                        <li>Format kolom: <code>Nama Karyawan</code>, <code>Tanggal</code>, <code>Jam Masuk</code>, <code>Jam Pulang</code>, <code>Status</code>, <code>Catatan</code>.</li>
                    </ul>
                    <div class="pt-1.5 flex flex-wrap gap-2">
                        <a href="{{ route('super-admin.attendance.import-template', ['format' => 'xlsx']) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('super-admin.attendance.import-template', ['format' => 'csv']) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Template CSV</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Pilih File Excel / CSV (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-300 cursor-pointer border border-slate-200 dark:border-slate-800 rounded-xl p-1">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showImportModal = false"
                        class="btn-premium-secondary py-3 px-4 text-xs uppercase tracking-widest flex-1">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 cursor-pointer shadow-sm shadow-indigo-500/10 flex-1">
                        Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
