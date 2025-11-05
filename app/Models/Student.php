<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'religion',
        'father_name',
        'mother_name',
        'file_foto',
        'year',
    ];

    public static function generateNIS($year)
    {
        $lastStudent = self::where('year', $year)->orderBy('id', 'desc')->first();

        $nextNumber = $lastStudent ? ((int) substr($lastStudent->nis, -3)) + 1 : 1;

        return $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
