<!DOCTYPE html>
<html lang="id" 
      x-data="{ 
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
      }" 
      :class="{ 'dark': darkMode }">
<head>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - JMN Karyawan</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white dark:bg-slate-950 text-blue-950 dark:text-slate-200 flex min-h-screen transition-colors duration-300">

    <!-- Sidebar Kiri (desktop) dengan toggle collapse -->
    <div x-data="{ collapsed: false }" 
         :class="collapsed ? 'w-20' : 'w-64'"
         class="hidden lg:flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out bg-[#20609b] dark:bg-slate-900 text-white border-r dark:border-slate-800">
        
        <!-- Logo / Brand -->
        <div class="px-4 pt-6 pb-5 border-b border-white/10 flex items-center" :class="collapsed ? 'justify-center' : 'space-x-3'">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
            </div>
            <div x-show="!collapsed" x-transition class="min-w-0 pt-1">
                <h1 class="text-lg font-bold text-white leading-none tracking-wider uppercase">{{ Auth::user()->role->name }}</h1>
            </div>
        </div>

        <!-- Role Badge -->
        <div class="px-4 py-3" x-show="!collapsed" x-transition>
            <div class="bg-white/10 rounded-lg px-3 py-2 flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0 overflow-hidden border border-white/10 shadow-inner">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-[11px] font-bold text-white uppercase">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] text-blue-200/60 font-medium uppercase tracking-wider">{{ Auth::user()->role->name }}</p>
                </div>
            </div>
        </div>
        <div class="px-4 py-3 flex justify-center" x-show="collapsed" x-transition>
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center overflow-hidden border border-white/10 shadow-inner" title="{{ Auth::user()->name }}">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-xs font-bold text-white uppercase">{{ substr(Auth::user()->name, 0, 1) }}</span>
                @endif
            </div>
        </div>

        <!-- Menu Label -->
        <div class="px-4 pb-1" x-show="!collapsed" x-transition>
            <p class="text-[9px] font-bold text-blue-200/40 uppercase tracking-widest">Menu</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 space-y-0.5 overflow-y-auto py-1">

            <!-- Beranda (semua role) -->
            <a href="{{ route(Auth::user()->role->slug . '.dashboard') }}"
               class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('*.dashboard') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
               :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
               title="Beranda">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Beranda</span>
            </a>

            {{-- ============ SUPER ADMIN ============ --}}
            @if(Auth::user()->role->slug == 'super-admin')
                <a href="{{ route('super-admin.users.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('super-admin.users.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Pengguna">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Pengguna</span>
                </a>
                <a href="{{ route('super-admin.settings.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('super-admin.settings.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Pengaturan">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Pengaturan</span>
                </a>
                <a href="{{ route('super-admin.holidays.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('super-admin.holidays.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Hari Libur">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Hari Libur</span>
                </a>
            @endif

            {{-- ============ HRD ============ --}}
            @if(Auth::user()->role->slug == 'hrd')
                <a href="{{ route('hrd.attendance.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('hrd.attendance.index') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Monitor Absensi">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Monitor Absensi</span>
                </a>
                <a href="{{ route('hrd.attendance.recap') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('hrd.attendance.recap') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Rekap Laporan">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Rekap Laporan</span>
                </a>
            @endif

            {{-- ============ PIC ============ --}}
            @if(Auth::user()->role->slug == 'pic')
                <a href="{{ route('pic.leave-approvals.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('pic.leave-approvals.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Persetujuan Izin">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Persetujuan Izin</span>
                </a>
                <a href="{{ route('pic.employees.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('pic.employees.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Data Tim">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Data Tim</span>
                </a>
                <a href="{{ route('pic.reports.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('pic.reports.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Laporan Absensi">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Laporan Absensi</span>
                </a>
            @endif

            {{-- ============ KARYAWAN ============ --}}
            @if(Auth::user()->role->slug == 'karyawan')
                <a href="{{ route('karyawan.leave-requests.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('karyawan.leave-requests.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Pengajuan Izin">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Pengajuan Izin</span>
                </a>
                <a href="{{ route('karyawan.attendance.index') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('karyawan.attendance.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Riwayat Absen">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Riwayat Absen</span>
                </a>
                <a href="{{ route('karyawan.profile.edit') }}"
                   class="flex items-center rounded-lg transition-all duration-200 {{ request()->routeIs('*.profile.*') ? 'bg-white/20 text-white shadow-sm' : 'text-blue-100/70 hover:bg-white/10 hover:text-white' }}"
                   :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                   title="Biodata Diri">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Biodata Diri</span>
                </a>
            @endif

            <!-- Divider -->
            <div class="border-t border-white/10 my-2"></div>

            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="flex items-center w-full rounded-lg transition-all duration-200 text-red-300/70 hover:bg-red-500/10 hover:text-red-300"
                        :class="collapsed ? 'justify-center px-2 py-2.5' : 'space-x-2.5 px-3 py-2.5'"
                        title="Keluar">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="text-[13px] font-medium" x-show="!collapsed" x-transition>Keluar</span>
                </button>
            </form>
        </nav>

        <!-- Toggle Collapse Button (panah) -->
        <div class="py-6 flex" :class="collapsed ? 'justify-center' : 'justify-center'">
            <button @click="collapsed = !collapsed"
                    class="w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-all duration-300"
                    :title="collapsed ? 'Buka Menu' : 'Kecilkan Menu'">
                <svg class="w-5 h-5 transition-transform duration-300" 
                     :class="collapsed ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 pb-16 lg:pb-0 bg-white dark:bg-slate-950 relative">
        <!-- Top Header -->
        <header class="h-16 border-b border-blue-200 dark:border-slate-800 flex items-center justify-between px-5 lg:px-8 shrink-0 bg-white dark:bg-slate-900/50 backdrop-blur-md shadow-sm sticky top-0 z-30">
            <div class="flex items-center space-x-3">
                <span class="text-sm font-semibold text-blue-900 dark:text-blue-100">Manajemen Sistem Karyawan</span>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                <button @click="toggleTheme()" class="p-2 rounded-xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-slate-700 transition-all active:scale-95" title="Ganti Tema">
                    <template x-if="!darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </template>
                    <template x-if="darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </template>
                </button>

                <div class="flex items-center space-x-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-medium text-blue-600/80 dark:text-blue-400/80 hidden sm:inline">Sistem Aktif</span>
                </div>
                <div class="bg-blue-50 dark:bg-slate-800 px-4 py-2 rounded-lg border border-blue-200 dark:border-slate-700">
                    <span id="live-clock" class="text-sm font-bold text-blue-800 dark:text-blue-300 tabular-nums">00:00:00</span>
                    <span class="text-xs font-medium text-blue-600/80 dark:text-blue-400/80 ml-1">WIB</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-y-auto w-full">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-blue-200 dark:border-slate-800 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-around h-16 px-1">
            <a href="{{ route(Auth::user()->role->slug . '.dashboard') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('*.dashboard') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-semibold mt-0.5">Beranda</span>
            </a>

            @if(Auth::user()->role->slug == 'super-admin')
                <a href="{{ route('super-admin.users.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('super-admin.users.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Pengguna</span>
                </a>
                <a href="{{ route('super-admin.settings.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('super-admin.settings.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Pengaturan</span>
                </a>
                <a href="{{ route('super-admin.holidays.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('super-admin.holidays.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Hari Libur</span>
                </a>
            @endif

            @if(Auth::user()->role->slug == 'hrd')
                <a href="{{ route('hrd.attendance.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('hrd.attendance.index') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Absensi</span>
                </a>
                <a href="{{ route('hrd.attendance.recap') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('hrd.attendance.recap') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Rekap</span>
                </a>
            @endif

            @if(Auth::user()->role->slug == 'pic')
                <a href="{{ route('pic.leave-approvals.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('pic.leave-approvals.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Izin</span>
                </a>
                <a href="{{ route('pic.employees.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('pic.employees.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Tim</span>
                </a>
                <a href="{{ route('pic.reports.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('pic.reports.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Laporan</span>
                </a>
            @endif

            @if(Auth::user()->role->slug == 'karyawan')
                <a href="{{ route('karyawan.leave-requests.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('karyawan.leave-requests.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Izin</span>
                </a>
                <a href="{{ route('karyawan.attendance.index') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('karyawan.attendance.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Riwayat</span>
                </a>
                <a href="{{ route('karyawan.profile.edit') }}" class="flex flex-col items-center py-1.5 px-2 transition-colors {{ request()->routeIs('*.profile.*') ? 'text-blue-600' : 'text-blue-400 hover:text-blue-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Profil</span>
                </a>
            @endif

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex flex-col items-center py-1.5 px-2 text-blue-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="text-[10px] font-semibold mt-0.5">Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const wibOffset = 7 * 60;
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const wibDate = new Date(utc + (60000 * wibOffset));

            const hours = String(wibDate.getHours()).padStart(2, '0');
            const minutes = String(wibDate.getMinutes()).padStart(2, '0');
            const seconds = String(wibDate.getSeconds()).padStart(2, '0');

            document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>
