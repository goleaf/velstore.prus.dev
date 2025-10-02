<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'language_code', 'name', 'description', 'image_url'];

    protected static function booted(): void
    {
        static::saving(function (self $translation) {
            if (! $translation->image_url) {
                $translation->image_url = 'assets/images/placeholder-promo.svg';
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
