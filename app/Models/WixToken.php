<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WixToken extends Model
{
    protected $table = 'wix_tokens';

    protected $fillable = [
        'instance',
        'app',
        'access_token',
        'acc_expires_at',
        'refresh_token',
        'ref_expires_at',
        'info',
    ];

    protected $casts = [
        'acc_expires_at' => 'datetime',
        'ref_expires_at' => 'datetime',
        'info' => 'array',
    ];
}
