<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $products = $user->wishlistProducts()
            ->with(['translation', 'thumbnail', 'primaryVariant', 'reviews'])
            ->withCount('reviews')
            ->orderBy('wishlists.created_at', 'desc')
            ->get();

        return view('wishlist.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $customer = auth('customer')->user();

        // Check if product already in wishlist
        $exists = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already in wishlist'], 200);
        }

        Wishlist::create([
            'customer_id' => $customer->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Added to wishlist'], 200);
    }
}
