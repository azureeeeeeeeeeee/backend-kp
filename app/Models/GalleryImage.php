<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    //
    protected $table = "gallery_media";
    protected $fillable = ['gallery_id', 'path'];

    public function gallery() {
        return $this->belongsTo(Gallery::class);
    }
}
