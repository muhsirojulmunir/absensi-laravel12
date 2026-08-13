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

    protected $fillable = [
        'user_id',
        'type',
        'sub_type',
        'start_date',
        'end_date',
        'time_start',
        'time_end',
        'reason',
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
}
