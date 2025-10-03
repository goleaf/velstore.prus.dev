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
                'status' => Refund::STATUS_PENDING,
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

        $payments = Payment::query()->take(count($definitions))->get();

        if ($payments->count() < count($definitions)) {
            $additional = Payment::factory()
                ->count(count($definitions) - $payments->count())
                ->create();

            $payments = $payments->concat($additional);
        }

        foreach ($definitions as $index => $definition) {
            $payment = $payments[$index] ?? $payments->first();

            if (! $payment) {
                break;
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

        $showcasePayment = Payment::where('order_id', 3)
            ->where('status', 'completed')
            ->first()
            ?? Payment::first();

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
