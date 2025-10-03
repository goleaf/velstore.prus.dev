<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Create a new order from PayPal payment and session cart.
     *
     * @param  array  $paypalResult  The PayPal API capture result
     */
    public function createOrderFromPaypal(array $paypalResult): Order
    {
        // Extract PayPal payer & order data
        $payer = $paypalResult['payer'] ?? [];
        $purchaseUnit = $paypalResult['purchase_units'][0] ?? [];
        $amount = $purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0;

        $cart = session('cart', []);
        $productIds = array_map('intval', array_keys($cart));
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        $shopIds = collect($cart)
            ->map(function ($item, $productId) use ($products) {
                $product = $products[(int) $productId] ?? null;

                return $product?->shop_id;
            })
            ->filter()
            ->unique();
        $shopId = $shopIds->count() === 1 ? $shopIds->first() : null;

        // Create Order
        $order = Order::create([
            'customer_id' => Auth::check() ? Auth::id() : null,
            'guest_email' => $payer['email_address'] ?? null,
            'shop_id' => $shopId,
            'total_amount' => $amount,
            'status' => 'completed',
        ]);

        // Create Order Details from cart session
        foreach ($cart as $productId => $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Create Shipping Address if available
        $shippingData = session('checkout.shipping');
        if ($shippingData) {
            ShippingAddress::create([
                'order_id' => $order->id,
                'customer_id' => Auth::check() ? Auth::id() : null,
                'name' => $shippingData['name'],
                'phone' => $shippingData['phone'],
                'address' => $shippingData['address'],
                'city' => $shippingData['city'],
                'postal_code' => $shippingData['postal_code'],
                'country' => $shippingData['country'],
            ]);
        }

        // Clear cart session
        session()->forget(['cart', 'checkout.shipping']);

        return $order;
    }
}
