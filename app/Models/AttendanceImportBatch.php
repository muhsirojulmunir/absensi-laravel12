<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'imported_count',
        'status',
        'created_ids',
        'updated_snapshots',
        'reverted_by',
        'reverted_at',
    ];

    protected function casts(): array
    {
        return [
            'created_ids' => 'array',
            'updated_snapshots' => 'array',
            'reverted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revertedByUser()
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }
}
