<?php

namespace App\Support\Refunds;

use App\Models\Refund;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RefundDashboardService
{
    public function __construct(private readonly Refund $refund)
    {
    }

    public function summary(RefundFilters $filters): array
    {
        $baseQuery = $this->baseQuery($filters);

        $total = (clone $baseQuery)->count();
        $completedQuery = (clone $baseQuery)->where('status', Refund::STATUS_COMPLETED);
        $completed = $completedQuery->count();
        $refundedAmount = (clone $completedQuery)->sum('amount');

        $pending = (clone $baseQuery)
            ->whereIn('status', [
                Refund::STATUS_PENDING,
                Refund::STATUS_REQUESTED,
                Refund::STATUS_APPROVED,
            ])
            ->count();

        $averageAmount = (clone $baseQuery)->avg('amount');
        $highestRefund = (clone $baseQuery)->orderByDesc('amount')->select(['amount', 'currency'])->first();
        $latestRefund = (clone $baseQuery)->latest('created_at')->first();

        return [
            'total' => $total,
            'completed' => $completed,
            'refunded_amount' => round((float) $refundedAmount, 2),
            'pending' => $pending,
            'average_amount' => $averageAmount ? round((float) $averageAmount, 2) : 0.0,
            'highest_amount' => $highestRefund ? (float) $highestRefund->amount : null,
            'highest_amount_currency' => $highestRefund?->currency,
            'latest_refund_at' => $latestRefund?->created_at,
            'latest_reference' => $latestRefund?->refund_id,
            'shops_impacted' => $this->shopsImpactedCount($filters),
        ];
    }

    /**
     * @return Collection<int, array{status: string, label: string, count: int, percentage: float}>
     */
    public function statusDistribution(RefundFilters $filters): Collection
    {
        $baseQuery = $this->baseQuery($filters);
        $totals = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalCount = $totals->sum();
        $options = Refund::statusOptions();

        return collect($options)->map(function (string $label, string $status) use ($totals, $totalCount) {
            $count = (int) ($totals[$status] ?? 0);
            $percentage = $totalCount > 0
                ? round(($count / $totalCount) * 100, 1)
                : 0.0;

            return [
                'status' => $status,
                'label' => $label,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->values();
    }

    public function shopBreakdown(RefundFilters $filters, int $limit = 5): Collection
    {
        $shopBreakdownQuery = $this->shopBreakdownQuery($filters);

        return DB::query()
            ->fromSub($shopBreakdownQuery, 'refund_shops')
            ->select([
                'shop_id',
                'shop_name',
                DB::raw('COUNT(*) as refund_count'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('shop_id', 'shop_name')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();
    }

    public function recentRefunds(RefundFilters $filters, int $limit = 5): Collection
    {
        return $this->baseQuery($filters)
            ->with([
                'payment.gateway',
                'payment.order.customer',
            ])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function query(RefundFilters $filters): Builder
    {
        return $this->baseQuery($filters);
    }

    protected function baseQuery(RefundFilters $filters): Builder
    {
        $query = $this->refund->newQuery();

        return $this->applyFilters($query, $filters);
    }

    protected function applyFilters(Builder $query, RefundFilters $filters): Builder
    {
        return $query
            ->withStatuses($filters->statuses)
            ->createdBetween($filters->dateFrom, $filters->dateTo)
            ->forShop($filters->shopId)
            ->forGateway($filters->gatewayId)
            ->search($filters->search)
            ->amountBetween($filters->amountMin, $filters->amountMax);
    }

    protected function shopBreakdownQuery(RefundFilters $filters): QueryBuilderContract
    {
        return $this->baseQuery($filters)
            ->select([
                'refunds.id as refund_id',
                'refunds.amount',
                'shops.id as shop_id',
                'shops.name as shop_name',
            ])
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('shops', 'shops.id', '=', 'products.shop_id');
    }

    protected function shopsImpactedCount(RefundFilters $filters): int
    {
        $shopQuery = $this->shopBreakdownQuery($filters);

        return DB::query()
            ->fromSub($shopQuery, 'refund_shops')
            ->distinct('shop_id')
            ->count('shop_id');
    }
}
