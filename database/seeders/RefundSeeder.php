<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RefundSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'status' => Refund::STATUS_REQUESTED,
                'reason' => 'Customer reported a damaged item on delivery.',
                'ratio' => 0.35,
                'days_ago' => 2,
            ],
            [
                'status' => Refund::STATUS_APPROVED,
                'reason' => 'Manual approval after investigation.',
                'ratio' => 0.5,
                'days_ago' => 5,
            ],
            [
                'status' => Refund::STATUS_REJECTED,
                'reason' => 'Refund rejected due to missing documentation.',
                'ratio' => 0.25,
                'days_ago' => 7,
            ],
            [
                'status' => Refund::STATUS_REQUESTED,
                'reason' => 'Awaiting gateway confirmation.',
                'ratio' => 0.2,
                'days_ago' => 1,
            ],
            [
                'status' => Refund::STATUS_COMPLETED,
                'reason' => 'Refund completed by payment provider.',
                'ratio' => 0.6,
                'days_ago' => 10,
            ],
            [
                'status' => Refund::STATUS_FAILED,
                'reason' => 'Payment gateway error during refund attempt.',
                'ratio' => 0.15,
                'days_ago' => 3,
            ],
        ];

        $payments = Payment::query()
            ->with([
                'order.details.product.shop',
                'gateway',
            ])
            ->orderBy('id')
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        $showcasePayment = $payments->firstWhere('order_id', 3)
            ?? $payments->first();

        $paymentsPool = $payments->values();

        foreach ($definitions as $index => $definition) {
            $payment = $paymentsPool[$index % $paymentsPool->count()];

            if ($showcasePayment && $payment->is($showcasePayment)) {
                continue;
            }

            $amount = round((float) $payment->amount * ($definition['ratio'] ?? 0.3), 2);
            $amount = $amount > 0
                ? min($amount, (float) $payment->amount)
                : round((float) $payment->amount * 0.3, 2);

            $timestamp = now()->subDays((int) ($definition['days_ago'] ?? 0));

            $refund = Refund::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'status' => $definition['status'],
                ],
                [
                    'amount' => $amount,
                    'currency' => $payment->currency ?? 'USD',
                    'reason' => $definition['reason'],
                    'refund_id' => $definition['refund_id'] ?? (string) Str::uuid(),
                    'response' => ['message' => $definition['reason']],
                ]
            );

            $refund->created_at = $timestamp;
            $refund->updated_at = $timestamp;
            $refund->save();
        }

        $shopGroups = $payments
            ->filter(function ($payment) {
                return $payment->order && $payment->order->details->isNotEmpty();
            })
            ->groupBy(function ($payment) {
                return optional($payment->order->details->first()?->product?->shop)->id;
            })
            ->filter(fn ($group, $shopId) => ! is_null($shopId));

        foreach ($shopGroups as $shopId => $group) {
            $shop = optional($group->first()?->order?->details->first()?->product?->shop);

            if (! $shop) {
                continue;
            }

            $candidatePayment = $group->firstWhere(fn ($payment) => strtolower((string) $payment->status) === 'completed')
                ?? $group->first();

            if (! $candidatePayment) {
                continue;
            }

            if ($showcasePayment && $candidatePayment->is($showcasePayment)) {
                $candidatePayment = $group->first(function ($payment) use ($showcasePayment) {
                    return ! $payment->is($showcasePayment);
                }) ?? $candidatePayment;
            }

            $baseAmount = round((float) $candidatePayment->amount * 0.35, 2);
            $baseAmount = max(1, min($baseAmount, (float) $candidatePayment->amount));

            $shopRefundDefinitions = [
                [
                    'refund_id' => sprintf('SHOP-%04d-A', $shopId),
                    'status' => Refund::STATUS_COMPLETED,
                    'reason' => 'Completed refund processed for ' . $shop->name,
                    'amount' => $baseAmount,
                    'days_ago' => 8,
                ],
                [
                    'refund_id' => sprintf('SHOP-%04d-B', $shopId),
                    'status' => Refund::STATUS_PENDING,
                    'reason' => 'Awaiting approval from ' . $shop->name,
                    'amount' => min(round($baseAmount * 0.6, 2), (float) $candidatePayment->amount),
                    'days_ago' => 2,
                ],
            ];

            foreach ($shopRefundDefinitions as $data) {
                $amount = max(1, min($data['amount'], (float) $candidatePayment->amount));
                $timestamp = now()->subDays((int) ($data['days_ago'] ?? 0));

                $refund = Refund::updateOrCreate(
                    [
                        'payment_id' => $candidatePayment->id,
                        'refund_id' => $data['refund_id'],
                    ],
                    [
                        'amount' => $amount,
                        'currency' => $candidatePayment->currency ?? 'USD',
                        'status' => $data['status'],
                        'reason' => $data['reason'],
                        'response' => ['message' => $data['reason']],
                    ]
                );

                $refund->created_at = $timestamp;
                $refund->updated_at = $timestamp->copy()->addMinutes(15);
                $refund->save();
            }
        }

        $highValuePayment = $paymentsPool->sortByDesc(fn ($payment) => (float) $payment->amount)->first();

        if ($highValuePayment) {
            $highValueDefinitions = [
                [
                    'refund_id' => 'RFND-HIGH-' . $highValuePayment->id,
                    'amount' => min(round((float) $highValuePayment->amount * 0.75, 2), (float) $highValuePayment->amount),
                    'status' => Refund::STATUS_COMPLETED,
                    'reason' => 'High-value refund issued after escalated support case.',
                    'created_at' => now()->subDays(4),
                ],
                [
                    'refund_id' => 'RFND-OPEN-' . $highValuePayment->id,
                    'amount' => min(round((float) $highValuePayment->amount * 0.42, 2), (float) $highValuePayment->amount),
                    'status' => Refund::STATUS_PENDING,
                    'reason' => 'Pending finance approval for bulk product return.',
                    'created_at' => now()->subDays(1),
                ],
            ];

            foreach ($highValueDefinitions as $definition) {
                $refund = Refund::updateOrCreate(
                    [
                        'payment_id' => $highValuePayment->id,
                        'refund_id' => $definition['refund_id'],
                    ],
                    [
                        'amount' => $definition['amount'],
                        'currency' => $highValuePayment->currency ?? 'USD',
                        'status' => $definition['status'],
                        'reason' => $definition['reason'],
                        'response' => ['message' => $definition['reason']],
                    ]
                );

                $createdAt = $definition['created_at'] instanceof Carbon
                    ? $definition['created_at']
                    : Carbon::parse($definition['created_at']);

                $refund->created_at = $createdAt;
                $refund->updated_at = $createdAt->copy()->addMinutes(10);
                $refund->save();
            }
        }

        $nonUsdPayment = $paymentsPool->first(function ($payment) {
            return $payment->currency && strtoupper($payment->currency) !== 'USD';
        });

        if ($nonUsdPayment) {
            $refund = Refund::updateOrCreate(
                [
                    'payment_id' => $nonUsdPayment->id,
                    'refund_id' => 'RFND-INT-' . $nonUsdPayment->id,
                ],
                [
                    'amount' => min(round((float) $nonUsdPayment->amount * 0.3, 2), (float) $nonUsdPayment->amount),
                    'currency' => $nonUsdPayment->currency,
                    'status' => Refund::STATUS_APPROVED,
                    'reason' => 'International refund awaiting local settlement.',
                    'response' => ['message' => 'Exchange rate will be applied upon completion.'],
                ]
            );

            $refund->created_at = now()->subDays(6);
            $refund->updated_at = now()->subDays(6)->addHour();
            $refund->save();
        }

        if (! $showcasePayment) {
            return;
        }

        $showcaseRefunds = [
            [
                'refund_id' => 'RFND-0003-A',
                'amount' => 45.00,
                'status' => Refund::STATUS_APPROVED,
                'reason' => 'Returned accessory item',
                'response' => ['message' => 'Refund approved by administrator'],
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'refund_id' => 'RFND-0003-B',
                'amount' => 15.75,
                'status' => Refund::STATUS_COMPLETED,
                'reason' => 'Shipping delay adjustment',
                'response' => ['message' => 'Refund processed via Stripe'],
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        foreach ($showcaseRefunds as $data) {
            $refund = Refund::updateOrCreate(
                [
                    'payment_id' => $showcasePayment->id,
                    'refund_id' => $data['refund_id'],
                ],
                [
                    'amount' => $data['amount'],
                    'currency' => $showcasePayment->currency ?? 'USD',
                    'status' => $data['status'],
                    'reason' => $data['reason'],
                    'response' => $data['response'],
                ]
            );

            $createdAt = $data['created_at'] instanceof Carbon
                ? $data['created_at']
                : Carbon::parse($data['created_at']);

            $refund->created_at = $createdAt;
            $refund->updated_at = $createdAt->copy()->addMinutes(5);
            $refund->save();
        }
    }
}
