<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayConfig extends Model
{
    use HasFactory;

    protected $fillable = ['gateway_id', 'key_name', 'key_value', 'is_encrypted', 'environment'];

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function getKeyValueAttribute($value)
    {
        return $value; // no decryption, just return whatever is stored
    }

    public function setKeyValueAttribute($value)
    {
        $this->attributes['key_value'] = $this->is_encrypted && $value
            ? Crypt::encryptString($value)
            : $value;
    }
}
