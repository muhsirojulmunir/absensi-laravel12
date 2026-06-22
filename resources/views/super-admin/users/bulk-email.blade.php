@extends('layouts.master')
@section('title', 'Kirim Email Akun Karyawan Massal')
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
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.users.index') }}" class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 text-gray-400 dark:text-blue-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-navy-900 dark:text-white">Kirim Email Akun Massal</h1>
                <p class="text-xs text-gray-500 dark:text-slate-400">Kirim email kredensial login & update password karyawan sekaligus</p>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    <template x-if="errorMsg">
        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-5 py-4 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-sm animate-pulse">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span x-text="errorMsg"></span>
        </div>
    </template>

    {{-- Loader Overlay --}}
    <div x-show="isLoading" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/40 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-2xl border border-blue-50 dark:border-slate-700 flex flex-col items-center gap-4 text-center max-w-xs">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm font-bold text-blue-950 dark:text-white">Sedang Memproses...</p>
            <p class="text-xs text-gray-500 dark:text-slate-400">Mengupdate database dan mengirim email absensi ke karyawan.</p>
        </div>
    </div>

    {{-- Hasil Pengiriman (Results) --}}
    <template x-if="results">
        <div class="bg-white dark:bg-slate-800 border border-emerald-100 dark:border-slate-700 rounded-3xl p-8 shadow-md space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 rounded-full flex items-center justify-center text-2xl text-emerald-500">
                    🎉
                </div>
                <div>
                    <h3 class="text-lg font-bold text-blue-950 dark:text-white">Proses Pengiriman Selesai!</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Pembaruan akun dan pengiriman email berhasil diproses.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/50 p-4 rounded-2xl">
                    <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Berhasil</p>
                    <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400" x-text="results.success_count"></p>
                </div>
                <div class="bg-red-50/50 dark:bg-red-950/10 border border-red-100 dark:border-red-900/50 p-4 rounded-2xl">
                    <p class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-widest mb-1">Gagal</p>
                    <p class="text-2xl font-extrabold text-red-700 dark:text-red-400" x-text="results.error_count"></p>
                </div>
            </div>

            <template x-if="results.errors.length > 0">
                <div class="bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-800 rounded-2xl p-5 space-y-2">
                    <h4 class="text-xs font-bold text-red-800 dark:text-red-400 uppercase tracking-wider">Detail Kesalahan:</h4>
                    <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
                        <template x-for="err in results.errors">
                            <li x-text="err"></li>
                        </template>
                    </ul>
                </div>
            </template>
            
            <div class="flex justify-end pt-2">
                <button type="button" @click="results = null" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-md shadow-blue-500/20 text-sm active:scale-95 transition-all">
                    Kirim Data Baru
                </button>
            </div>
        </div>
    </template>

    {{-- Main Input Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden" x-show="!results">
        <div class="p-8 space-y-6">
            <div>
                <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-2">Salin & Tempel Data Notepad</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">
                    Masukkan daftar akun karyawan dengan format: <strong>Email/Username & Password</strong>. Setiap baris mewakili satu karyawan.<br>
                    Contoh format pemisah yang didukung: <code>koma (,)</code>, <code>titik koma (;)</code>, <code>titik dua (:)</code>, <code>pipa (|)</code>, atau <code>spasi/tab</code>.
                </p>
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 font-mono text-[11px] text-slate-600 dark:text-slate-400 mb-4">
                    karyawan1@example.com, passwordKaryawan1<br>
                    karyawan2_ramayana, passwordKaryawan2<br>
                    spg_ramayana_sidoarjo: passRamayanaSda
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Teks Mentah Kredensial</label>
                <textarea x-model="rawText" rows="8" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-900 dark:text-white font-mono" placeholder="Tempelkan data notepad Anda di sini..."></textarea>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900/50 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-slate-700">
            <button type="button" @click="parseText()" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/20 text-sm active:scale-95 transition-all">
                Pratinjau & Verifikasi Karyawan
            </button>
        </div>
    </div>

    {{-- Interactive Preview Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden" x-show="showPreview && !results" x-cloak>
        <div class="p-8 border-b border-gray-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Hasil Verifikasi Karyawan</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Verifikasi kecocokan data dengan akun aktif di sistem absensi.</p>
            </div>
            
            <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900 px-4 py-2 rounded-xl border border-slate-100 dark:border-slate-800 text-xs">
                <span class="font-bold text-blue-600 dark:text-blue-400" x-text="`${parsedUsers.length} baris terdeteksi`"></span>
                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="`${parsedUsers.filter(u => u.found).length} terdaftar`"></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 border-b border-gray-100 dark:border-slate-700 text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Data Input</th>
                        <th class="px-6 py-4">Karyawan Terdeteksi</th>
                        <th class="px-6 py-4">Status DB</th>
                        <th class="px-6 py-4">Password Baru</th>
                        <th class="px-6 py-4 text-center">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="globalUpdatePassword" @change="toggleGlobalUpdatePassword()" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5">
                                <span>Update PW</span>
                            </label>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="globalSendEmail" @change="toggleGlobalSendEmail()" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5">
                                <span>Kirim Email</span>
                            </label>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                    <template x-for="(u, idx) in parsedUsers" :key="idx">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                            {{-- Input Text --}}
                            <td class="px-6 py-4 font-mono text-xs text-gray-600 dark:text-slate-400" x-text="u.input"></td>
                            
                            {{-- Detected Employee --}}
                            <td class="px-6 py-4">
                                <template x-if="u.found">
                                    <div>
                                        <div class="font-bold text-blue-950 dark:text-white" x-text="u.name"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-slate-400 font-medium">
                                            Role: <span class="font-bold uppercase" x-text="u.role"></span> | Usr: <span class="font-mono font-bold" x-text="u.username"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!u.found">
                                    <span class="text-xs text-gray-400 italic">Tidak terdeteksi</span>
                                </template>
                            </td>
                            
                            {{-- DB Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="u.found">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        Cocok
                                    </span>
                                </template>
                                <template x-if="!u.found">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-950/20 text-xs font-bold text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                        Tidak Ada
                                    </span>
                                </template>
                            </td>
                            
                            {{-- Password --}}
                            <td class="px-6 py-4">
                                <span class="font-mono bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded-md text-xs font-bold text-gray-900 dark:text-slate-200" x-text="u.password || '(Kosong)'"></span>
                            </td>
                            
                            {{-- Checkbox Update Password --}}
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="u.update_password" :disabled="!u.found" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 disabled:opacity-30">
                            </td>
                            
                            {{-- Checkbox Send Email --}}
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="u.send_email" :disabled="!u.found || !u.email" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 disabled:opacity-30">
                                <template x-if="u.found && !u.email">
                                    <span class="block text-[9px] text-red-500 mt-1 font-bold">Email Kosong</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900/50 p-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-gray-100 dark:border-slate-700">
            <p class="text-xs text-gray-500 dark:text-slate-400">
                Akan memproses <strong class="text-blue-600 dark:text-blue-400" x-text="selectedCount"></strong> aksi akun dari tabel pratinjau di atas.
            </p>
            <div class="flex gap-2">
                <button type="button" @click="showPreview = false; parsedUsers = []" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors">
                    Reset
                </button>
                <button type="button" @click="sendEmails()" :disabled="selectedCount === 0" class="bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-300 dark:disabled:bg-slate-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 text-xs active:scale-95 disabled:pointer-events-none transition-all">
                    Kirim & Perbarui Akun Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
