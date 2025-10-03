<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order for the admin panel.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $shop = Shop::query()->find($data['shop_id'] ?? null);

            if (! $shop) {
                throw ValidationException::withMessages([
                    'shop_id' => [__('cms.orders.validation.shop_required')],
                ]);
            }

            $customerId = $data['customer_id'] ?? null;
            $guestEmail = $customerId ? null : (isset($data['guest_email']) ? trim((string) $data['guest_email']) : null);
            $status = $data['status'] ?? 'pending';

            $itemsInput = collect($data['items'] ?? [])
                ->map(function ($item) {
                    return [
                        'product_id' => (int) ($item['product_id'] ?? 0),
                        'quantity' => (int) ($item['quantity'] ?? 0),
                        'unit_price' => isset($item['unit_price']) && $item['unit_price'] !== ''
                            ? (float) $item['unit_price']
                            : null,
                    ];
                })
                ->filter(fn (array $item) => $item['product_id'] > 0 && $item['quantity'] > 0)
                ->values();

            if ($itemsInput->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => [__('cms.orders.validation.items_required')],
                ]);
            }

            $productIds = $itemsInput->pluck('product_id')->all();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            if ($products->count() !== count(array_unique($productIds))) {
                throw ValidationException::withMessages([
                    'items' => [__('cms.orders.validation.items_invalid')],
                ]);
            }

            $invalidProduct = $products->first(function (Product $product) use ($shop) {
                return (int) $product->shop_id !== (int) $shop->id;
            });

            if ($invalidProduct) {
                $productName = $invalidProduct->translation?->name
                    ?? $invalidProduct->slug
                    ?? __('cms.orders.validation.unknown_product');

                throw ValidationException::withMessages([
                    'items' => [__('cms.orders.validation.product_not_in_shop', ['product' => $productName, 'shop' => $shop->name])],
                ]);
            }

            $orderTotal = 0.0;
            $detailsPayload = [];

            foreach ($itemsInput as $item) {
                /** @var \App\Models\Product $product */
                $product = $products[$item['product_id']];
                $quantity = max($item['quantity'], 1);
                $defaultPrice = (float) ($product->price ?? 0);
                $unitPrice = $item['unit_price'] !== null
                    ? max($item['unit_price'], 0)
                    : $defaultPrice;

                $lineTotal = $unitPrice * $quantity;
                $orderTotal += $lineTotal;

                $detailsPayload[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => number_format($unitPrice, 2, '.', ''),
                ];
            }

            if ($orderTotal <= 0) {
                throw ValidationException::withMessages([
                    'items' => [__('cms.orders.validation.total_must_be_positive')],
                ]);
            }

            $order = Order::create([
                'shop_id' => $shop->id,
                'customer_id' => $customerId,
                'guest_email' => $guestEmail ?: null,
                'total_amount' => number_format($orderTotal, 2, '.', ''),
                'status' => $status,
            ]);

            foreach ($detailsPayload as $detail) {
                $order->details()->create($detail);
            }

            $shipping = $data['shipping'] ?? [];
            $hasShipping = collect($shipping)
                ->filter(fn ($value) => filled($value))
                ->isNotEmpty();

            if ($hasShipping) {
                $order->shippingAddress()->updateOrCreate([], [
                    'customer_id' => $customerId,
                    'name' => Arr::get($shipping, 'name'),
                    'phone' => Arr::get($shipping, 'phone'),
                    'address' => Arr::get($shipping, 'address'),
                    'city' => Arr::get($shipping, 'city'),
                    'postal_code' => Arr::get($shipping, 'postal_code'),
                    'country' => Arr::get($shipping, 'country'),
                ]);
            }

            return $order->fresh([
                'customer',
                'shop',
                'details.product.translation',
            ]);
        });
    }
}
