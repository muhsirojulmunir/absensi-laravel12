<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceEvaluation extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'attendance_summary',
        'sales_summary',
        'predicate',
        'ai_feedback',
        'ai_recommendation',
    ];

    protected $casts = [
        'attendance_summary' => 'array',
        'sales_summary' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
