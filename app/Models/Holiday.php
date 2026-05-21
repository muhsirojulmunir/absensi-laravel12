<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'description', 'division_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
