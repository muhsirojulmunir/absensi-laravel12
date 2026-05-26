<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedAttendance extends Model
{
    protected $fillable = [
        'original_attendance_id',
        'user_id',
        'user_name',
        'division_name',
        'date',
        'payload',
        'deleted_by',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'payload' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
