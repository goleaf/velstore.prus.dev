<?php

namespace App\Support\Refunds;

use App\Models\Refund;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RefundFilters
{
    /**
     * @param array<int, string> $statuses
     */
    public function __construct(
        public array $statuses = [],
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $shopId = null,
        public ?string $gatewayId = null,
        public ?string $search = null,
        public ?float $amountMin = null,
        public ?float $amountMax = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $statuses = Arr::wrap($request->input('status', []));
        $statuses = array_values(array_filter($statuses, function ($status) {
            if (! is_string($status)) {
                return false;
            }

            $status = strtolower($status);

            return in_array($status, Refund::STATUSES, true);
        }));

        $dateFrom = self::normalizeDate($request->input('date_from'));
        $dateTo = self::normalizeDate($request->input('date_to'));

        $shopId = self::nullableString($request->input('shop_id'));
        $gatewayId = self::nullableString($request->input('gateway_id'));
        $search = self::nullableString($request->input('search_term'));

        $amountMin = self::nullableFloat($request->input('amount_min'));
        $amountMax = self::nullableFloat($request->input('amount_max'));

        if ($amountMin !== null && $amountMax !== null && $amountMin > $amountMax) {
            [$amountMin, $amountMax] = [$amountMax, $amountMin];
        }

        return new self(
            statuses: $statuses,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            shopId: $shopId,
            gatewayId: $gatewayId,
            search: $search,
            amountMin: $amountMin,
            amountMax: $amountMax,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->statuses,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'shop_id' => $this->shopId,
            'gateway_id' => $this->gatewayId,
            'search' => $this->search,
            'amount_min' => $this->amountMin,
            'amount_max' => $this->amountMax,
        ];
    }

    public function hasActiveFilters(): bool
    {
        return ! empty($this->statuses)
            || $this->dateFrom !== null
            || $this->dateTo !== null
            || $this->shopId !== null
            || $this->gatewayId !== null
            || $this->search !== null
            || $this->amountMin !== null
            || $this->amountMax !== null;
    }

    private static function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
