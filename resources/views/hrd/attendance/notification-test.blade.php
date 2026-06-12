@extends('layouts.master')
@section('title', 'Test Notifikasi - HRD')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">🔔 Test Notifikasi</h1>
        <p class="text-sm text-blue-500 dark:text-blue-400">Kirim dan uji notifikasi push ke karyawan secara manual</p>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif
    @if(session('smart_output'))
        <div class="bg-slate-800 dark:bg-slate-900 border border-slate-600 rounded-xl p-4">
            <p class="text-xs font-mono font-bold text-emerald-400 mb-2">📋 Output Notifikasi Otomatis:</p>
            <pre class="text-xs font-mono text-slate-300 whitespace-pre-wrap">{{ session('smart_output') }}</pre>
        </div>
    @endif

    {{-- Status Karyawan FCM --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm p-6">
        <h2 class="text-lg font-bold text-blue-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Status FCM Token Karyawan
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $allUsers->count() }}</p>
                <p class="text-xs text-blue-500 mt-1">Total Karyawan</p>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $users->count() }}</p>
                <p class="text-xs text-emerald-500 mt-1">Punya FCM Token</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $allUsers->count() - $users->count() }}</p>
                <p class="text-xs text-amber-500 mt-1">Belum Ada Token</p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/30 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ $users->where('division.name', 'like', '%live%')->count() }}</p>
                <p class="text-xs text-purple-500 mt-1">Live Streaming</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left py-2 px-3 font-semibold text-slate-600 dark:text-slate-400">Nama</th>
                        <th class="text-left py-2 px-3 font-semibold text-slate-600 dark:text-slate-400">Divisi</th>
                        <th class="text-left py-2 px-3 font-semibold text-slate-600 dark:text-slate-400">Status FCM</th>
                        <th class="text-left py-2 px-3 font-semibold text-slate-600 dark:text-slate-400">Token (Preview)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allUsers as $u)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="py-2 px-3 font-medium text-slate-800 dark:text-slate-200">{{ $u->name }}</td>
                        <td class="py-2 px-3 text-slate-500 dark:text-slate-400 text-xs">{{ $u->division->name ?? '-' }}</td>
                        <td class="py-2 px-3">
                            @if($u->fcm_token)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Siap
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Belum Login / Izin Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-xs font-mono text-slate-400 dark:text-slate-500">
                            {{ $u->fcm_token ? substr($u->fcm_token, 0, 25) . '...' : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Card 1: Kirim Notif Manual ke Karyawan --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-lg font-bold text-blue-900 dark:text-white mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Notifikasi Manual
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Kirim push notification ke karyawan tertentu untuk test</p>

            <form action="{{ route('hrd.notification-test.send') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Karyawan</label>
                    <select name="user_id" id="select-user" required
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" {{ !$u->fcm_token ? 'data-no-token=1' : '' }}>
                                {{ $u->name }} ({{ $u->division->name ?? '-' }})
                                {{ !$u->fcm_token ? ' ⚠️ No FCM' : ' ✅' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul Notifikasi</label>
                    <input type="text" name="title" id="notif-title" value="⏰ Test Notifikasi"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-blue-400 outline-none"
                        required maxlength="100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Isi Pesan</label>
                    <textarea name="message" rows="3" required maxlength="500"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none"
                        placeholder="Tulis isi pesan notifikasi...">Ini adalah test notifikasi dari HRD. Sistem notifikasi absensi berjalan dengan baik!</textarea>
                </div>

                {{-- Template Cepat --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Template Cepat</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setTemplate('masuk')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-800/40 border border-blue-200 dark:border-blue-700 transition">
                            ⏰ Pengingat Masuk
                        </button>
                        <button type="button" onclick="setTemplate('pulang')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-800/40 border border-amber-200 dark:border-amber-700 transition">
                            🏠 Pengingat Pulang
                        </button>
                        <button type="button" onclick="setTemplate('8jam')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-800/40 border border-purple-200 dark:border-purple-700 transition">
                            ⌛ Sudah 8 Jam
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Notifikasi
                </button>
            </form>
        </div>

        {{-- Card 2: Jalankan Smart Notification --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-lg font-bold text-blue-900 dark:text-white mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Jalankan Notifikasi Otomatis (Test Mode)
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                Jalankan command <code class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-xs">notify:smart-attendance --test</code> sekarang juga dan lihat hasilnya.
            </p>

            <form action="{{ route('hrd.notification-test.run-smart') }}" method="POST" class="space-y-4">
                @csrf

                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 space-y-3">
                    <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">📋 Cara Kerja Notifikasi Otomatis</h3>
                    <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">🏢</span>
                            <span><strong>Staff Kantor:</strong> Pengingat <strong>masuk</strong> dikirim jam 07:45–08:30 jika belum absen masuk, setiap 5 menit sekali.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 mt-0.5">🏠</span>
                            <span><strong>Staff Kantor:</strong> Pengingat <strong>pulang</strong> dikirim jam 17:00–17:15 jika belum absen pulang, setiap 5 menit sekali.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 mt-0.5">📡</span>
                            <span><strong>Live Streamer:</strong> Notifikasi <strong>8 jam</strong> dikirim saat sudah 8–8.5 jam bekerja sejak clock-in, 1x per hari.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">⚙️</span>
                            <span>Sistem ini berjalan <strong>otomatis setiap 5 menit</strong> via Laravel Scheduler (perlu cron aktif di server).</span>
                        </li>
                    </ul>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Filter Nama (opsional)</label>
                        <input type="text" name="user" placeholder="Contoh: Ari"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Filter Tipe (opsional)</label>
                        <select name="type"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                            <option value="">Semua Tipe</option>
                            <option value="masuk">⏰ Masuk</option>
                            <option value="pulang">🏠 Pulang</option>
                            <option value="8jam">⌛ 8 Jam (Live)</option>
                        </select>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jalankan Sekarang (Test Mode)
                </button>
            </form>
        </div>
    </div>

    {{-- Panduan Setup Cron Windows --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-amber-200 dark:border-amber-700/50 shadow-sm p-6">
        <h2 class="text-lg font-bold text-amber-800 dark:text-amber-300 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ⚙️ Setup Jadwal Otomatis (Agar Notif Berjalan Tiap 5 Menit)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Local (XAMPP/Windows) --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xs font-bold">1</span>
                    Lokal (Windows/XAMPP)
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Buka <strong>Task Scheduler</strong> Windows dan buat task baru dengan perintah berikut:</p>
                <div class="bg-slate-900 rounded-lg p-3 relative group">
                    <button onclick="copyCmd(this, 'cmd-local')" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition text-xs text-slate-400 hover:text-white">Copy</button>
                    <pre id="cmd-local" class="text-xs text-emerald-400 font-mono whitespace-pre-wrap">C:\xampp\php\php.exe artisan schedule:run</pre>
                    <p class="text-xs text-slate-500 mt-2">Working Directory: <span class="text-slate-300">{{ base_path() }}</span></p>
                    <p class="text-xs text-slate-500 mt-1">Trigger: Setiap 1 menit</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-xs text-blue-700 dark:text-blue-300">
                    <strong>Langkah:</strong> Cari "Task Scheduler" di Start Menu → Create Basic Task → Trigger: Daily + Repeat every 1 minute → Action: Run Program diatas.
                </div>
            </div>

            {{-- Live Server / cPanel --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-xs font-bold">2</span>
                    Live Server (cPanel / Linux)
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan cron job berikut di cPanel → Cron Jobs:</p>
                <div class="bg-slate-900 rounded-lg p-3 relative group">
                    <button onclick="copyCmd(this, 'cmd-server')" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition text-xs text-slate-400 hover:text-white">Copy</button>
                    <pre id="cmd-server" class="text-xs text-emerald-400 font-mono whitespace-pre-wrap">* * * * * /usr/bin/php {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1</pre>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 text-xs text-emerald-700 dark:text-emerald-300">
                    <strong>Atau gunakan External Cron:</strong> Buka URL berikut setiap menit via cron service eksternal (UptimeRobot, dll):
                    <br><code class="mt-1 block text-xs break-all text-emerald-600 dark:text-emerald-400">{{ url('/cron/smart-notification') }}?key={{ env('SYNC_SECRET_KEY') }}</code>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800/30">
            <p class="text-xs text-amber-700 dark:text-amber-300">
                <strong>⚠️ Penting:</strong> Tanpa cron/task scheduler yang aktif, notifikasi otomatis <strong>tidak akan berjalan</strong>. Tombol "Jalankan Sekarang" di atas hanya untuk test manual. Untuk production, wajib setup cron.
            </p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function setTemplate(type) {
    const titleEl = document.getElementById('notif-title');
    const msgEl = document.querySelector('textarea[name="message"]');

    const templates = {
        masuk: {
            title: '⏰ Pengingat Absen Masuk',
            msg: 'Halo, jangan lupa untuk melakukan absen masuk (Clock In) pagi ini. Selamat bekerja dan semoga harimu menyenangkan! 🌅'
        },
        pulang: {
            title: '🏠 Waktunya Pulang!',
            msg: 'Halo, waktu kerja hari ini sudah selesai (17:00 WIB). Terima kasih atas kerja kerasnya hari ini! Jangan lupa absen pulang (Clock Out). 🏠'
        },
        '8jam': {
            title: '⏰ Sudah 8 Jam Bekerja!',
            msg: 'Halo, kamu sudah bekerja selama 8 jam lho! Waktunya istirahat dan jangan lupa untuk melakukan absen pulang (Clock Out) ya. 💪'
        }
    };

    if (templates[type]) {
        titleEl.value = templates[type].title;
        msgEl.value = templates[type].msg;
    }
}

function copyCmd(btn, elId) {
    const text = document.getElementById(elId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
    });
}
</script>
@endpush
@endsection
