<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RefreshToken extends Model
{
    private const REFRESH_TOKEN_DAYS = 30;

    protected $table = 'refresh_tokens';
    public $incrementing = true;
    public $timestamps = false;
    protected $primaryKey = 'seq_cd';
    protected $keyType = 'int';

    protected $fillable = [
        'user_cd',
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
        return $this->belongsTo(User::class, 'user_cd', 'user_cd');
    }

    /**
     * リフレッシュトークンの作成とDB保存メソッド
     *
     * @param User $user
     * @param string $deviceName
     * @param Request $request
     * @return array
     */
    public static function issuance(User $user, string $deviceName, ?string $userAgent, ?string $ipAddress): array
    {
        $refreshPlain = Str::random(64);
        $refreshHash = hash('sha256', $refreshPlain);

        $refreshExpiresTime = Carbon::now()->addDays(self::REFRESH_TOKEN_DAYS);

        // リフレッシュトークンをDBに保存
        $result = self::create([
            'user_cd' => $user->getKey(),
            'token_hash' => $refreshHash,
            'device_name' => $deviceName,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'expires_time' => $refreshExpiresTime,
        ]);
        return ['seq_cd' => $result->seq_cd ?? null, 'token' => $refreshPlain, 'expires_time' => $refreshExpiresTime->toIso8601String()];
    }
}
