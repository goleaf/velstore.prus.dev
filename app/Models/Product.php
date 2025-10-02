<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'shop_id',
        'vendor_id',
        'price',
        'discount_price',
        'stock',
        'status',
        'slug',
        'currency',
        'SKU',
        'weight',
        'dimensions',
        'product_type',
        'image_url',
    ];

    /**
     * Get the translations for the product.
     */
    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('language_code', App::getLocale());
    }

    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // One-to-many relationship with ProductImage
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the brand for the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getTranslation($field, $locale = 'en')
    {
        $translation = $this->translations->firstWhere('language_code', $locale);

        return $translation ? $translation->$field : null;
    }

    public function thumbnail()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'thumb');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->approved()->latest();
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getConvertedPriceAttribute()
    {
        return convert_price($this->price);
    }

    public function getConvertedDiscountPriceAttribute()
    {
        return $this->discount_price ? convert_price($this->discount_price) : null;
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /*public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values', 'product_id', 'attribute_value_id');
    }*/
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->with('attribute', 'translations');
    }

    public function primaryVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_primary', true);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(Customer::class, 'wishlists');
    }
}
