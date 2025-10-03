<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

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

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }
}
