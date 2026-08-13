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

        return view('karyawan.leave-requests.index', compact(
            'leaveRequests',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'unreadMessages'
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

        // ── Cek batasan 1 kali sebulan untuk Lupa Absen (Masuk/Pulang) ─────
        // Rule: dalam 1 bulan kalender, user hanya boleh 1x mengajukan
        // Lupa Absen (baik Absen Masuk maupun Absen Pulang).
        // Rejected tidak dihitung, jadi user tetap bisa mengajukan ulang.
        if ($request->type === 'Lupa Absen') {
            $startDate = Carbon::parse($request->start_date);

            $alreadyExistsThisMonth = Auth::user()->leaveRequests()
                ->where('type', 'Lupa Absen')
                ->whereIn('status', ['pending', 'approved'])          // rejected tidak dihitung
                ->whereMonth('start_date', $startDate->month)         // ← fix: pakai start_date
                ->whereYear('start_date', $startDate->year)           // ← fix: pakai start_date
                ->exists();

            if ($alreadyExistsThisMonth) {
                $bulan = $startDate->locale('id')->isoFormat('MMMM Y');
                return redirect()->back()->withInput()->withErrors([
                    'type' => "Anda sudah menggunakan jatah Lupa Absen (Absen Masuk/Pulang) untuk bulan {$bulan}. Kesempatan hanya 1 kali per bulan dan akan reset di bulan berikutnya.",
                ]);
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
            'status' => 'pending',
        ]);

        return redirect()->route('karyawan.leave-requests.index')->with('success', 'Pengajuan berhasil dibuat.');
    }
}
