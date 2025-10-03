<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending',
        'processing',
        'completed',
        'failed',
        'refunded',
    ];

    protected $fillable = [
        'order_id',
        'gateway_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'response',
        'meta',
    ];

    protected $casts = [
        'response' => 'array',
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function scopeForGateway($query, ?int $gatewayId)
    {
        return $query->when($gatewayId, fn ($builder) => $builder->where('gateway_id', $gatewayId));
    }

    public function scopeForStatus($query, ?string $status)
    {
        return $query->when(
            $status,
            fn ($builder) => $builder->where('status', $status)
        );
    }

    public function scopeForShop($query, ?int $shopId)
    {
        return $query->when($shopId, function ($builder) use ($shopId) {
            $builder->whereHas('order.details.product', function ($relationQuery) use ($shopId) {
                $relationQuery->where('shop_id', $shopId);
            });
        });
    }

    public function getCustomerDisplayNameAttribute(): ?string
    {
        if ($this->relationLoaded('order') && $this->order) {
            if ($this->order->relationLoaded('customer') && $this->order->customer) {
                return $this->order->customer->name;
            }

            if ($this->order->guest_email) {
                return $this->order->guest_email;
            }
        }

        return null;
    }

    public function getShopNamesAttribute(): array
    {
        if (! $this->relationLoaded('order') || ! $this->order) {
            return [];
        }

        $details = $this->order->relationLoaded('details')
            ? $this->order->details
            : $this->order->details()->with('product.shop')->get();

        if ($details->isEmpty()) {
            return [];
        }

        return $details
            ->loadMissing('product.shop')
            ->map(fn ($detail) => $detail->product?->shop?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
