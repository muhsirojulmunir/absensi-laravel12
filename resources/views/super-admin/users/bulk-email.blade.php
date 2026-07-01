@extends('layouts.master')
@section('title', 'Kirim Email Kredensial Karyawan Massal')
@section('content')

@php
    $employeesJson = $employees->map(function($emp) {
        return [
            'id' => $emp->id,
            'name' => $emp->name,
            'role_name' => $emp->role->name ?? '-',
            'username' => $emp->username,
            'email' => $emp->email ?? 'Belum ada Email',
            'has_email' => !empty($emp->email),
            'status' => !$emp->latestBulkEmailLog ? 'not_sent' : ($emp->latestBulkEmailLog->status === 'success' ? 'sent' : 'failed'),
            'error_message' => $emp->latestBulkEmailLog->error_message ?? null,
            'sent_at' => $emp->latestBulkEmailLog ? $emp->latestBulkEmailLog->created_at->translatedFormat('d M Y, H:i') : '-',
            'sender_name' => ($emp->latestBulkEmailLog && $emp->latestBulkEmailLog->sender) ? $emp->latestBulkEmailLog->sender->name : '-',
        ];
    })->values()->toJson();
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="bulkEmailApp">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('super-admin.users.index') }}" 
               class="p-2.5 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Kirim Email Akun Massal</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Kirim email kredensial login & update password karyawan sekaligus</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex bg-slate-100 dark:bg-slate-950 p-1 rounded-xl border border-slate-200/60 dark:border-slate-850">
            <button @click="activeTab = 'send'" 
                    :class="activeTab === 'send' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-4 py-2 text-xs font-bold rounded-lg transition-all">
                Kirim Email
            </button>
            <button @click="activeTab = 'status'" 
                    :class="activeTab === 'status' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 font-bold">
                Status Karyawan
                <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 px-1.5 py-0.5 rounded text-[10px] font-black">
                    {{ count($employees) }}
                </span>
            </button>
            <button @click="activeTab = 'history'" 
                    :class="activeTab === 'history' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-4 py-2 text-xs font-bold rounded-lg transition-all">
                Log Pengiriman
            </button>
        </div>
    </div>

    {{-- Error Banner --}}
    <template x-if="errorMsg">
        <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-250 dark:border-rose-900/50 text-rose-800 dark:text-rose-400 px-5 py-4 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
            </svg>
            <span x-text="errorMsg"></span>
        </div>
    </template>

    {{-- Loader Overlay --}}
    <div x-show="isLoading" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/40 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 flex flex-col items-center gap-4 text-center max-w-xs animate-scale-in">
            <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Sedang Memproses...</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Mengupdate database dan mengirim email absensi ke karyawan.</p>
            </div>
        </div>
    </div>

    {{-- TAB 1: KIRIM EMAIL MASSAL --}}
    <div x-show="activeTab === 'send'" class="space-y-6">
        {{-- Hasil Pengiriman (Results) --}}
        <template x-if="results">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-8 shadow-sm space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-250 dark:border-emerald-900/60 rounded-xl flex items-center justify-center text-xl">
                        🎉
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Proses Pengiriman Selesai!</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Pembaruan akun dan pengiriman email berhasil diproses. Halaman akan dimuat ulang untuk memperbarui riwayat.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/50 p-4 rounded-xl">
                        <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Berhasil</p>
                        <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400" x-text="results.success_count"></p>
                    </div>
                    <div class="bg-rose-50/40 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/50 p-4 rounded-xl">
                        <p class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest mb-1">Gagal</p>
                        <p class="text-2xl font-extrabold text-rose-700 dark:text-rose-400" x-text="results.error_count"></p>
                    </div>
                </div>

                <template x-if="results.errors.length > 0">
                    <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 rounded-xl p-5 space-y-2">
                        <h4 class="text-xs font-bold text-rose-800 dark:text-rose-400 uppercase tracking-wider">Detail Kesalahan:</h4>
                        <ul class="list-disc list-inside text-xs text-rose-700 dark:text-rose-350 space-y-1">
                            <template x-for="err in results.errors">
                                <li x-text="err"></li>
                            </template>
                        </ul>
                    </div>
                </template>
                
                <div class="flex justify-end pt-2">
                    <button type="button" @click="window.location.reload()" 
                            class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-md shadow-indigo-900/10 text-xs active:scale-[0.98] transition-all">
                        Muat Ulang & Kirim Data Baru
                    </button>
                </div>
            </div>
        </template>

        {{-- Main Input Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden" x-show="!results">
            <div class="p-6 sm:p-8 space-y-6">
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Salin & Tempel Data Notepad</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                        Masukkan daftar akun karyawan dengan format: <strong class="text-slate-700 dark:text-slate-355">Email/Username & Password</strong>. Setiap baris mewakili satu karyawan.<br>
                        Contoh format pemisah yang didukung: <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">koma (,)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">titik koma (;)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">titik dua (:)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">pipa (|)</code>, atau <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">spasi/tab</code>.
                    </p>
                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded-xl p-4 font-mono text-[11px] text-slate-550 dark:text-slate-455 leading-relaxed shadow-inner">
                        karyawan1@example.com, passwordKaryawan1<br>
                        karyawan2_ramayana, passwordKaryawan2<br>
                        spg_ramayana_sidoarjo: passRamayanaSda
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Teks Mentah Kredensial</label>
                    <textarea x-model="rawText" rows="8" 
                              class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 transition-all text-xs text-slate-900 dark:text-white font-mono leading-relaxed" 
                              placeholder="Tempelkan data notepad Anda di sini..."></textarea>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-950/40 p-6 flex justify-end space-x-3 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" @click="parseText()" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-md shadow-indigo-900/10 text-xs active:scale-[0.98] transition-all">
                    Pratinjau & Verifikasi Karyawan
                </button>
            </div>
        </div>

        {{-- Interactive Preview Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden" 
             x-show="showPreview && !results" x-cloak>
            <div class="p-6 border-b border-slate-100 dark:border-slate-800/60 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Hasil Verifikasi Karyawan</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Verifikasi kecocokan data dengan akun aktif di sistem absensi.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-950 px-4 py-2 rounded-xl border border-slate-100 dark:border-slate-850 text-xs shrink-0 self-start sm:self-center font-bold">
                    <span class="text-indigo-600 dark:text-indigo-400" x-text="`${parsedUsers.length} baris terdeteksi`"></span>
                    <span class="w-1.5 h-1.5 bg-slate-350 dark:bg-slate-700 rounded-full"></span>
                    <span class="text-emerald-600 dark:text-emerald-400" x-text="`${parsedUsers.filter(u => u.found).length} terdaftar`"></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-150 dark:border-slate-850 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-4">Data Input</th>
                            <th class="px-6 py-4">Karyawan Terdeteksi</th>
                            <th class="px-6 py-4">Status DB</th>
                            <th class="px-6 py-4">Password Baru</th>
                            <th class="px-6 py-4 text-center">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" x-model="globalUpdatePassword" @change="toggleGlobalUpdatePassword()" 
                                           class="rounded border-slate-350 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 h-3.5 w-3.5 bg-white dark:bg-slate-900 cursor-pointer">
                                    <span>Update PW</span>
                                </label>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" x-model="globalSendEmail" @change="toggleGlobalSendEmail()" 
                                           class="rounded border-slate-350 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 h-3.5 w-3.5 bg-white dark:bg-slate-900 cursor-pointer">
                                    <span>Kirim Email</span>
                                </label>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                        <template x-for="(u, idx) in parsedUsers" :key="idx">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors">
                                {{-- Input Text --}}
                                <td class="px-6 py-4 font-mono text-xs text-slate-550 dark:text-slate-400" x-text="u.input"></td>
                                
                                {{-- Detected Employee --}}
                                <td class="px-6 py-4">
                                    <template x-if="u.found">
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white" x-text="u.name"></div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-455 mt-0.5 font-medium">
                                                Role: <span class="font-bold uppercase text-indigo-500" x-text="u.role"></span> | Usr: <span class="font-mono font-bold text-slate-700 dark:text-slate-300" x-text="u.username"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!u.found">
                                        <span class="text-xs text-slate-400 italic">Tidak terdeteksi</span>
                                    </template>
                                </td>
                                
                                {{-- DB Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="u.found">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-xs font-bold text-emerald-600 dark:text-emerald-450 border border-emerald-100/50 dark:border-emerald-900/50">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                            Cocok
                                        </span>
                                    </template>
                                    <template x-if="!u.found">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/20 text-xs font-bold text-rose-600 dark:text-rose-455 border border-rose-100/50 dark:border-rose-900/50">
                                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                            Tidak Ada
                                        </span>
                                    </template>
                                </td>
                                
                                {{-- Password --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono bg-slate-100 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-850 px-2 py-1 rounded-lg text-xs font-bold text-slate-800 dark:text-slate-300" x-text="u.password || '(Kosong)'"></span>
                                </td>
                                
                                {{-- Checkbox Update Password --}}
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" x-model="u.update_password" :disabled="!u.found" 
                                           class="rounded border-slate-300 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed">
                                </td>
                                
                                {{-- Checkbox Send Email --}}
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" x-model="u.send_email" :disabled="!u.found || !u.email" 
                                           class="rounded border-slate-300 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed">
                                    <template x-if="u.found && !u.email">
                                        <span class="block text-[9px] text-rose-500 mt-1 font-bold">Email Kosong</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 dark:bg-slate-950/40 p-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-100 dark:border-slate-800/80">
                <p class="text-xs text-slate-500 dark:text-slate-450 font-medium">
                    Akan memproses <strong class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="selectedCount"></strong> aksi akun dari tabel pratinjau di atas.
                </p>
                <div class="flex gap-2.5">
                    <button type="button" @click="showPreview = false; parsedUsers = []" 
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-950 transition-colors">
                        Reset
                    </button>
                    <button type="button" @click="sendEmails()" :disabled="selectedCount === 0" 
                            class="bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-200 dark:disabled:bg-slate-800 text-white px-6 py-2.5 rounded-xl font-bold shadow-md shadow-emerald-500/10 text-xs active:scale-[0.98] disabled:text-slate-400 dark:disabled:text-slate-600 disabled:pointer-events-none transition-all">
                        Kirim & Perbarui Akun Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: STATUS PENGIRIMAN KARYAWAN --}}
    @php
        $totalCount = $employees->count();
        $sentCount = $employees->filter(fn($e) => $e->latestBulkEmailLog && $e->latestBulkEmailLog->status === 'success')->count();
        $failedCount = $employees->filter(fn($e) => $e->latestBulkEmailLog && $e->latestBulkEmailLog->status === 'failed')->count();
        $notSentCount = $employees->filter(fn($e) => !$e->latestBulkEmailLog)->count();
    @endphp
    <div x-show="activeTab === 'status'" class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden" x-cloak>
        <!-- Stats Summary Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 dark:divide-slate-850 border-b border-slate-100 dark:border-slate-850">
            <!-- Total -->
            <button @click="statusFilter = 'all'" 
                    :class="statusFilter === 'all' ? 'bg-indigo-50/30 dark:bg-indigo-950/10' : ''"
                    class="p-6 text-left hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-all focus:outline-none group">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Total Karyawan</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $totalCount }}</span>
                    <span class="text-xs font-semibold text-slate-400">orang</span>
                </div>
            </button>
            <!-- Terkirim -->
            <button @click="statusFilter = 'sent'" 
                    :class="statusFilter === 'sent' ? 'bg-emerald-50/30 dark:bg-emerald-950/10' : ''"
                    class="p-6 text-left hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-all focus:outline-none group">
                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-450 uppercase tracking-widest transition-colors">Email Terkirim</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-450">{{ $sentCount }}</span>
                    <span class="text-xs font-semibold text-emerald-400">berhasil</span>
                </div>
            </button>
            <!-- Gagal -->
            <button @click="statusFilter = 'failed'" 
                    :class="statusFilter === 'failed' ? 'bg-rose-50/30 dark:bg-rose-950/10' : ''"
                    class="p-6 text-left hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-all focus:outline-none group">
                <p class="text-[10px] font-bold text-rose-600 dark:text-rose-455 uppercase tracking-widest transition-colors">Gagal Kirim</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-rose-600 dark:text-rose-455">{{ $failedCount }}</span>
                    <span class="text-xs font-semibold text-slate-400">gagal</span>
                </div>
            </button>
            <!-- Belum Terkirim -->
            <button @click="statusFilter = 'not_sent'" 
                    :class="statusFilter === 'not_sent' ? 'bg-amber-50/30 dark:bg-amber-950/10' : ''"
                    class="p-6 text-left hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-all focus:outline-none group">
                <p class="text-[10px] font-bold text-amber-600 dark:text-amber-450 uppercase tracking-widest transition-colors">Belum Terkirim</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-450">{{ $notSentCount }}</span>
                    <span class="text-xs font-semibold text-slate-400">belum</span>
                </div>
            </button>
        </div>

        <div class="p-6 border-b border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-950/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Status Log Email Karyawan</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Daftar seluruh karyawan beserta status pengiriman email kredensial terakhir mereka.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <!-- Status Filter Dropdown -->
                <div class="relative min-w-[160px]">
                    <select x-model="statusFilter" 
                            class="block w-full px-3.5 py-2 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-950 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold cursor-pointer">
                        <option value="all">Semua Status</option>
                        <option value="sent">Terkirim</option>
                        <option value="failed">Gagal Kirim</option>
                        <option value="not_sent">Belum Terkirim</option>
                    </select>
                </div>
                
                <!-- Search field -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="employeeSearch" placeholder="Cari nama atau email..." 
                           class="block w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-950 text-xs text-slate-900 dark:text-white placeholder-slate-450 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/40 border-b border-slate-150 dark:border-slate-850 text-xs font-semibold text-slate-500 dark:text-slate-450 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama Karyawan</th>
                        <th class="px-6 py-4">Username & Email</th>
                        <th class="px-6 py-4">Status Terakhir</th>
                        <th class="px-6 py-4">Tanggal Pengiriman</th>
                        <th class="px-6 py-4">Pengirim (Admin)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                    <template x-for="emp in paginatedEmployees" :key="emp.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white" x-text="emp.name"></div>
                                <div class="text-[10px] text-indigo-500 font-bold uppercase tracking-wide mt-0.5" x-text="emp.role_name"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-xs text-slate-700 dark:text-slate-300 font-bold" x-text="emp.username"></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="emp.email"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="emp.status === 'sent'">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                        <span class="w-1 h-1 bg-emerald-500 rounded-full"></span>
                                        Terkirim
                                    </span>
                                </template>
                                <template x-if="emp.status === 'failed'">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40" 
                                          :title="emp.error_message">
                                        <span class="w-1 h-1 bg-rose-500 rounded-full"></span>
                                        Gagal
                                    </span>
                                </template>
                                <template x-if="emp.status === 'not_sent'">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40">
                                        <span class="w-1 h-1 bg-amber-500 rounded-full"></span>
                                        Belum Terkirim
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600 dark:text-slate-400" x-text="emp.sent_at"></td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-350" x-text="emp.sender_name"></td>
                        </tr>
                    </template>
                    
                    <template x-if="filteredEmployees.length === 0">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-slate-450">
                                Tidak ada data karyawan aktif.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-850 flex items-center justify-between flex-wrap gap-4 select-none">
            <div class="text-xs text-slate-500 dark:text-slate-455">
                <span>
                    Menampilkan <span class="font-bold" x-text="filteredEmployees.length > 0 ? (employeePage - 1) * employeePerPage + 1 : 0"></span> - 
                    <span class="font-bold" x-text="Math.min(employeePage * employeePerPage, filteredEmployees.length)"></span> dari 
                    <span class="font-bold" x-text="filteredEmployees.length"></span> data
                </span>
            </div>
            <div class="flex gap-1 items-center" x-show="totalPages > 1">
                <!-- Prev Button -->
                <button @click="if (employeePage > 1) employeePage--" 
                        :disabled="employeePage === 1"
                        class="p-2 text-slate-450 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:pointer-events-none rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path>
                    </svg>
                </button>
                
                <!-- Page Numbers -->
                <template x-for="p in totalPages" :key="p">
                    <button @click="employeePage = p" 
                            :class="employeePage === p ? 'bg-indigo-600 text-white font-bold' : 'text-slate-650 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors"
                            x-text="p">
                    </button>
                </template>
                
                <!-- Next Button -->
                <button @click="if (employeePage < totalPages) employeePage++" 
                        :disabled="employeePage === totalPages"
                        class="p-2 text-slate-455 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:pointer-events-none rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- TAB 3: RIWAYAT LOG PENGIRIMAN --}}
    <div x-show="activeTab === 'history'" class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden" x-cloak>
        <div class="p-6 border-b border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-950/20">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Log Pengiriman Email Massal Terbaru</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Histori pengiriman email berdasarkan aktivitas pengirim admin.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/40 border-b border-slate-150 dark:border-slate-850 text-xs font-semibold text-slate-500 dark:text-slate-450 uppercase tracking-wider">
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Alamat Email</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Pengirim (Admin)</th>
                        <th class="px-6 py-4">Pesan Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors">
                        <td class="px-6 py-4 text-xs font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            {{ $log->user->name ?? 'User Terhapus' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-700 dark:text-slate-300">
                            {{ $log->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->status === 'success')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                    Berhasil
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-455 border border-rose-100 dark:border-rose-900/40">
                                    Gagal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-700 dark:text-slate-355 whitespace-nowrap">
                            {{ $log->sender->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ $log->error_message ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-xs text-slate-450">
                            Belum ada riwayat log pengiriman email.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bulkEmailApp', () => ({
        activeTab: 'send',
        rawText: '',
        parsedUsers: [],
        isLoading: false,
        showPreview: false,
        results: null,
        errorMsg: '',
        globalUpdatePassword: true,
        globalSendEmail: true,
        employeeSearch: '',
        statusFilter: 'all',
        employeesList: {!! $employeesJson !!},
        employeePage: 1,
        employeePerPage: 10,
        
        init() {
            this.$watch('employeeSearch', () => this.employeePage = 1);
            this.$watch('statusFilter', () => this.employeePage = 1);
        },
        
        get filteredEmployees() {
            return this.employeesList.filter(emp => {
                const searchLower = this.employeeSearch.toLowerCase();
                const matchesSearch = this.employeeSearch === '' || 
                    (emp.name && emp.name.toLowerCase().includes(searchLower)) || 
                    (emp.email && emp.email.toLowerCase().includes(searchLower)) || 
                    (emp.username && emp.username.toLowerCase().includes(searchLower));
                
                const matchesStatus = this.statusFilter === 'all' || emp.status === this.statusFilter;
                
                return matchesSearch && matchesStatus;
            });
        },
        
        get paginatedEmployees() {
            const start = (this.employeePage - 1) * this.employeePerPage;
            return this.filteredEmployees.slice(start, start + this.employeePerPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredEmployees.length / this.employeePerPage);
        },
        
        async parseText() {
            if (!this.rawText.trim()) {
                this.errorMsg = 'Silakan masukkan teks email dan password terlebih dahulu.';
                return;
            }
            this.errorMsg = '';
            this.isLoading = true;
            this.results = null;
            
            try {
                const response = await fetch('{{ route('super-admin.users.bulk-email.parse') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ raw_text: this.rawText })
                });
                
                const res = await response.json();
                if (res.success) {
                    this.parsedUsers = res.data.map(user => ({
                        ...user,
                        update_password: user.found,
                        send_email: user.found && !!user.email
                    }));
                    this.showPreview = true;
                } else {
                    this.errorMsg = res.message || 'Gagal memproses data input.';
                }
            } catch (err) {
                this.errorMsg = 'Terjadi kesalahan saat menghubungi server.';
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        },
        
        toggleGlobalUpdatePassword() {
            this.parsedUsers.forEach(u => {
                if (u.found) u.update_password = this.globalUpdatePassword;
            });
        },
        
        toggleGlobalSendEmail() {
            this.parsedUsers.forEach(u => {
                if (u.found && u.email) u.send_email = this.globalSendEmail;
            });
        },
        
        get selectedCount() {
            return this.parsedUsers.filter(u => u.found && (u.update_password || u.send_email)).length;
        },
        
        async sendEmails() {
            const selected = this.parsedUsers.filter(u => u.found && (u.update_password || u.send_email));
            if (selected.length === 0) {
                alert('Silakan pilih minimal 1 karyawan dengan opsi update password atau kirim email.');
                return;
            }
            
            if (!confirm(`Apakah Anda yakin ingin memproses ${selected.length} akun karyawan terpilih?`)) {
                return;
            }
            
            this.isLoading = true;
            this.errorMsg = '';
            
            try {
                const response = await fetch('{{ route('super-admin.users.bulk-email.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ users: selected })
                });
                
                const res = await response.json();
                if (res.success) {
                    this.results = res;
                    this.parsedUsers = [];
                    this.showPreview = false;
                    this.rawText = '';
                    // Reload page after a success message is shown so the history updates
                    setTimeout(() => {
                        window.location.reload();
                    }, 4000);
                } else {
                    this.errorMsg = res.message || 'Gagal memproses pengiriman email.';
                }
            } catch (err) {
                this.errorMsg = 'Terjadi kesalahan sistem saat memproses data.';
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        }
    }));
});
</script>
@endsection
