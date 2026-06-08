<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius',
        'google_maps_url',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
