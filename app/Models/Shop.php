<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'vendor_id',
        'seller_id',
        'name',
        'slug',
        'logo',
        'description',
        'status',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($shop) {
            if (blank($shop->slug)) {
                $shop->slug = Str::slug($shop->name);
            }
        });
    }

    public static function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value) ?: Str::slug(Str::random(8));
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
