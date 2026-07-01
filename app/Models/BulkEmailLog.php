<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkEmailLog extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'status',
        'error_message',
        'sent_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
