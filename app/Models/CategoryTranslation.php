<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    use HasFactory;

    public const DEFAULT_IMAGE_PATH = 'assets/images/placeholder-promo.svg';

    protected $fillable = ['category_id', 'language_code', 'name', 'description', 'image_url'];

    protected static function booted(): void
    {
        static::saving(function (CategoryTranslation $translation) {
            $translation->image_url = $translation->image_url ?? self::DEFAULT_IMAGE_PATH;
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
