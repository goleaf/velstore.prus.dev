<?php

namespace App\Services\Admin;

use App\Models\ProductReview;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProductReviewMetricsService
{
    public function getOverview(): array
    {
        $baseQuery = ProductReview::query();

        $total = (clone $baseQuery)->count();
        $approved = (clone $baseQuery)->where('is_approved', true)->count();
        $pending = (clone $baseQuery)->where('is_approved', false)->count();
        $averageRating = round((clone $baseQuery)->avg('rating') ?? 0, 2);
        $latestReviewAt = (clone $baseQuery)->max('created_at');

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'average_rating' => $averageRating,
            'latest_review_at' => $latestReviewAt ? Carbon::parse($latestReviewAt) : null,
            'rating_distribution' => $this->ratingDistribution(),
        ];
    }

    public function topProducts(int $limit = 5): Collection
    {
        return ProductReview::query()
            ->selectRaw('product_id, AVG(rating) as avg_rating, COUNT(*) as reviews_count')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->with(['product.translation' => function ($query) {
                $query->select('id', 'product_id', 'name', 'language_code');
            }])
            ->orderByDesc('avg_rating')
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get()
            ->map(function (ProductReview $review) {
                $product = $review->product;

                return [
                    'product_id' => $review->product_id,
                    'product_name' => $product?->translation?->name ?? __('cms.product_reviews.missing_product'),
                    'avg_rating' => round((float) $review->avg_rating, 2),
                    'reviews_count' => (int) $review->reviews_count,
                    'approved_percentage' => $this->approvedPercentage($review->product_id),
                ];
            })
            ->values();
    }

    public function recentReviews(int $limit = 5): Collection
    {
        return ProductReview::query()
            ->with(['customer:id,name,email', 'product.translation:id,product_id,name,language_code'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (ProductReview $review) {
                return [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'status' => $review->status_label,
                    'created_at' => $review->created_at,
                    'created_at_human' => optional($review->created_at)->diffForHumans(),
                    'customer' => $review->customer_display_name,
                    'product' => $review->product_display_name,
                ];
            })
            ->values();
    }

    protected function ratingDistribution(): array
    {
        $distribution = array_fill(1, 5, 0);

        ProductReview::query()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->each(function ($total, $rating) use (&$distribution) {
                $distribution[(int) $rating] = (int) $total;
            });

        return $distribution;
    }

    protected function approvedPercentage(int $productId): float
    {
        $query = ProductReview::query()->where('product_id', $productId);

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0.0;
        }

        $approved = (clone $query)->where('is_approved', true)->count();

        return round(($approved / $total) * 100, 1);
    }
}

