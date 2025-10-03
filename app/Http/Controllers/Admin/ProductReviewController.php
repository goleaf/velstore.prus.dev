<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateProductReviewRequest;
use App\Http\Requests\Admin\ProductReviewDataRequest;
use App\Http\Requests\Admin\UpdateProductReviewRequest;
use App\Models\ProductReview;
use App\Services\Admin\ProductReviewMetricsService;
use App\Support\Filters\ProductReviewFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductReviewController extends Controller
{
    public function __construct(private readonly ProductReviewMetricsService $metricsService)
    {
    }

    public function index(Request $request)
    {
        return view('admin.reviews.index', [
            'metrics' => $this->metricsService->getOverview(),
            'topProducts' => $this->metricsService->topProducts(),
            'recentReviews' => $this->metricsService->recentReviews(),
        ]);
    }

    public function getData(ProductReviewDataRequest $request, ProductReviewFilters $filters)
    {
        $reviews = ProductReview::query()
            ->with([
                'customer:id,name,email',
                'product.translation:id,product_id,name,language_code',
                'product.translations' => function ($query) {
                    $query->select('id', 'product_id', 'language_code', 'name');
                },
            ])
            ->select('product_reviews.*');

        $filters->apply($reviews, $request->filters());

        return DataTables::of($reviews)
            ->filter(function ($query) use ($request) {
                $search = (string) $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('product_reviews.review', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($relation) use ($search) {
                            $relation->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product.translations', function ($relation) use ($search) {
                            $relation->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->order(function ($query) use ($request) {
                if ($request->input('order.0.column') === null) {
                    $query->latest();

                    return;
                }
            })
            ->addColumn('customer_name', function (ProductReview $review) {
                return $review->customer_display_name;
            })
            ->addColumn('product_name', function (ProductReview $review) {
                return $review->product_display_name;
            })
            ->addColumn('review_excerpt', function (ProductReview $review) {
                return Str::limit((string) $review->review, 60);
            })
            ->editColumn('rating', function (ProductReview $review) {
                return number_format((float) $review->rating, 1);
            })
            ->addColumn('status', function (ProductReview $review) {
                return $review->status_label;
            })
            ->editColumn('created_at', function (ProductReview $review) {
                return optional($review->created_at)->format('Y-m-d H:i');
            })
            ->toJson();
    }

    public function metrics(): JsonResponse
    {
        return response()->json([
            'metrics' => $this->metricsService->getOverview(),
            'top_products' => $this->metricsService->topProducts(),
            'recent_reviews' => $this->metricsService->recentReviews(),
        ]);
    }

    public function show(ProductReview $review)
    {
        return view('admin.reviews.show', compact('review'));
    }

    public function edit(ProductReview $review)
    {
        return view('admin.reviews.edit', compact('review'));
    }

    public function update(UpdateProductReviewRequest $request, ProductReview $review)
    {
        $validated = $request->validated();

        $isApproved = array_key_exists('is_approved', $validated)
            ? filter_var($validated['is_approved'], FILTER_VALIDATE_BOOLEAN)
            : false;

        $review->fill([
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'is_approved' => $isApproved,
        ])->save();

        return redirect()
            ->route('admin.reviews.show', $review)
            ->with('success', __('cms.product_reviews.success_update'));
    }

    public function bulkAction(BulkUpdateProductReviewRequest $request): JsonResponse
    {
        $ids = $request->reviewIds();
        $action = $request->action();

        $query = ProductReview::query()->whereIn('id', $ids);

        $updated = match ($action) {
            'approve' => $query->update(['is_approved' => true]),
            'unapprove' => $query->update(['is_approved' => false]),
            'delete' => ProductReview::destroy($ids),
            default => 0,
        };

        return response()->json([
            'success' => true,
            'updated' => (int) $updated,
            'action' => $action,
            'message' => __('cms.product_reviews.bulk_action_success', ['count' => (int) $updated]),
        ]);
    }

    public function destroy(ProductReview $review)
    {
        try {
            $review->delete();

            return response()->json(['success' => true, 'message' => __('cms.product_reviews.success_delete')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('cms.product_reviews.error_delete')]);
        }
    }
}
