<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'language_code',
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'image_url',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
