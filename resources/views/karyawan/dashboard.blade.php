@extends('layouts.master')
@section('title', 'Dashboard Karyawan')
@section('content')
    <div x-data="attendanceHandler()" class="max-w-5xl mx-auto space-y-6">
        <!-- Welcome Section: Bold & Italic (Following History Design) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-4">
            <div class="animate-[fadeIn_0.5s_ease-out] space-y-1 group">
                <h1
                    class="text-3xl md:text-4xl font-black text-blue-950 dark:text-white uppercase tracking-tighter italic transition-transform duration-300 group-hover:translate-x-1">
                    Halo, <span class="text-blue-500">{{ explode(' ', Auth::user()->name)[0] }}</span>
                </h1>
                <p class="text-blue-600/80 dark:text-blue-400 font-medium tracking-wide italic leading-tight">Siap untuk memberikan dampak
                    positif hari ini? ✨</p>
            </div>

            <!-- Date Info: Premium Badge -->
            <div
                class="inline-flex items-center space-x-4 bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-blue-100 dark:border-slate-700 px-6 py-3 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-1.5 rounded-full">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div class="pr-2">
                    <p class="text-[9px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-tighter leading-none mb-1">Hari Ini</p>
                    <p class="text-[11px] font-black text-blue-950 dark:text-blue-100 leading-none">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Grid (Refined) -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-6">

            <!-- Left: Attendance Card (Premium Sleek) -->
            <div
                class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                <!-- Accent Decoration -->
                <div class="absolute -left-10 -top-10 w-24 h-24 bg-blue-50/50 dark:bg-blue-900/10 rounded-full blur-2xl"></div>

                <div class="text-center mb-6 relative z-10">
                    <h2 class="text-xs font-black text-blue-980 dark:text-blue-100 uppercase tracking-[0.2em] mb-3">Kehadiran Hari Ini</h2>
                    <div class="inline-block px-3 py-1 bg-blue-50/50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-900/50"
                        x-text="locationStatus">Mencari Lokasi...</div>
                </div>

                <!-- Clock In / Clock Out Buttons (Mini) -->
                <div class="grid grid-cols-2 gap-4 max-w-[280px] mx-auto mb-6 relative z-10">
                    <!-- Clock In -->
                    <button @click="submitAttendance('in')" :disabled="!isWithinRange || isSubmitting || hasCheckedIn"
                        :class="{
                                        'bg-blue-600 text-white shadow-lg shadow-blue-600/20 active:scale-95': isWithinRange && !hasCheckedIn && !isSubmitting,
                                        'bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-700 border border-slate-100 dark:border-slate-800 cursor-not-allowed': !isWithinRange || hasCheckedIn,
                                        'opacity-60 cursor-wait': isSubmitting
                                    }"
                        class="relative aspect-square rounded-[2rem] flex flex-col items-center justify-center gap-1.5 transition-all duration-300 group">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-[10px] font-black uppercase tracking-widest">Absen Masuk</span>
                        <div x-show="hasCheckedIn"
                            class="absolute inset-0 bg-emerald-500/10 backdrop-blur-[1px] rounded-[2rem] flex items-center justify-center">
                            <div class="bg-emerald-500 rounded-full p-1.5 shadow-sm shadow-emerald-500/30">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>

                    <!-- Clock Out -->
                    <button @click="submitAttendance('out')"
                        :disabled="!isWithinRange || isSubmitting || !hasCheckedIn || hasCheckedOut" :class="{
                                        'bg-orange-500 text-white shadow-lg shadow-orange-500/20 active:scale-95': isWithinRange && hasCheckedIn && !hasCheckedOut && !isSubmitting,
                                        'bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-700 border border-slate-100 dark:border-slate-800 cursor-not-allowed': !isWithinRange || !hasCheckedIn || hasCheckedOut,
                                        'opacity-60 cursor-wait': isSubmitting
                                    }"
                        class="relative aspect-square rounded-[2rem] flex flex-col items-center justify-center gap-1.5 transition-all duration-300 group">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span class="text-[10px] font-black uppercase tracking-widest">Absen Keluar</span>
                        <div x-show="hasCheckedOut"
                            class="absolute inset-0 bg-emerald-500/10 backdrop-blur-[1px] rounded-[2rem] flex items-center justify-center">
                            <div class="bg-emerald-500 rounded-full p-1.5 shadow-sm shadow-emerald-500/30">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Timer Pulang Cepat (Tiny) -->
                <div x-show="hasCheckedIn && !hasCheckedOut" style="display:none;"
                    class="text-center mb-6 px-4 py-2.5 rounded-2xl bg-blue-50/30 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/50 flex flex-col items-center justify-center gap-1">
                    <div class="flex items-center justify-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full"
                            :class="isEarlyLeave ? 'bg-orange-500' : 'bg-emerald-500'"></span>
                        <span class="text-[10px] font-black uppercase tracking-[0.1em]"
                            :class="isEarlyLeave ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-500'" x-text="timeLeftText"></span>
                    </div>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest" x-show="isEarlyLeave" x-text="estimatedOutText"></span>
                </div>

                <!-- Distance Info (Pill Style) -->
                <div
                    class="bg-blue-50/20 dark:bg-blue-900/10 rounded-full px-5 py-3 border border-blue-50 dark:border-blue-900/30 flex items-center justify-between mb-2 relative z-10 transition-colors hover:bg-blue-50/50 dark:hover:bg-blue-900/20">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                        </svg>
                        <span class="text-[9px] font-black text-blue-900/40 dark:text-blue-500/40 uppercase tracking-widest">Jarak</span>
                    </div>
                    <span class="text-sm font-black text-blue-600 dark:text-blue-400" x-text="distanceText">-- m</span>
                </div>
                <p class="text-[9px] text-blue-300 dark:text-blue-700 text-center font-bold tracking-tight">* Maximal
                    {{ $settings['office_radius'] ?? 50 }}m dari kantor.
                </p>
            </div>

            <!-- Right: Stats + History (Compact) -->
            <div class="space-y-5">
                <!-- Stats Grid (Consitent with History) -->
                <div
                    class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] p-8 shadow-[0_15px_50px_rgba(0,0,0,0.03)] relative overflow-hidden transition-all hover:shadow-[0_20px_60px_rgba(0,0,0,0.06)] group">
                    <!-- Deco -->
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-50/50 dark:bg-blue-900/10 rounded-full blur-3xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-colors"></div>
                    
                    <h3 class="text-[11px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-[0.3em] mb-6 text-center italic">
                        Ringkasan <span class="text-blue-600 dark:text-blue-400">Bulanan</span>
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Hadir (Blue) -->
                        <div class="bg-blue-50/50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-900/50 rounded-full p-5 flex flex-col items-center justify-center transition-all hover:scale-[1.05] hover:border-blue-400 group/s1 shadow-sm">
                            <p class="text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1 group-hover/s1:translate-y-[-2px] transition-transform font-mono">Hadir</p>
                            <p class="text-3xl font-black text-blue-600 dark:text-blue-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('status', 'Hadir')->count() }}
                            </p>
                        </div>
                        
                        <!-- Izin (Purple) -->
                        <div class="bg-purple-50/50 dark:bg-purple-900/20 border-2 border-purple-200 dark:border-purple-900/50 rounded-full p-5 flex flex-col items-center justify-center transition-all hover:scale-[1.05] hover:border-purple-400 group/s2 shadow-sm">
                            <p class="text-[10px] font-black text-purple-500 dark:text-purple-400 uppercase tracking-widest mb-1 group-hover/s2:translate-y-[-2px] transition-transform font-mono">Izin</p>
                            <p class="text-3xl font-black text-purple-600 dark:text-purple-300 leading-none">
                                {{ Auth::user()->leaveRequests()->where('status', 'approved')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() }}
                            </p>
                        </div>
                        
                        <!-- Early (Yellow/Amber) -->
                        <div class="bg-amber-50/50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-900/50 rounded-full p-5 flex flex-col items-center justify-center transition-all hover:scale-[1.05] hover:border-amber-400 group/s3 shadow-sm">
                            <p class="text-center text-[10px] font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-1 group-hover/s3:translate-y-[-2px] transition-transform font-mono">Pulang Cepat</p>
                            <p class="text-3xl font-black text-amber-600 dark:text-amber-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('is_pulang_cepat', true)->count() }}
                            </p>
                        </div>
                        
                        <!-- Late (Red) -->
                        <div class="bg-red-50/50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-900/50 rounded-full p-5 flex flex-col items-center justify-center transition-all hover:scale-[1.05] hover:border-red-400 group/s4 shadow-sm">
                            <p class="text-[10px] font-black text-red-500 dark:text-red-400 uppercase tracking-widest mb-1 group-hover/s4:translate-y-[-2px] transition-transform font-mono">Telat</p>
                            <p class="text-3xl font-black text-red-600 dark:text-red-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('status', 'Terlambat')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity (Premium Table Container) -->
                <div
                    class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2rem] overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)]">
                    <div class="px-5 py-3 border-b border-blue-50/60 dark:border-slate-700/60 flex items-center justify-between bg-blue-50/20 dark:bg-slate-900/30">
                        <h2 class="text-[10px] font-black text-blue-900 dark:text-blue-200 uppercase tracking-[0.1em]">Log Terbaru</h2>
                        <a href="{{ route('karyawan.attendance.index') }}"
                            class="text-[8px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest hover:text-blue-600 dark:hover:text-blue-300 transition-colors italic">Perbarui
                            Riwayat &rarr;</a>
                    </div>
                    <div class="overflow-hidden">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-blue-50/50 dark:divide-slate-700/50">
                                @forelse(Auth::user()->attendances()->latest()->take(2)->get() as $attendance)
                                    <tr class="hover:bg-blue-50/10 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-6 h-6 rounded-full flex items-center justify-center bg-blue-50 dark:bg-slate-900 text-[10px]">
                                                    📅
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-blue-950 dark:text-white uppercase tracking-tighter">
                                                        {{ \Carbon\Carbon::parse($attendance->date)->format('d M') }}
                                                    </p>
                                                    <p class="text-[9px] text-blue-400 dark:text-blue-500 font-bold uppercase tracking-widest">
                                                        {{ substr($attendance->check_in, 0, 5) }} -
                                                        {{ $attendance->check_out ? substr($attendance->check_out, 0, 5) : '--:--' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-tighter border
                                                                @if($attendance->status == 'Hadir') bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30
                                                                @elseif($attendance->status == 'Terlambat') bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30
                                                                @else bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/30 @endif">
                                                {{ $attendance->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"
                                            class="px-6 py-8 text-center text-blue-300 text-[9px] font-bold uppercase tracking-widest italic">
                                            Belum ada aktivitas baru</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions (Ultra Rounds Cards) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" @click="showLeaveModal = true"
                        class="group bg-blue-600 rounded-full py-4 px-6 flex items-center justify-between shadow-lg shadow-blue-600/10 transition-all hover:scale-[1.03] active:scale-95 text-white">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black uppercase tracking-[0.1em]">Pengajuan Cepat</span>
                            <span class="text-[9px] text-blue-200 font-bold tracking-widest">Pengajuan Satu Klik</span>
                        </div>
                        <div
                            class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center transition-transform group-hover:rotate-12">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </div>
                    </button>

                    <a href="{{ route('karyawan.leave-requests.index') }}"
                        class="group bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-full py-4 px-6 flex items-center justify-between transition-all hover:bg-blue-50 dark:hover:bg-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 text-blue-900 dark:text-white">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black uppercase tracking-[0.1em]">List Pengajuan</span>
                            <span class="text-[9px] text-blue-400 dark:text-blue-500 font-bold tracking-widest">Riwayat Log</span>
                        </div>
                        <div
                            class="w-8 h-8 bg-blue-100/50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 transition-transform group-hover:rotate-12">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- Redesigned Mini Modal (Compact & Aesthetic) -->
        <div x-show="showLeaveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
            style="display: none;">
            <div x-show="showLeaveModal" x-transition.opacity class="fixed inset-0 bg-blue-950/40 backdrop-blur-[2px]"
                @click="showLeaveModal = false"></div>

            <div x-show="showLeaveModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl relative z-10 w-full max-w-sm overflow-hidden border border-blue-50 dark:border-slate-700 animate-[fadeIn_0.3s_ease-out]">

                <div class="px-6 py-4 border-b border-blue-50/60 dark:border-slate-700/60 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-blue-950 dark:text-white text-xs uppercase tracking-widest">Izin Cepat <span
                                class="text-blue-400 italic">.</span></h3>
                    </div>
                    <button @click="showLeaveModal = false"
                        class="text-blue-300 dark:text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 bg-blue-50 dark:bg-slate-900 p-1.5 rounded-full transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('karyawan.leave-requests.store') }}" method="POST" class="p-5">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="item in ['Sakit', 'Izin Tidak Masuk', 'Izin Masuk Siang', 'Libur', 'Lainnya']">
                                <button type="button" @click="selectedLeaveType = item"
                                    :class="selectedLeaveType === item ? 'bg-blue-600 text-white border-blue-600 shadow-lg' : 'bg-slate-50 dark:bg-slate-900 text-blue-600 dark:text-blue-400 border-slate-100 dark:border-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700'"
                                    class="p-2.5 rounded-2xl border-2 text-[9px] font-black uppercase transition-all tracking-tighter">
                                    <span x-text="item"></span>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="type" x-model="selectedLeaveType" required>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label
                                    class="block font-black text-blue-900/40 dark:text-blue-500/40 text-[8px] uppercase tracking-widest ml-1">Mulai</label>
                                <input type="date" name="start_date" required
                                    class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-full px-4 py-2 text-[10px] font-bold focus:ring-2 focus:ring-blue-500 dark:text-white">
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="block font-black text-blue-900/40 dark:text-blue-500/40 text-[8px] uppercase tracking-widest ml-1">Selesai</label>
                                <input type="date" name="end_date" required
                                    class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-full px-4 py-2 text-[10px] font-bold focus:ring-2 focus:ring-blue-500 dark:text-white">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label
                                class="block font-black text-blue-900/40 dark:text-blue-500/40 text-[8px] uppercase tracking-widest ml-1">Alasan</label>
                            <textarea name="reason" rows="1" required placeholder="..."
                                class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-[1.5rem] px-4 py-2.5 text-[10px] font-medium resize-none focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-slate-600"></textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-full shadow-lg shadow-blue-600/10 transition-all active:scale-95 text-[10px] uppercase tracking-widest">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function attendanceHandler() {
            return {
                locationStatus: 'Mencari Lokasi...',
                isWithinRange: false,
                distanceText: '-- m',
                isSubmitting: false,
                showLeaveModal: false,
                selectedLeaveType: '',
                @php
                    $todayAttendance = Auth::user()->attendances()->whereDate('date', \Carbon\Carbon::today())->first();
                @endphp
                    hasCheckedIn: {{ $todayAttendance ? 'true' : 'false' }},
                hasCheckedOut: {{ ($todayAttendance && $todayAttendance->check_out) ? 'true' : 'false' }},
                checkInTime: '{{ $todayAttendance ? $todayAttendance->check_in : '' }}',
                officeLat: {{ $settings['office_latitude'] ?? -7.232539 }},
                officeLong: {{ $settings['office_longitude'] ?? 112.776228 }},
                officeRadius: {{ $settings['office_radius'] ?? 50 }},
                userLat: null,
                userLong: null,

                isEarlyLeave: false,
                timeLeftText: '',
                estimatedOutText: '',

                init() {
                    this.trackLocation();
                    this.startTimer();
                },

                startTimer() {
                    if (!this.checkInTime || this.hasCheckedOut) return;
                    
                    const [h, m, s] = this.checkInTime.split(':');
                    const checkInDate = new Date();
                    checkInDate.setHours(h, m, s, 0);
                    const estDate = new Date(checkInDate.getTime() + (8 * 60 * 60 * 1000));
                    const estH = String(estDate.getHours()).padStart(2, '0');
                    const estM = String(estDate.getMinutes()).padStart(2, '0');
                    this.estimatedOutText = `Estimasi jam pulang: ${estH}:${estM}`;

                    const updateTime = () => {
                        const now = new Date();
                        checkInDate.setHours(h, m, s, 0);

                        const diffMs = now - checkInDate;
                        const requiredMs = 8 * 60 * 60 * 1000;
                        const remainingMs = requiredMs - diffMs;

                        if (remainingMs > 0) {
                            this.isEarlyLeave = true;
                            // Format remaining time
                            const remH = Math.floor(remainingMs / (1000 * 60 * 60));
                            const remM = Math.floor((remainingMs % (1000 * 60 * 60)) / (1000 * 60));
                            const remS = Math.floor((remainingMs % (1000 * 60)) / 1000);
                            this.timeLeftText = `Sisa ${remH} Jam ${remM} Menit ${remS} Detik Lagi`;
                        } else {
                            this.isEarlyLeave = false;
                            this.timeLeftText = '8 Jam Terpenuhi ✨ (Luar Biasa, Tetap Semangat {{ explode(' ', Auth::user()->name)[0] }}! 💪)';
                        }
                    };
                    updateTime();
                    setInterval(updateTime, 1000); // update every second
                },

                trackLocation() {
                    if (!navigator.geolocation) {
                        this.locationStatus = 'GPS Tidak Mendukung';
                        return;
                    }

                    navigator.geolocation.watchPosition(
                        (position) => {
                            this.userLat = position.coords.latitude;
                            this.userLong = position.coords.longitude;
                            this.calculateDistance();
                        },
                        (err) => {
                            console.error("GPS error:", err);
                            this.locationStatus = 'GPS Mati / Tidak Diizinkan';
                        },
                        { enableHighAccuracy: true }
                    );
                },

                calculateDistance() {
                    const R = 6371e3;
                    const p1 = this.userLat * Math.PI / 180;
                    const p2 = this.officeLat * Math.PI / 180;
                    const dp = (this.officeLat - this.userLat) * Math.PI / 180;
                    const dl = (this.officeLong - this.userLong) * Math.PI / 180;

                    const a = Math.sin(dp / 2) * Math.sin(dp / 2) +
                        Math.cos(p1) * Math.cos(p2) *
                        Math.sin(dl / 2) * Math.sin(dl / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                    const distance = Math.round(c * R);
                    this.distanceText = `${distance} m`;

                    if (distance <= this.officeRadius) {
                        this.isWithinRange = true;
                        this.locationStatus = 'Siap Absen ✨';
                    } else {
                        this.isWithinRange = false;
                        this.locationStatus = `Di Luar Radius (Min ${this.officeRadius}m)`;
                    }
                },

                submitAttendance(type) {
                    if (this.isSubmitting || !this.isWithinRange) return;
                    if (type === 'in' && this.hasCheckedIn) return;
                    if (type === 'out' && (!this.hasCheckedIn || this.hasCheckedOut)) return;

                    const proceed = () => {
                        this.isSubmitting = true;
                        const url = type === 'in' ? "{{ route('karyawan.attendance.store') }}" : "{{ route('karyawan.attendance.checkout') }}";

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lat: this.userLat,
                                long: this.userLong
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Berhasil! ✨',
                                        text: data.message,
                                        icon: 'success',
                                        background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                        color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
                                        confirmButtonColor: '#2563eb',
                                        customClass: {
                                            popup: 'rounded-2xl',
                                            confirmButton: 'rounded-lg px-6'
                                        }
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        title: 'Gagal',
                                        text: data.message || 'Gagal, coba lagi.',
                                        icon: 'error',
                                        background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                        color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
                                        confirmButtonColor: '#2563eb',
                                        customClass: {
                                            popup: 'rounded-2xl',
                                            confirmButton: 'rounded-lg px-6'
                                        }
                                    });
                                }
                            })
                            .catch(err => {
                                console.error("Submit error:", err);
                                Swal.fire('Error', 'Koneksi bermasalah.', 'error');
                            })
                            .finally(() => {
                                this.isSubmitting = false;
                            });
                    };

                    if (type === 'out' && this.isEarlyLeave) {
                        Swal.fire({
                            title: 'Pulang Cepat?',
                            text: 'Anda belum bekerja 8 jam hari ini. Jika absen pulang sekarang, Anda akan tercatat "Pulang Cepat". Lanjutkan?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#94a3b8',
                            confirmButtonText: 'Ya, Pulang Cepat',
                            cancelButtonText: 'Batal',
                            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
                        }).then((result) => {
                            if (result.isConfirmed) proceed();
                        });
                    } else {
                        proceed();
                    }
                }
            }
        }
    </script>
@endsection