<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'note',
        'total_items',
        'total_qty',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_qty' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(IncomingStockItem::class);
    }
}
