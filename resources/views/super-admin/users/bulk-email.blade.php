@extends('layouts.master')
@section('title', 'Kirim Email Kredensial Karyawan Massal')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    rawText: '',
    parsedUsers: [],
    isLoading: false,
    showPreview: false,
    results: null,
    errorMsg: '',
    globalUpdatePassword: true,
    globalSendEmail: true,
    
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
}">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('super-admin.users.index') }}" 
               class="p-2.5 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Kirim Email Akun Massal</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kirim email kredensial login & update password karyawan sekaligus</p>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    <template x-if="errorMsg">
        <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-400 px-5 py-4 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-xs">
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
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Mengupdate database dan mengirim email absensi ke karyawan.</p>
            </div>
        </div>
    </div>

    {{-- Hasil Pengiriman (Results) --}}
    <template x-if="results">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-250 dark:border-emerald-900/60 rounded-xl flex items-center justify-center text-xl">
                    🎉
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Proses Pengiriman Selesai!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pembaruan akun dan pengiriman email berhasil diproses.</p>
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
                <button type="button" @click="results = null" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-md shadow-indigo-900/10 text-xs active:scale-[0.98] transition-all">
                    Kirim Data Baru
                </button>
            </div>
        </div>
    </template>

    {{-- Main Input Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden" x-show="!results">
        <div class="p-6 sm:p-8 space-y-6">
            <div class="space-y-3">
                <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Salin & Tempel Data Notepad</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Masukkan daftar akun karyawan dengan format: <strong class="text-slate-700 dark:text-slate-350">Email/Username & Password</strong>. Setiap baris mewakili satu karyawan.<br>
                    Contoh format pemisah yang didukung: <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">koma (,)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">titik koma (;)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">titik dua (:)</code>, <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">pipa (|)</code>, atau <code class="bg-slate-100 dark:bg-slate-950 px-1 py-0.5 rounded text-indigo-500">spasi/tab</code>.
                </p>
                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded-xl p-4 font-mono text-[11px] text-slate-550 dark:text-slate-450 leading-relaxed shadow-inner">
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
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Verifikasi kecocokan data dengan akun aktif di sistem absensi.</p>
            </div>
            
            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-950 px-4 py-2 rounded-xl border border-slate-100 dark:border-slate-850 text-xs shrink-0 self-start sm:self-center">
                <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="`${parsedUsers.length} baris terdeteksi`"></span>
                <span class="w-1.5 h-1.5 bg-slate-350 dark:bg-slate-700 rounded-full"></span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="`${parsedUsers.filter(u => u.found).length} terdaftar`"></span>
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
                                        <div class="text-[10px] text-slate-500 dark:text-slate-450 mt-0.5">
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
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-100/50 dark:border-emerald-900/50">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        Cocok
                                    </span>
                                </template>
                                <template x-if="!u.found">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/20 text-xs font-bold text-rose-600 dark:text-rose-450 border border-rose-100/50 dark:border-rose-900/50">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                        Tidak Ada
                                    </span>
                                </template>
                            </td>
                            
                            {{-- Password --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono bg-slate-100 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-850 px-2 py-1 rounded-lg text-xs font-bold text-slate-850 dark:text-slate-350" x-text="u.password || '(Kosong)'"></span>
                            </td>
                            
                            {{-- Checkbox Update Password --}}
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="u.update_password" :disabled="!u.found" 
                                       class="rounded border-slate-300 dark:border-slate-700 text-indigo-655 focus:ring-indigo-500 disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed">
                            </td>
                            
                            {{-- Checkbox Send Email --}}
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="u.send_email" :disabled="!u.found || !u.email" 
                                       class="rounded border-slate-300 dark:border-slate-700 text-indigo-655 focus:ring-indigo-500 disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed">
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
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Akan memproses <strong class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="selectedCount"></strong> aksi akun dari tabel pratinjau di atas.
            </p>
            <div class="flex gap-2.5">
                <button type="button" @click="showPreview = false; parsedUsers = []" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-650 dark:text-slate-350 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-950 transition-colors">
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
@endsection
