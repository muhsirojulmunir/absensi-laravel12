<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'date',
        'status',
        'is_pulang_cepat',
        'lat',
        'long',
        'photo',
        'lateness_minutes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
