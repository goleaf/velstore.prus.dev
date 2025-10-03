<?php

namespace App\Support\Filters;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class ProductReviewFilters
{
    public function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('is_approved', $filters['status'] === 'approved');
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['product_name'])) {
            $name = $filters['product_name'];

            $query->whereHas('product.translations', function ($relation) use ($name) {
                $relation->where('name', 'like', "%{$name}%");
            });
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $ratingMin = $filters['rating_min'] ?? null;
        $ratingMax = $filters['rating_max'] ?? null;

        if ($ratingMin && $ratingMax && $ratingMin > $ratingMax) {
            [$ratingMin, $ratingMax] = [$ratingMax, $ratingMin];
        }

        if ($ratingMin) {
            $query->where('rating', '>=', (int) $ratingMin);
        }

        if ($ratingMax) {
            $query->where('rating', '<=', (int) $ratingMax);
        }

        if (! empty($filters['has_review'])) {
            $query->whereNotNull('review')->where('review', '<>', '');
        }

        if (! empty($filters['date_from'])) {
            $from = CarbonImmutable::parse($filters['date_from'])->startOfDay();
            $query->where('created_at', '>=', $from);
        }

        if (! empty($filters['date_to'])) {
            $to = CarbonImmutable::parse($filters['date_to'])->endOfDay();
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }
}

