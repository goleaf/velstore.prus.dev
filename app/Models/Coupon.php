<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'type',
        'minimum_spend',
        'usage_limit',
        'usage_count',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'minimum_spend' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
    ];

    public function isExpired()
    {
        return $this->expires_at ? $this->expires_at->isPast() : false;
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        $now = now();

        if ($this->expires_at->isPast()) {
            return false;
        }

        return $this->expires_at->lte($now->copy()->addDays($days));
    }

    public function hasReachedUsageLimit(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }

        return $this->usage_count >= $this->usage_limit;
    }

    public function meetsMinimumSpend(float $amount): bool
    {
        if ($this->minimum_spend === null) {
            return true;
        }

        return $amount >= (float) $this->minimum_spend;
    }
}
