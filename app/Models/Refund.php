<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Payment|null $payment
 */
class Refund extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_PENDING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
    ];

    private const STATUS_BADGE_CLASSES = [
        self::STATUS_COMPLETED => 'bg-emerald-50 text-emerald-700 ring-emerald-500/20',
        self::STATUS_PENDING => 'bg-amber-50 text-amber-700 ring-amber-500/20',
        self::STATUS_FAILED => 'bg-rose-50 text-rose-700 ring-rose-500/20',
        self::STATUS_APPROVED => 'bg-sky-50 text-sky-700 ring-sky-500/20',
        self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 ring-rose-500/20',
        self::STATUS_REQUESTED => 'bg-indigo-50 text-indigo-700 ring-indigo-500/20',
    ];

    protected $fillable = [
        'payment_id',
        'amount',
        'currency',
        'status',
        'refund_id',
        'reason',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_REQUESTED => __('cms.refunds.status_labels.requested'),
            self::STATUS_APPROVED => __('cms.refunds.status_labels.approved'),
            self::STATUS_REJECTED => __('cms.refunds.status_labels.rejected'),
            self::STATUS_PENDING => __('cms.refunds.status_labels.pending'),
            self::STATUS_COMPLETED => __('cms.refunds.status_labels.completed'),
            self::STATUS_FAILED => __('cms.refunds.status_labels.failed'),
        ];
    }

    public static function badgeClassForStatus(?string $status): string
    {
        if (! is_string($status)) {
            return 'bg-gray-100 text-gray-700 ring-gray-500/10';
        }

        return self::STATUS_BADGE_CLASSES[strtolower($status)] ?? 'bg-gray-100 text-gray-700 ring-gray-500/10';
    }

    public static function labelForStatus(?string $status): string
    {
        if (! is_string($status)) {
            return __('cms.refunds.status_labels.pending');
        }

        $status = strtolower($status);

        return match ($status) {
            self::STATUS_REQUESTED => __('cms.refunds.status_labels.requested'),
            self::STATUS_APPROVED => __('cms.refunds.status_labels.approved'),
            self::STATUS_REJECTED => __('cms.refunds.status_labels.rejected'),
            self::STATUS_PENDING => __('cms.refunds.status_labels.pending'),
            self::STATUS_COMPLETED => __('cms.refunds.status_labels.completed'),
            self::STATUS_FAILED => __('cms.refunds.status_labels.failed'),
            default => ucfirst($status),
        };
    }

    public function scopeWithStatuses(Builder $query, array $statuses): Builder
    {
        $statuses = array_values(array_filter($statuses, function ($value) {
            if (! is_string($value)) {
                return false;
            }

            return in_array(strtolower($value), self::STATUSES, true);
        }));

        if (empty($statuses)) {
            return $query;
        }

        return $query->whereIn('status', $statuses);
    }

    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
