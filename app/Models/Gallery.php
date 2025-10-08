<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    //
    protected $fillable = ['name', 'description', 'activity_date'];

    public function images() {
        return $this->hasMany(GalleryImage::class);
    }
}
