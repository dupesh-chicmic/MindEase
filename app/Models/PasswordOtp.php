<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'is_verified',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }
}
