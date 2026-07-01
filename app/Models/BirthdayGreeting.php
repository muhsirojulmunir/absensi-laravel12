<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayGreeting extends Model
{
    protected $fillable = [
        'birthday_user_id',
        'sender_id',
        'message',
        'year',
    ];

    public function birthdayUser()
    {
        return $this->belongsTo(User::class, 'birthday_user_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
