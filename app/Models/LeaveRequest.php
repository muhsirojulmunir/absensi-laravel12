<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * Tipe pengajuan yang sifatnya "satu momen" (satu tanggal + satu jam), bukan rentang.
     * Untuk tipe ini end_date SELALU dipaksa sama dengan start_date.
     */
    public const SINGLE_DAY_TYPES = ['Lupa Absen', 'Absen Diluar'];

    /**
     * Jatah maksimal pengajuan "Lupa Absen" dalam 1 bulan kalender.
     * Dihitung GABUNGAN antara Absen Masuk dan Absen Pulang.
     */
    public const LUPA_ABSEN_QUOTA_PER_MONTH = 3;

    protected $fillable = [
        'user_id',
        'type',
        'sub_type',
        'start_date',
        'end_date',
        'time_start',
        'time_end',
        'reason',
        'photo',
        'photo_taken_at',
        'status', // pending, approved, rejected
        'approved_by',
        'admin_message',
        'admin_message_by',
        'admin_message_at',
        'admin_message_read_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'admin_message_at' => 'datetime',
        'admin_message_read_at' => 'datetime',
        'photo_taken_at' => 'datetime',
        // Optional: cast time to string to avoid carbon errors if we only care about 'H:i'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function messageSender()
    {
        return $this->belongsTo(User::class, 'admin_message_by');
    }

    /**
     * Apakah tipe ini bersifat satu hari saja (Lupa Absen / Absen Diluar)?
     */
    public function isSingleDayType(): bool
    {
        return in_array($this->type, self::SINGLE_DAY_TYPES, true);
    }

    /**
     * Jumlah hari izin (inklusif). Contoh: 13–14 = 2 hari, 13–13 = 1 hari.
     */
    public function getTotalDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 1;
        }

        if ($this->isSingleDayType()) {
            return 1;
        }

        return $this->start_date->startOfDay()->diffInDays($this->end_date->startOfDay()) + 1;
    }

    /**
     * Apakah ada pesan dari Super Admin / PIC yang belum dibaca karyawan?
     */
    public function hasUnreadAdminMessage(): bool
    {
        return !empty($this->admin_message) && is_null($this->admin_message_read_at);
    }

    /**
     * Hitung berapa kali user sudah memakai jatah "Lupa Absen" pada bulan tertentu.
     * Dihitung GABUNGAN (Absen Masuk + Absen Pulang).
     * Pengajuan yang DITOLAK tidak dihitung, sehingga karyawan tetap bisa mengajukan ulang.
     */
    public static function lupaAbsenUsedInMonth(int $userId, \Carbon\Carbon $month): int
    {
        return static::where('user_id', $userId)
            ->where('type', 'Lupa Absen')
            ->whereIn('status', ['pending', 'approved'])
            ->whereMonth('start_date', $month->month)
            ->whereYear('start_date', $month->year)
            ->count();
    }

    /**
     * Sisa jatah "Lupa Absen" bulan tersebut (tidak pernah negatif).
     */
    public static function lupaAbsenRemainingInMonth(int $userId, \Carbon\Carbon $month): int
    {
        return max(0, self::LUPA_ABSEN_QUOTA_PER_MONTH - static::lupaAbsenUsedInMonth($userId, $month));
    }
}
