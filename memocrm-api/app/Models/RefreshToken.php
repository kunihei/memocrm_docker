<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'device_name',
        'user_agent',
        'ip_address',
        'expires_time',
        'revoked_time',
        'replaced_by_token_id',
    ];

    protected $casts = [
        'expires_time' => 'datetime',
        'revoked_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_time === null && $this->expires_time->isFuture();
    }
}
