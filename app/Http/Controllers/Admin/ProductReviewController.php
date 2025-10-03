<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductReviewRequest;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.reviews.index');
    }

    public function getData(Request $request)
    {
        $reviews = ProductReview::query()
            ->with([
                'customer:id,name,email',
                'product.translation:id,product_id,name,language_code',
                'product.translations' => function ($query) {
                    $query->select('id', 'product_id', 'language_code', 'name');
                },
            ])
            ->select('product_reviews.*')
            ->latest();

        $status = $request->query('status');

        if (in_array($status, ['approved', 'pending'], true)) {
            $reviews->where('is_approved', $status === 'approved');
        }

        return DataTables::of($reviews)
            ->addColumn('customer_name', function (ProductReview $review) {
                return $review->customer_display_name;
            })
            ->addColumn('product_name', function (ProductReview $review) {
                return $review->product_display_name;
            })
            ->editColumn('rating', function (ProductReview $review) {
                return number_format((float) $review->rating, 1);
            })
            ->addColumn('status', function (ProductReview $review) {
                return $review->status_label;
            })
            ->toJson();
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
