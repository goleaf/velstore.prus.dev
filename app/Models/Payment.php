<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

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
}
