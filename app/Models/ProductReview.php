<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'product_id', 'rating', 'review', 'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Determine the status label that should be used for this review.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_approved ? 'approved' : 'pending';
    }

    /**
     * Return the badge class that should be used for the current status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_approved ? 'badge badge-success' : 'badge badge-warning';
    }

    /**
     * Resolve the display name for the customer associated with the review.
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        $customer = $this->customer;

        if (! $customer) {
            return __('cms.product_reviews.missing_customer');
        }

        return $customer->name ?: ($customer->email ?? __('cms.product_reviews.missing_customer'));
    }

    /**
     * Resolve the display name for the product associated with the review.
     */
    public function getProductDisplayNameAttribute(): string
    {
        $product = $this->product;

        if (! $product) {
            return __('cms.product_reviews.missing_product');
        }

        if ($product->relationLoaded('translation') && $product->translation) {
            return $product->translation->name ?? __('cms.product_reviews.missing_product');
        }

        $translation = $product->translation()->first();

        if ($translation && $translation->name) {
            return $translation->name;
        }

        $fallback = $product->translations()->first();

        return $fallback?->name ?? __('cms.product_reviews.missing_product');
    }

    /**
     * Get the customer that owns the review.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the product that the review belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', 1);
    }
}
