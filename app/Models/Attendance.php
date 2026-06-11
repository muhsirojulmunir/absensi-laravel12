<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder|Attendance where(string $column, mixed $operator = null, mixed $value = null)
 * @method static Builder|Attendance whereDate(string $column, mixed $value)
 * @method static Builder|Attendance whereNotNull(string $column)
 * @method static Builder|Attendance whereNull(string $column)
 * @method static Attendance|null first()
 * @method static Builder|Attendance query()
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
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
