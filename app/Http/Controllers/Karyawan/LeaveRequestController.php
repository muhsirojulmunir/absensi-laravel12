<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->leaveRequests()->latest();

        // Custom Date Filtering
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $leaveRequests = $query->paginate(5)->appends($request->all());

        // Pesan dari Super Admin / PIC yang belum dibaca karyawan (untuk banner notifikasi).
        // Diambil SEBELUM ditandai terbaca, supaya karyawan tetap melihat notifikasinya
        // saat pertama kali membuka halaman ini.
        $unreadMessages = $user->leaveRequests()
            ->whereNotNull('admin_message')
            ->whereNull('admin_message_read_at')
            ->latest('admin_message_at')
            ->get();

        // Tandai pesan yang tampil di halaman ini sebagai sudah dibaca.
        // Hanya mengubah kolom penanda baca — tidak menyentuh data pengajuan lainnya.
        if ($unreadMessages->isNotEmpty()) {
            $user->leaveRequests()
                ->whereNotNull('admin_message')
                ->whereNull('admin_message_read_at')
                ->update(['admin_message_read_at' => now()]);
        }

        // Monthly Stats Reset
        $statsQuery = $user->leaveRequests()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();

        // Sisa jatah "Lupa Absen" bulan ini (gabungan Absen Masuk + Absen Pulang)
        $lupaAbsenQuota     = LeaveRequest::LUPA_ABSEN_QUOTA_PER_MONTH;
        $lupaAbsenUsed      = LeaveRequest::lupaAbsenUsedInMonth($user->id, now());
        $lupaAbsenRemaining = max(0, $lupaAbsenQuota - $lupaAbsenUsed);

        // Foto bukti hanya diwajibkan untuk Karyawan Ramayana
        $wajibFotoLupaAbsen = $user->role->slug === 'karyawan_ramayana';

        return view('karyawan.leave-requests.index', compact(
            'leaveRequests',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'unreadMessages',
            'lupaAbsenQuota',
            'lupaAbsenUsed',
            'lupaAbsenRemaining',
            'wajibFotoLupaAbsen'
        ));
    }

    public function store(Request $request)
    {
        $isLupaAbsen = $request->type === 'Lupa Absen';
        $isAbsenDiluar = $request->type === 'Absen Diluar';

        // ── PENTING: "Lupa Absen" & "Absen Diluar" adalah kejadian SATU MOMEN
        // (satu tanggal + satu jam), bukan rentang tanggal. Field "Sampai" di form
        // memang disembunyikan untuk tipe ini, tapi input yang cuma disembunyikan
        // secara visual TETAP IKUT TERKIRIM — sehingga nilai end_date sisa dari tipe
        // sebelumnya (mis. user sempat pilih 08–09 lalu ganti ke Lupa Absen) ikut
        // tersimpan dan bikin data absensi jadi tidak masuk akal.
        // Karena itu kita paksa end_date = start_date di sisi server (tidak bisa
        // diakali dari browser).
        if ($isLupaAbsen || $isAbsenDiluar) {
            $request->merge(['end_date' => $request->start_date]);
        }

        $isStaffKantor = Auth::user()->role->slug === 'karyawan';
        $startDateRule = 'required|date';
        if ($isLupaAbsen) {
            $startDateRule .= '|after_or_equal:' . now()->startOfMonth()->toDateString() . '|before_or_equal:today';
        } else {
            if ($isStaffKantor) {
                $startDateRule .= '|after_or_equal:' . now()->startOfMonth()->toDateString();
            } else {
                $startDateRule .= '|after_or_equal:today';
            }
        }

        $request->validate([
            'type' => 'required|in:Sakit,Izin Tidak Masuk,Izin Masuk Siang,Libur,Lupa Absen,Absen Diluar',
            'sub_type' => [
                'nullable',
                'required_if:type,Lupa Absen',
                'required_if:type,Absen Diluar',
                'required_if:type,Izin Tidak Masuk',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->type, ['Lupa Absen', 'Absen Diluar']) && !in_array($value, ['Absen Masuk', 'Absen Pulang'])) {
                        $fail('Sub tipe tidak valid untuk Lupa Absen atau Absen Diluar.');
                    }
                    if ($request->type === 'Izin Tidak Masuk' && !in_array($value, ['Sakit', 'Izin Tidak Masuk'])) {
                        $fail('Sub tipe tidak valid untuk Izin Tidak Masuk.');
                    }
                }
            ],
            'start_date' => $startDateRule,
            'end_date' => 'required|date|after_or_equal:start_date',
            'time_start' => 'nullable|required_if:type,Izin Masuk Siang|required_if:type,Lupa Absen|required_if:type,Absen Diluar',
            'time_end' => 'nullable|required_if:type,Izin Masuk Siang',
            'reason' => 'required|string|max:500',
        ]);

        // ── Batasan jatah Lupa Absen per bulan ─────────────────────────────
        // Dalam 1 bulan kalender, karyawan boleh mengajukan Lupa Absen maksimal
        // 3 kali, dihitung GABUNGAN antara Absen Masuk dan Absen Pulang.
        // Pengajuan yang DITOLAK tidak dihitung, jadi tetap bisa mengajukan ulang.
        $photoPath = null;
        $photoTakenAt = null;

        if ($request->type === 'Lupa Absen') {
            $startDate = Carbon::parse($request->start_date);
            $terpakai  = LeaveRequest::lupaAbsenUsedInMonth(Auth::id(), $startDate);
            $kuota     = LeaveRequest::LUPA_ABSEN_QUOTA_PER_MONTH;

            if ($terpakai >= $kuota) {
                $bulan = $startDate->locale('id')->isoFormat('MMMM Y');
                return redirect()->back()->withInput()->withErrors([
                    'type' => "Jatah Lupa Absen bulan {$bulan} sudah habis (terpakai {$terpakai} dari {$kuota} kali). Jatah akan direset otomatis di bulan berikutnya.",
                ]);
            }

            // ── Wajib foto bukti di counter (khusus Karyawan Ramayana) ──────
            // Foto diambil langsung dari kamera dan sudah dicap tanggal/jam +
            // nama counter oleh halaman pengajuan.
            if ($this->wajibFotoLupaAbsen()) {
                $result = $this->simpanFotoLupaAbsen($request);
                if ($result instanceof \Illuminate\Http\RedirectResponse) {
                    return $result;
                }
                [$photoPath, $photoTakenAt] = $result;
            }
        }
        // ────────────────────────────────────────────────────────────────────

        Auth::user()->leaveRequests()->create([
            'type' => $request->type,
            'sub_type' => $request->sub_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'reason' => $request->reason,
            'photo' => $photoPath,
            'photo_taken_at' => $photoTakenAt,
            'status' => 'pending',
        ]);

        // Arahkan kembali ke halaman izin sesuai peran user (karyawan / karyawan_ramayana),
        // bukan selalu ke rute 'karyawan.*'.
        $routeName = Auth::user()->role->slug . '.leave-requests.index';
        if (!\Illuminate\Support\Facades\Route::has($routeName)) {
            $routeName = 'karyawan.leave-requests.index';
        }

        return redirect()->route($routeName)->with('success', 'Pengajuan berhasil dibuat.');
    }

    /**
     * Apakah user wajib melampirkan foto saat mengajukan "Lupa Absen"?
     * Berlaku KHUSUS Karyawan Ramayana. Staff kantor & live streamer tidak wajib.
     */
    private function wajibFotoLupaAbsen(): bool
    {
        return Auth::user()->role->slug === 'karyawan_ramayana';
    }

    /**
     * Simpan foto bukti "Lupa Absen".
     *
     * PENTING: jalur utama sekarang adalah FILE UPLOAD ASLI (multipart, field
     * `photo`) — lihat dataUrlToFile() di halaman form untuk alasan lengkapnya:
     * mengirim foto sebagai teks base64 raksasa di field tersembunyi (jalur lama)
     * sering diblokir 403 oleh proteksi keamanan hosting (mod_security/WAF)
     * sebelum request itu sempat sampai ke Laravel. Jalur base64 (`photo_data`)
     * masih dipertahankan sebagai CADANGAN saja, untuk browser lama yang gagal
     * membuat file upload dari JavaScript.
     *
     * Mengembalikan [path, waktuPengambilan] jika berhasil, atau RedirectResponse
     * berisi pesan error jika gagal.
     *
     * @return array{0:string,1:\Carbon\Carbon}|\Illuminate\Http\RedirectResponse
     */
    private function simpanFotoLupaAbsen(Request $request)
    {
        if ($request->hasFile('photo')) {
            return $this->simpanFotoDariUpload($request);
        }

        return $this->simpanFotoDariBase64($request);
    }

    /**
     * Pastikan folder lupa-absen selalu ada dan memiliki izin tulis.
     */
    private function ensureLupaAbsenDirectoryExists(): string
    {
        $dir = public_path('storage/lupa-absen');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_dir($dir)) {
            @chmod($dir, 0775);
        }
        return $dir;
    }

    /**
     * Jalur UTAMA: foto dikirim sebagai file upload asli (multipart/form-data).
     */
    private function simpanFotoDariUpload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:6144', // maks ± 6 MB
        ], [
            'photo.image' => 'File yang dikirim bukan gambar. Silakan ambil ulang foto.',
            'photo.mimes' => 'Format foto tidak valid. Silakan ambil ulang foto menggunakan kamera.',
            'photo.max'   => 'Ukuran foto terlalu besar. Silakan ambil ulang foto.',
        ]);

        $dir = $this->ensureLupaAbsenDirectoryExists();

        $file = $request->file('photo');
        $fileName = 'lupa-absen/' . Auth::id() . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.jpg';

        $file->move($dir, basename($fileName));

        // Waktu pengambilan dicatat dari SERVER (bukan dari perangkat karyawan),
        // supaya tidak bisa dimanipulasi lewat pengaturan jam di HP.
        return [$fileName, now()];
    }

    /**
     * Jalur CADANGAN: foto dikirim sebagai data URL base64 (field `photo_data`).
     * Hanya dipakai kalau browser karyawan gagal membuat file upload asli.
     *
     * @return array{0:string,1:\Carbon\Carbon}|\Illuminate\Http\RedirectResponse
     */
    private function simpanFotoDariBase64(Request $request)
    {
        $dataUrl = $request->input('photo_data');

        if (empty($dataUrl)) {
            return redirect()->back()->withInput()->withErrors([
                'photo_data' => 'Foto bukti wajib diambil menggunakan kamera di counter Anda.',
            ]);
        }

        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $dataUrl, $m)) {
            return redirect()->back()->withInput()->withErrors([
                'photo_data' => 'Format foto tidak valid. Silakan ambil ulang foto menggunakan kamera.',
            ]);
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

        if ($binary === false || strlen($binary) < 1024) {
            return redirect()->back()->withInput()->withErrors([
                'photo_data' => 'Foto gagal diproses atau rusak. Silakan ambil ulang foto.',
            ]);
        }

        // Batas ukuran wajar agar tidak membebani penyimpanan (± 6 MB)
        if (strlen($binary) > 6 * 1024 * 1024) {
            return redirect()->back()->withInput()->withErrors([
                'photo_data' => 'Ukuran foto terlalu besar. Silakan ambil ulang foto.',
            ]);
        }

        $dir = $this->ensureLupaAbsenDirectoryExists();
        $fileName = 'lupa-absen/' . Auth::id() . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.jpg';

        if (file_put_contents(public_path('storage/' . $fileName), $binary) === false) {
            return redirect()->back()->withInput()->withErrors([
                'photo_data' => 'Foto gagal disimpan di server. Silakan coba lagi.',
            ]);
        }

        // Waktu pengambilan dicatat dari SERVER (bukan dari perangkat karyawan),
        // supaya tidak bisa dimanipulasi lewat pengaturan jam di HP.
        return [$fileName, now()];
    }
}
