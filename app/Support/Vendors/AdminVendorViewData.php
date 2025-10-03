<?php

namespace App\Support\Vendors;

use App\Models\Vendor;

class AdminVendorViewData
{
    public static function forIndex(): array
    {
        return [
            'stats' => self::stats(),
            'statusOptions' => self::statusOptions(),
            'filters' => self::defaultFilters(),
        ];
    }

    public static function forCreate(): array
    {
        return [
            'statusOptions' => self::statusOptions(),
            'defaultStatus' => 'active',
        ];
    }

    public static function statusOptions(): array
    {
        return collect(Vendor::STATUSES)
            ->mapWithKeys(fn (string $status) => [
                $status => __('cms.vendors.status_' . $status),
            ])
            ->all();
    }

    public static function stats(): array
    {
        $statusCounts = Vendor::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $breakdown = collect(Vendor::STATUSES)
            ->mapWithKeys(fn (string $status) => [
                $status => (int) ($statusCounts[$status] ?? 0),
            ])
            ->all();

        $total = (int) array_sum($breakdown);

        $percentages = collect($breakdown)
            ->map(fn (int $count) => $total > 0 ? (int) round(($count / $total) * 100) : 0)
            ->all();

        return [
            'total' => $total,
            'breakdown' => $breakdown,
            'percentages' => $percentages,
        ];
    }

    public static function defaultFilters(): array
    {
        return [
            'status' => '',
            'search' => '',
        ];
    }
}
