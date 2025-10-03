<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RefundSeeder extends Seeder
{
    public function run(): void
    {
        $payment = Payment::where('order_id', 3)
            ->where('status', 'completed')
            ->first()
            ?? Payment::first();

        if (! $payment) {
            return;
        }

        $refunds = [
            [
                'refund_id' => 'RFND-0003-A',
                'amount' => 45.00,
                'status' => 'approved',
                'reason' => 'Returned accessory item',
                'response' => ['message' => 'Refund approved by administrator'],
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'refund_id' => 'RFND-0003-B',
                'amount' => 15.75,
                'status' => 'completed',
                'reason' => 'Shipping delay adjustment',
                'response' => ['message' => 'Refund processed via Stripe'],
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        foreach ($refunds as $data) {
            $refund = Refund::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'refund_id' => $data['refund_id'],
                ],
                [
                    'amount' => $data['amount'],
                    'currency' => $payment->currency,
                    'status' => $data['status'],
                    'reason' => $data['reason'],
                    'response' => $data['response'],
                ]
            );

            if (isset($data['created_at'])) {
                $refund->created_at = $data['created_at'];
                $refund->updated_at = Carbon::now();
                $refund->save();
            }
        }
    }
}
