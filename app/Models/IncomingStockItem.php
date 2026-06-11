<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingStockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_stock_id',
        'sku',
        'kode_barang',
        'size',
        'warna',
        'satuan',
        'qty',
    ];

    public function incomingStock()
    {
        return $this->belongsTo(IncomingStock::class);
    }
}
