<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = 'otps';

    protected $fillable = [
        'token',
        'user_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
