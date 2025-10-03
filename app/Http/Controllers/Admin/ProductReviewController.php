<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateProductReviewRequest;
use App\Http\Requests\Admin\ProductReviewDataRequest;
use App\Http\Requests\Admin\UpdateProductReviewRequest;
use App\Http\Requests\Admin\UpdateProductReviewStatusRequest;
use App\Models\ProductReview;
use App\Services\Admin\ProductReviewMetricsService;
use App\Support\Filters\ProductReviewFilters;
use Carbon\Carbon;
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

        $minRating = (int) $request->query('rating_min', 0);
        $maxRating = (int) $request->query('rating_max', 5);

        if ($minRating > 0) {
            $reviews->where('rating', '>=', $minRating);
        }

        if ($maxRating > 0) {
            $reviews->where('rating', '<=', $maxRating);
        }

        $search = (string) $request->query('keyword');

        if ($search !== '') {
            $reviews->where(function ($query) use ($search) {
                $query
                    ->where('review', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where(function ($inner) use ($search) {
                            $inner->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    })
                    ->orWhereHas('product.translations', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $productId = (int) $request->query('product_id');

        if ($productId > 0) {
            $reviews->where('product_id', $productId);
        }

        $submittedFrom = $request->query('submitted_from');
        $submittedTo = $request->query('submitted_to');

        if ($submittedFrom && Carbon::hasFormat($submittedFrom, 'Y-m-d')) {
            $reviews->whereDate('created_at', '>=', Carbon::createFromFormat('Y-m-d', $submittedFrom));
        }

        if ($submittedTo && Carbon::hasFormat($submittedTo, 'Y-m-d')) {
            $reviews->whereDate('created_at', '<=', Carbon::createFromFormat('Y-m-d', $submittedTo));
        }

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
            ->addColumn('review_excerpt', function (ProductReview $review) {
                return Str::limit((string) $review->review, 120);
            })
            ->addColumn('submitted_at', function (ProductReview $review) {
                return optional($review->created_at)?->timezone(config('app.timezone'))
                    ?->format('M j, Y g:i A');
            })
            ->addColumn('status', function (ProductReview $review) {
                return $review->status_label;
            })
            ->editColumn('created_at', function (ProductReview $review) {
                return optional($review->created_at)->format('Y-m-d H:i');
            })
            ->addColumn('is_approved', function (ProductReview $review) {
                return (bool) $review->is_approved;
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
=======
    public function updateApproval(UpdateProductReviewStatusRequest $request, ProductReview $review)
    {
        $validated = $request->validated();

        $review->forceFill([
            'is_approved' => $validated['is_approved'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' => __('cms.product_reviews.success_status_update'),
            'is_approved' => $review->is_approved,
            'status_label' => $review->status_label,
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features
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
