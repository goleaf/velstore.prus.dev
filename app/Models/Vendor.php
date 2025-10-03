<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Vendor extends Authenticatable
{
    use HasFactory, Notifiable;

    public const STATUSES = ['active', 'inactive', 'banned'];

    protected $guard = 'vendor';

    protected $fillable = ['name', 'email', 'password', 'phone', 'status', 'avatar'];

    protected $hidden = ['password'];

    protected $casts = ['password' => 'hashed'];
}
