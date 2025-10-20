<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PPDBSetting extends Model
{
    protected $table = 'ppdb_settings';
    
    protected $fillable = [
        'status',
        'message',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];
}