<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'author_name',
        'is_internal',
        'note',
    ];

    protected $casts = [
        'is_internal' => 'bool',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
