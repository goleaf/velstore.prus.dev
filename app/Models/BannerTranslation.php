<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTranslation extends Model
{
    use HasFactory;

    // Define the fillable attributes
    protected $fillable = ['banner_id', 'language_code', 'title', 'description', 'image_url', 'type'];

    protected static function booted(): void
    {
        static::saving(function (self $translation) {
            if (! $translation->image_url) {
                $translation->image_url = 'assets/images/placeholder-banner.svg';
            }
        });
    }

    // Relationship with Banner
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
