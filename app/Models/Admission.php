<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = [
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'religion',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'paud',
        'file_kk',
        'file_akta',
        'file_foto',
        'status',
        'year',
        'admission_code',
    ];
}
