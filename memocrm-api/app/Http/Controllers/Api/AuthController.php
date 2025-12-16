<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    private const ACCESS_TOKEN_MINUTES = 30;
    private const REFRESH_TOKEN_DAYS = 30;
    public function login(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validated->errors(),
            ]);
        }
        $data = $validated->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['メールアドレスかパスワードが違います'],
            // ]);
            return response()->json([
                'error' => 'メールアドレスかパスワードが違います',
            ]);
        }

        $tokenName = $data['device_name'] ?? 'mobile';

        // $token = $user->createToken($tokenName)->plainTextToken;

        // ここで「端末単位でログインし直したら既存を消す」運用も可
        // $this->revokeUserRefreshTokens($user, $tokenName);
        return DB::transaction(function () use ($user, $tokenName, $request) {
            // 1) アクセストークン作成
            $accessExpiresAt = Carbon::now()->addMinutes(self::ACCESS_TOKEN_MINUTES);
            $accessToken = $user->createToken($tokenName, ['*'], $accessExpiresAt)->plainTextToken;

            // 2) リフレッシュトークン作成
            $refreshPlain = Str::random(64);
            $refreshHash = hash('sha256', $refreshPlain);

            $refreshExpiresAt = Carbon::now()->addDays(self::REFRESH_TOKEN_DAYS);
            $refreshToken = RefreshToken::create([
                'user_id' => $user->getKey(),
                'token_hash' => $refreshHash,
                'device_name' => $tokenName,
                'user_agent' => substr((string)$request->userAgent(), 0, 2000),
                'ip_address' => $request->ip(),
                'expires_time' => $refreshExpiresAt,
            ]);
            return response()->json([
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessExpiresAt->toIso8601String(),
                'refresh_token' => $refreshPlain,
                'refresh_token_expires_at' => $refreshExpiresAt->toIso8601String(),
                'token_type' => 'Bearer',
            ], 200);
        });
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json([
            'ok' => true,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    private function revokeUserRefreshTokens(User $user, string $deviceName): void {
        RefreshToken::where('user_id', $user->getKey())
        ->where('device_name', $deviceName)
        ->whereNull('revoked_time')
        ->update(['revoked_time' => now()]);
    }
}
