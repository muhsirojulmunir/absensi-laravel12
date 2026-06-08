<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInput extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'sku',
        'kode_barang',
        'size',
        'warna',
        'nominal',
        'qty',
        'satuan',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStockIn($query)
    {
        return $query->where('type', 'stock_in');
    }

    public function scopeSale($query)
    {
        return $query->where('type', 'sale');
    }
}
