@extends('layouts.master')
@section('title', 'Dashboard Karyawan')
@section('content')
    <div x-data="attendanceHandler()" class="max-w-5xl mx-auto space-y-6 animate-fade-in">
        <!-- Top 3 Penjualan Ramayana Bulan Ini -->
        @php
            $rankMeta = [
                1 => ['color' => '#E87A10', 'bg' => 'rgba(232,122,16,0.10)'],
                2 => ['color' => '#6B7FA3', 'bg' => 'rgba(107,127,163,0.10)'],
                3 => ['color' => '#A0724A', 'bg' => 'rgba(160,114,74,0.10)'],
            ];
        @endphp
        <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
            {{-- Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; padding: 10px 14px 8px 14px; border-bottom: 1px solid rgba(148,163,184,0.15);">
                <div style="display:flex; align-items:center; gap:7px;">
                    <span style="font-size:14px; line-height:1;">🏆</span>
                    <div>
                        <div style="font-size:10px; font-weight:800; letter-spacing:0.08em; text-transform:uppercase; line-height:1;" class="text-slate-800 dark:text-slate-100">
                            3 Penjualan Terbaik Bulan Ini
                        </div>
                        <div style="font-size:9px; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; margin-top:2px; line-height:1;" class="text-slate-400 dark:text-slate-500">
                            Berdasarkan Qty &middot; {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                        </div>
                    </div>
                </div>
                <span style="font-size:14px; opacity:0.8;" class="text-orange-400 dark:text-orange-300">📈</span>
            </div>

            {{-- 3 Persons Row --}}
            @if(isset($top3Sales) && $top3Sales->count() > 0)
                <div style="display:flex; align-items:center; padding: 10px 8px;">
                    @foreach($top3Sales as $i => $sale)
                        @php
                            $rank     = $i + 1;
                            $user     = $sale->user;
                            $name     = $user ? explode(' ', $user->name)[0] : 'N/A';
                            $initials = $user ? strtoupper(substr($user->name, 0, 1)) : '?';
                            $meta     = $rankMeta[$rank] ?? ['color' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.1)'];
                        @endphp

                        {{-- Divider between cards --}}
                        @if($i > 0)
                            <div style="width:1px; align-self:stretch; background:rgba(148,163,184,0.15); margin:0 4px; flex-shrink:0;"></div>
                        @endif

                        {{-- Person card --}}
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:5px; padding:6px 4px; border-radius:12px; background:{{ $meta['bg'] }};">
                            {{-- Avatar + rank badge --}}
                            <div style="position:relative; display:inline-flex;">
                                <div style="padding:2px; border-radius:50%; background:{{ $meta['color'] }}; flex-shrink:0;">
                                    @if($user && $user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid white; display:block;">
                                    @else
                                        <div style="width:38px; height:38px; border-radius:50%; background:{{ $meta['color'] }}; color:#fff; font-weight:800; font-size:15px; display:flex; align-items:center; justify-content:center; border:2px solid white;">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                </div>
                                {{-- Rank number badge --}}
                                <span style="position:absolute; bottom:-3px; right:-3px; width:16px; height:16px; border-radius:50%; background:{{ $meta['color'] }}; color:#fff; font-size:9px; font-weight:900; display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.25);">
                                    {{ $rank }}
                                </span>
                            </div>

                            {{-- Name --}}
                            <span style="font-size:11px; font-weight:700; text-align:center; max-width:72px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block;" class="text-slate-800 dark:text-slate-100" title="{{ $user?->name ?? 'N/A' }}">
                                {{ $name }}
                            </span>

                            {{-- Nama Lokasi / Counter --}}
                            <span style="font-size:7px; font-weight:700; color:{{ $meta['color'] }}; line-height:1; letter-spacing:0.02em; text-align:center; white-space:nowrap; display:block;"
                                  title="{{ $user?->location?->name ?? 'Lokasi tidak diset' }}">
                                {{ $user?->location?->name ?? '-' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="display:flex; align-items:center; justify-content:center; gap:8px; padding:16px 14px;">
                    <span style="font-size:22px;">🏆</span>
                    <p class="text-xs text-slate-400 dark:text-slate-500" style="font-weight:500;">Belum ada data penjualan bulan ini.</p>
                </div>
            @endif
        </div>

        @php
            $upcomingHolidays = \App\Models\Holiday::whereDate('date', '>=', \Carbon\Carbon::today())
                ->where(function ($q) {
                    $q->whereNull('division_id')->orWhere('division_id', Auth::user()->division_id);
                })
                ->orderBy('date', 'asc')
                ->get();
        @endphp

        @if($upcomingHolidays->count() > 0)
            <!-- Holiday Announcement Banner -->
            <div class="space-y-3">
                @foreach($upcomingHolidays as $holiday)
                    @php
                        $isToday = \Carbon\Carbon::parse($holiday->date)->isToday();
                        $dateStr = \Carbon\Carbon::parse($holiday->date)->translatedFormat('d F Y');
                        $bgStyle = $isToday ? 'background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);' : 'background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);';
                        $shadowColor = $isToday ? 'rgba(99, 102, 241, 0.15)' : 'rgba(59, 130, 246, 0.15)';
                    @endphp
                    <div class="rounded-2xl p-4 md:p-5 text-white flex items-center justify-between shadow-lg"
                        style="{{ $bgStyle }} box-shadow: 0 10px 25px -5px {{ $shadowColor }}; animate: fadeIn 0.4s ease-out;">
                        <div class="flex items-center space-x-4">
                            <div class="bg-white/10 p-2.5 rounded-xl backdrop-blur-md shadow-inner border border-white/10">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-widest text-white/90">Hari Libur</h3>
                                <p class="text-sm text-white font-medium mt-0.5">
                                    @if($isToday)
                                        Hari ini ditetapkan sebagai hari libur: <strong
                                            class="text-white font-bold">{{ $holiday->description }}</strong>
                                    @else
                                        Tanggal <strong class="text-white font-bold">{{ $dateStr }}</strong> ditetapkan sebagai <strong
                                            class="text-white font-bold">{{ $holiday->description }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Main Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            <!-- Left: Attendance Card -->
            <div
                class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-6 md:p-8 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[360px]">
                <!-- Accent Decoration -->
                <div class="absolute -left-10 -top-10 w-24 h-24 bg-blue-50/50 dark:bg-blue-950/10 rounded-full blur-2xl">
                </div>

                <div class="text-center relative z-10">
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Status
                        Kehadiran</h2>
                    <div class="inline-block px-4 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-[10px] font-bold uppercase tracking-widest"
                        x-text="locationStatus">Mencari Lokasi...</div>
                </div>

                <!-- Clock In / Clock Out Buttons -->
                <div class="grid grid-cols-2 gap-4 max-w-[320px] mx-auto my-6 w-full relative z-10">
                    <!-- Clock In -->
                    <button @click="submitAttendance('in')" :disabled="!isWithinRange || isSubmitting || hasCheckedIn"
                        :class="{
                                        'bg-blue-600 text-white hover:bg-blue-700 shadow-md shadow-blue-500/10 active:scale-95': isWithinRange && !hasCheckedIn && !isSubmitting,
                                        'bg-slate-50 dark:bg-slate-900/60 text-slate-350 dark:text-slate-700 border border-slate-200/30 dark:border-slate-850 cursor-not-allowed': !isWithinRange || hasCheckedIn,
                                        'opacity-60 cursor-wait': isSubmitting
                                    }"
                        class="relative aspect-square rounded-2xl flex flex-col items-center justify-center gap-2 transition-all duration-350 group cursor-pointer">
                        <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Absen Masuk</span>
                        <div x-show="hasCheckedIn"
                            class="absolute inset-0 bg-emerald-500/5 backdrop-blur-[0.5px] rounded-2xl flex items-center justify-center">
                            <div class="bg-emerald-500 rounded-full p-2 shadow-md shadow-emerald-500/30">
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
                                        'bg-orange-500 text-white hover:bg-orange-600 shadow-md shadow-orange-500/10 active:scale-95': isWithinRange && hasCheckedIn && !hasCheckedOut && !isSubmitting,
                                        'bg-slate-50 dark:bg-slate-900/60 text-slate-350 dark:text-slate-700 border border-slate-200/30 dark:border-slate-850 cursor-not-allowed': !isWithinRange || !hasCheckedIn || hasCheckedOut,
                                        'opacity-60 cursor-wait': isSubmitting
                                    }"
                        class="relative aspect-square rounded-2xl flex flex-col items-center justify-center gap-2 transition-all duration-350 group cursor-pointer">
                        <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Absen Keluar</span>
                        <div x-show="hasCheckedOut"
                            class="absolute inset-0 bg-emerald-500/5 backdrop-blur-[0.5px] rounded-2xl flex items-center justify-center">
                            <div class="bg-emerald-500 rounded-full p-2 shadow-md shadow-emerald-500/30">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Timer Pulang Cepat -->
                <div x-show="hasCheckedIn && !hasCheckedOut" style="display:none;"
                    class="text-center mb-4 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 flex flex-col items-center justify-center gap-1">
                    <div class="flex items-center justify-center gap-2">
                        <span class="w-2 h-2 rounded-full animate-pulse"
                            :class="isEarlyLeave ? 'bg-orange-500' : 'bg-emerald-500'"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider"
                            :class="isEarlyLeave ? 'text-orange-500' : 'text-emerald-500'" x-text="timeLeftText"></span>
                    </div>
                    <span class="text-[9px] font-semibold text-slate-450 uppercase tracking-widest" x-show="isEarlyLeave"
                        x-text="estimatedOutText"></span>
                </div>

                <!-- Distance Info -->
                <div
                    class="bg-slate-50 dark:bg-slate-900 rounded-xl px-5 py-3.5 border border-slate-250/20 dark:border-slate-800/80 flex items-center justify-between mb-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                        </svg>
                        <span
                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Jarak
                            ke Kantor</span>
                    </div>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="distanceText">-- m</span>
                </div>
                @if(Auth::user()->role->slug == 'karyawan_ramayana')
                    <p class="text-[9px] text-slate-400 dark:text-slate-600 text-center font-medium tracking-wide">
                        * Radius jangkauan:
                        @forelse(Auth::user()->all_locations as $loc)
                            {{ $loc->name }} ({{ $loc->radius }}m){{ !$loop->last ? ',' : '' }}
                        @empty
                            Tidak ada lokasi ditugaskan
                        @endforelse
                    </p>
                @else
                    <p class="text-[9px] text-slate-400 dark:text-slate-600 text-center font-medium tracking-wide">* Radius
                        jangkauan maksimal {{ $settings['office_radius'] ?? 50 }}m.</p>
                @endif
            </div>

            <!-- Right: Stats + History -->
            <div class="space-y-6">
                <!-- Stats Grid -->
                <div
                    class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-6 md:p-8 shadow-sm relative overflow-hidden group">
                    <h3
                        class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.25em] mb-6 text-center">
                        Ringkasan Bulan Ini</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Hadir (Blue) -->
                        <div
                            class="bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100/60 dark:border-blue-900/30 rounded-2xl p-4 flex flex-col items-center justify-center transition-all hover:scale-[1.02] shadow-sm">
                            <p
                                class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-1.5">
                                Hadir</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('status', 'Hadir')->count() }}
                            </p>
                        </div>

                        <!-- Izin (Purple) -->
                        <div
                            class="bg-purple-50/40 dark:bg-purple-950/20 border border-purple-100/60 dark:border-purple-900/30 rounded-2xl p-4 flex flex-col items-center justify-center transition-all hover:scale-[1.02] shadow-sm">
                            <p
                                class="text-[10px] font-bold text-purple-500 dark:text-purple-400 uppercase tracking-wider mb-1.5">
                                Izin</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-300 leading-none">
                                {{ Auth::user()->leaveRequests()->where('status', 'approved')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() }}
                            </p>
                        </div>

                        <!-- Early (Yellow/Amber) -->
                        <div
                            class="bg-amber-50/40 dark:bg-amber-950/20 border border-amber-100/60 dark:border-amber-900/30 rounded-2xl p-4 flex flex-col items-center justify-center transition-all hover:scale-[1.02] shadow-sm">
                            <p
                                class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-wider mb-1.5">
                                Pulang Cepat</p>
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('is_pulang_cepat', true)->count() }}
                            </p>
                        </div>

                        <!-- Late (Red) -->
                        <div
                            class="bg-rose-50/40 dark:bg-rose-950/20 border border-rose-100/60 dark:border-rose-900/30 rounded-2xl p-4 flex flex-col items-center justify-center transition-all hover:scale-[1.02] shadow-sm">
                            <p
                                class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mb-1.5">
                                Terlambat</p>
                            <p class="text-2xl font-bold text-rose-600 dark:text-rose-300 leading-none">
                                {{ Auth::user()->attendances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->where('status', 'Terlambat')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div
                    class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
                    <div
                        class="px-5 py-4 border-b border-slate-200/50 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Log
                            Riwayat Terbaru</h2>
                        <a href="{{ route(Auth::user()->role->slug . '.attendance.index') }}"
                            class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest hover:text-blue-600 dark:hover:text-blue-300 transition-colors">Semua
                            Riwayat &rarr;</a>
                    </div>
                    <div class="overflow-hidden">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                @forelse(Auth::user()->attendances()->latest()->take(2)->get() as $attendance)
                                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 dark:bg-slate-900 text-xs">
                                                    📅
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('d M Y') }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                                        {{ substr($attendance->check_in, 0, 5) }} -
                                                        {{ $attendance->check_out ? substr($attendance->check_out, 0, 5) : '--:--' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <span
                                                class="px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider border
                                                                        @if($attendance->status == 'Hadir') bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450 border-emerald-100 dark:border-emerald-900/20
                                                                        @elseif($attendance->status == 'Terlambat') bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-450 border-rose-100 dark:border-rose-900/20
                                                                        @else bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-450 border-blue-100 dark:border-blue-900/20 @endif">
                                                {{ $attendance->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"
                                            class="px-6 py-8 text-center text-slate-400 dark:text-slate-650 text-xs font-medium italic">
                                            Belum ada aktivitas baru
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="button" @click="showLeaveModal = true"
                        class="group bg-blue-600 hover:bg-blue-700 text-white rounded-2xl py-4 px-6 flex items-center justify-between shadow-md shadow-blue-500/10 active:scale-95 transition-all cursor-pointer">
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-bold uppercase tracking-wider">Pengajuan Cepat</span>
                            <span class="text-[9px] text-blue-200 font-medium tracking-wider mt-0.5">Izin & Lupa
                                Absen</span>
                        </div>
                        <div
                            class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center transition-transform group-hover:rotate-12 border border-white/5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </div>
                    </button>

                    <a href="{{ route(Auth::user()->role->slug . '.leave-requests.index') }}"
                        class="group bg-white dark:bg-dark-card border border-slate-200/60 dark:border-slate-800 rounded-2xl py-4 px-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-900/60 shadow-sm active:scale-95 transition-all">
                        <div class="flex flex-col text-left">
                            <span
                                class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Riwayat
                                Pengajuan</span>
                            <span
                                class="text-[9px] text-slate-450 dark:text-slate-500 font-medium tracking-wider mt-0.5">Log
                                Pengajuan Izin</span>
                        </div>
                        <div
                            class="w-8 h-8 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-500 dark:text-slate-400 transition-transform group-hover:rotate-12 border border-slate-250/20 dark:border-slate-850">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- Redesigned Mini Modal -->
        <div x-show="showLeaveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
            style="display: none;">
            <div x-show="showLeaveModal" x-transition.opacity class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="showLeaveModal = false"></div>

            <div x-show="showLeaveModal" x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-dark-card rounded-2xl shadow-xl relative z-10 w-full max-w-md overflow-hidden border border-slate-200/60 dark:border-slate-800/80 max-h-[90vh] flex flex-col">

                <div
                    class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm uppercase tracking-wider">Form Pengajuan
                            Cepat</h3>
                    </div>
                    <button @click="showLeaveModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white bg-slate-100 dark:bg-slate-900 p-1.5 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route(Auth::user()->role->slug . '.leave-requests.store') }}" method="POST"
                    class="p-6 overflow-y-auto space-y-4">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Pilih
                                Jenis Izin</label>
                            <div class="grid grid-cols-2 gap-2">
                                <template
                                    x-for="item in ['Sakit', 'Izin Tidak Masuk', 'Izin Masuk Siang', 'Libur', 'Lupa Absen']">
                                    <button type="button" @click="selectedLeaveType = item"
                                        :class="selectedLeaveType === item ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-250/20 dark:border-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800'"
                                        class="p-2.5 rounded-xl border text-[10px] font-bold uppercase transition-all tracking-wider text-center cursor-pointer">
                                        <span x-text="item"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="type" x-model="selectedLeaveType" required>
                        </div>

                        <template x-if="selectedLeaveType === 'Lupa Absen'">
                            <div
                                class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200/50 dark:border-slate-850">
                                <label
                                    class="block font-bold text-slate-400 dark:text-slate-500 text-[9px] uppercase tracking-wider">Jenis
                                    Lupa Absen</label>
                                <input type="hidden" name="sub_type" x-model="subType"
                                    :required="selectedLeaveType === 'Lupa Absen'">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="subType = 'Absen Masuk'"
                                        :class="subType === 'Absen Masuk' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 border border-slate-200 dark:border-slate-700'"
                                        class="p-2.5 rounded-xl text-[10px] font-bold cursor-pointer">Masuk</button>
                                    <button type="button" @click="subType = 'Absen Pulang'"
                                        :class="subType === 'Absen Pulang' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 border border-slate-200 dark:border-slate-700'"
                                        class="p-2.5 rounded-xl text-[10px] font-bold cursor-pointer">Pulang</button>
                                </div>
                                <div class="space-y-1 mt-2.5" x-show="subType">
                                    <label
                                        class="block font-bold text-slate-400 dark:text-slate-500 text-[9px] uppercase tracking-wider">Jam
                                        Kejadian</label>
                                    <input type="time" name="time_start" :required="selectedLeaveType === 'Lupa Absen'"
                                        class="focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500">
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedLeaveType === 'Izin Masuk Siang'">
                            <div
                                class="grid grid-cols-2 gap-3 p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200/50 dark:border-slate-850">
                                <div class="space-y-1">
                                    <label
                                        class="block font-bold text-slate-400 dark:text-slate-500 text-[9px] uppercase tracking-wider">Mulai
                                        Jam</label>
                                    <input type="time" name="time_start"
                                        :required="selectedLeaveType === 'Izin Masuk Siang'">
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="block font-bold text-slate-400 dark:text-slate-500 text-[9px] uppercase tracking-wider">Sampai
                                        Jam</label>
                                    <input type="time" name="time_end" :required="selectedLeaveType === 'Izin Masuk Siang'">
                                </div>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label
                                    class="block font-bold text-slate-400 dark:text-slate-550 text-[9px] uppercase tracking-wider">Tanggal
                                    Mulai</label>
                                <input type="date" name="start_date" required :min="minStartDate" :max="maxStartDate" x-model="startDate">
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="block font-bold text-slate-400 dark:text-slate-550 text-[9px] uppercase tracking-wider">Tanggal
                                    Selesai</label>
                                <input type="date" name="end_date" required :min="minEndDate">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-400 dark:text-slate-550 text-[9px] uppercase tracking-wider">Alasan
                                Pengajuan</label>
                            <textarea name="reason" rows="2.5" required placeholder="Tuliskan keterangan/alasan lengkap..."
                                class="rounded-xl"></textarea>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl shadow-md shadow-blue-500/10 transition-all active:scale-95 text-xs uppercase tracking-widest cursor-pointer">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>/div>

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
                subType: '',
                today: '{{ date('Y-m-d') }}',
                startOfMonth: '{{ date('Y-m-01') }}',
                isStaffKantor: {{ Auth::user()->role->slug === 'karyawan' ? 'true' : 'false' }},
                startDate: '',
                get minStartDate() { return this.selectedLeaveType === 'Lupa Absen' ? this.startOfMonth : (this.isStaffKantor ? this.startOfMonth : this.today); },
                get maxStartDate() { return this.selectedLeaveType === 'Lupa Absen' ? this.today : ''; },
                get minEndDate() { return this.startDate ? this.startDate : (this.isStaffKantor ? this.startOfMonth : this.today); },
                @php
                    $todayAttendance = Auth::user()->attendances()->whereDate('date', \Carbon\Carbon::today())->first();

                    $userDivision = Auth::user()->division ? strtolower(trim(Auth::user()->division->name)) : '';
                    if (str_contains($userDivision, 'live streaming')) {
                        if ((!$todayAttendance || !$todayAttendance->check_in) && \Carbon\Carbon::now()->format('H:i') <= '08:30') {
                            $overnightAttendance = Auth::user()->attendances()
                                ->whereDate('date', \Carbon\Carbon::yesterday())
                                ->whereNotNull('check_in')
                                ->whereNull('check_out')
                                ->first();
                            if ($overnightAttendance) {
                                $todayAttendance = $overnightAttendance;
                            }
                        }
                    }

                    $todayLupaAbsen = Auth::user()->leaveRequests()
                        ->where('type', 'Lupa Absen')
                        ->whereIn('status', ['pending', 'approved'])
                        ->whereDate('start_date', \Carbon\Carbon::today())
                        ->latest()
                        ->first();
                @endphp
                            hasCheckedIn: {{ $todayAttendance ? 'true' : 'false' }},
                hasCheckedOut: {{ ($todayAttendance && $todayAttendance->check_out) ? 'true' : 'false' }},
                checkInTime: '{{ $todayAttendance ? $todayAttendance->check_in : '' }}',
                requiredHours: {{ Auth::user()->role->slug === 'karyawan_ramayana' ? 7 : 8 }},
                locations: [
                    @if(Auth::user()->role->slug == 'karyawan_ramayana')
                        @foreach(Auth::user()->all_locations as $loc)
                                                {
                            name: {!! json_encode($loc->name) !!},
                            lat: {{ $loc->latitude }},
                            long: {{ $loc->longitude }},
                            radius: {{ $loc->radius }}
                                                },
                        @endforeach
                    @else
                        {
                            name: 'Kantor Pusat',
                            lat: {{ $settings['office_latitude'] ?? -7.232539 }},
                            long: {{ $settings['office_longitude'] ?? 112.776228 }},
                            radius: {{ $settings['office_radius'] ?? 50 }}
                                                }
                    @endif
                            ],
                userLat: null,
                userLong: null,
                gpsAlertShown: false,

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
                    checkInDate.setHours(h, m, s || 0, 0);
                    const estDate = new Date(checkInDate.getTime() + (this.requiredHours * 60 * 60 * 1000));
                    const estH = String(estDate.getHours()).padStart(2, '0');
                    const estM = String(estDate.getMinutes()).padStart(2, '0');
                    this.estimatedOutText = `Estimasi jam pulang: ${estH}:${estM}`;

                    const updateTime = () => {
                        const now = new Date();
                        checkInDate.setHours(h, m, s || 0, 0);

                        const diffMs = now - checkInDate;
                        const requiredMs = this.requiredHours * 60 * 60 * 1000;
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
                            this.timeLeftText = `${this.requiredHours} Jam Terpenuhi ✨ (Luar Biasa, Tetap Semangat {{ explode(' ', Auth::user()->name)[0] }}! 💪)`;
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
                            this.gpsAlertShown = false; // Reset status alert saat berhasil mendapat lokasi
                            this.calculateDistance();
                        },
                        (err) => {
                            console.error("GPS error:", err);

                            let title = '';
                            let text = '';

                            if (err.code === 1) { // PERMISSION_DENIED
                                this.locationStatus = 'IZIN LOKASI DITOLAK';
                                title = 'Izin Lokasi Diblokir 🔒';
                                text = 'Harap Aktifkan Lokasi anda di atas bar HP anda, kalau memang belum bisa silahkan Buka Pengaturan HP -> Aplikasi -> Kelola Aplikasi -> Cari Chrome -> Izin Aplikasi -> Aktifkan izin Lokasi.';
                            } else if (err.code === 3) { // TIMEOUT
                                this.locationStatus = 'TIMEOUT PENCARIAN LOKASI';
                                title = 'Pencarian Lokasi Timeout ⏳';
                                text = 'Waktu pencarian lokasi habis. Pastikan koneksi internet stabil dan silakan berpindah ke tempat terbuka (tidak terhalang gedung tebal) agar sinyal GPS satelit bisa mendeteksi posisi Anda.';
                            } else { // POSITION_UNAVAILABLE or other errors
                                this.locationStatus = 'GPS MATI / TIDAK AKTIF';
                                title = 'Aktifkan GPS / Lokasi Di HP Anda 📍';
                                text = 'Layanan lokasi (GPS) pada HP Anda tidak aktif atau tidak dapat dideteksi. Harap aktifkan GPS (Layanan Lokasi) di menu bar atas HP Anda.';
                            }

                            if (!this.gpsAlertShown) {
                                this.gpsAlertShown = true;
                                Swal.fire({
                                    title: title,
                                    text: text,
                                    icon: 'warning',
                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
                                    confirmButtonColor: '#2563eb',
                                    customClass: {
                                        popup: 'rounded-2xl',
                                        confirmButton: 'rounded-lg px-6'
                                    }
                                });
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000, // Timeout 10 detik agar tidak menggantung selamanya
                            maximumAge: 5000 // Mengizinkan cache lokasi jika baru berumur maksimal 5 detik
                        }
                    );
                },

                calculateDistance() {
                    if (this.locations.length === 0) {
                        this.distanceText = '-- m';
                        this.isWithinRange = false;
                        this.locationStatus = 'Tidak ada lokasi absen ditugaskan';
                        return;
                    }

                    const R = 6371e3;
                    const p1 = this.userLat * Math.PI / 180;

                    let minDistance = null;
                    let isWithinAny = false;
                    let nearestRadius = 0;

                    this.locations.forEach(loc => {
                        const p2 = loc.lat * Math.PI / 180;
                        const dp = (loc.lat - this.userLat) * Math.PI / 180;
                        const dl = (loc.long - this.userLong) * Math.PI / 180;

                        const a = Math.sin(dp / 2) * Math.sin(dp / 2) +
                            Math.cos(p1) * Math.cos(p2) *
                            Math.sin(dl / 2) * Math.sin(dl / 2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        const distance = Math.round(c * R);

                        if (minDistance === null || distance < minDistance) {
                            minDistance = distance;
                            nearestRadius = loc.radius;
                        }

                        if (distance <= loc.radius) {
                            isWithinAny = true;
                        }
                    });

                    this.distanceText = `${minDistance} m`;

                    if (isWithinAny) {
                        this.isWithinRange = true;
                        this.locationStatus = 'Siap Absen ✨';
                    } else {
                        this.isWithinRange = false;
                        this.locationStatus = `Di Luar Radius (Min ${nearestRadius}m)`;
                    }
                },

                submitAttendance(type) {

                    if (this.isSubmitting || !this.isWithinRange) return;
                    if (type === 'in' && this.hasCheckedIn) return;
                    if (type === 'out' && (!this.hasCheckedIn || this.hasCheckedOut)) return;

                    const proceed = () => {
                        this.isSubmitting = true;
                        const url = type === 'in' ? "{{ route(Auth::user()->role->slug . '.attendance.store') }}" : "{{ route(Auth::user()->role->slug . '.attendance.checkout') }}";

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                lat: this.userLat,
                                long: this.userLong
                            })
                        })
                            .then(async response => {
                                if (!response.ok) {
                                    const text = await response.text();
                                    throw new Error('HTTP ' + response.status + ' - ' + (text.substring(0, 100).replace(/(<([^>]+)>)/gi, "")));
                                }
                                return response.json();
                            })
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
                                Swal.fire('Error Detail', 'Gagal: ' + err.message, 'error');
                            })
                            .finally(() => {
                                this.isSubmitting = false;
                            });
                    };

                    if (type === 'out' && this.isEarlyLeave) {
                        Swal.fire({
                            title: 'Pulang Cepat?',
                            text: `Anda belum bekerja ${this.requiredHours} jam hari ini. Jika absen pulang sekarang, Anda akan tercatat "Pulang Cepat". Lanjutkan?`,
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