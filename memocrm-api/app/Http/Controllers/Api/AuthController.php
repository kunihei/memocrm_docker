<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    private const ACCESS_TOKEN_MINUTES = 30;
    private const REFRESH_TOKEN_DAYS = 30;

    /**
     * ログインAPI用
     * メールアドレス、パスワード、デバイス名のバリデーションチェックを行い
     * ユーザーテーブルから情報が取得できたらアクセストークンとフレッシュトークンを作成しDBに保存する
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        // リクエスト全体の入力値を指定したルールで検証するバリデータを作成
        $validated = Validator::make($request->all(), [
            // メールアドレスは必須、メール形式チェック
            'email' => ['required', 'email'],
            // パスワードは必須、文字列かチェック
            'password' => ['required', 'string'],
            // デバイス名は任意、文字列かチェック
            'device_name' => ['nullable', 'string'],
        ]);

        // バリデーションチェックに引っかかったかのチェック
        if ($validated->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validated->errors(),
            ], 422);
        }
        // バリデーションを通過した入力値を$dataに格納している
        $data = $validated->validated();

        // ユーザーテーブルからメールアドレスで情報を取得
        $user = User::where('email', $data['email'])->first();

        // ユーザー情報が空でパスワードが一致しない場合
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'error' => 'メールアドレスかパスワードが違います',
            ], 422);
        }

        $deviceName = $data['device_name'] ?? 'mobile';

        // ここで「端末単位でログインし直したら既存を消す」運用も可
        // $this->revokeUserRefreshTokens($user, $deviceName);

        // クロージャ内の複数の DB 操作を一つのトランザクションとして実行
        // クロージャが例外を投げなければコミット、例外が発生すれば自動でロールバックされます。
        // use ($user, $deviceName, $request)はクロージャ内で変数を使用する記述
        try {
            return DB::transaction(function () use ($user, $deviceName, $request) {
                // 1) アクセストークン作成
                $access = $this->createAccessToken($user, $deviceName);

                // 2) リフレッシュトークン作成
                $refresh = $this->createRefreshToken($user, $deviceName, $request);

                return response()->json([
                    'access_token' => $access['token'],
                    'access_token_expires_at' => $access['expires_time'],
                    'refresh_token' => $refresh['token'],
                    'refresh_token_expires_at' => $refresh['expires_time'],
                    'token_type' => 'Bearer',
                ], 200);
            });
        } catch (\Throwable $e) {
            Log::error("運営会社に問い合わせてください", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => '一時的なエラーが発生しました。しばらくしてから再度お試しください',
            ], 500);
        }
    }

    /**
     * リフレッシュトークンの更新
     *
     * @param Request $request
     * @return void
     */
    public function refresh(Request $request)
    {
        $valid = validator($request->all(), [
            'refresh_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();

        $incomingHash = hash('sha256', $data['refresh_token']);
        $deviceName = $data['device_name'] ?? 'mobile';

        try {
            return DB::transaction(function () use ($incomingHash, $deviceName, $request) {
                $rt = RefreshToken::where('token_hash', $incomingHash)->lockForUpdate()->first();

                if (!$rt || !$rt->isActive()) {
                    return response()->json([
                        'message' => '無効なリフレッシュトークンです',
                    ], 422);
                }
                $user = $rt->user()->first();
                if (!$user) {
                    return response()->json([
                        'message' => '無効なリフレッシュトークンです',
                    ], 422);
                }

                $rt->revoked_time = Carbon::now();

                // リフレッシュトークンの作成
                $newRefresh = $this->createRefreshToken($user, $deviceName, $request);

                // 既存のリフレッシュトークンのデータでここに移動したことを既存データに残す
                $rt->replaced_by_token_id = $newRefresh['seq_cd'];
                // モデルが既存レコードなら UPDATE、未挿入なら INSERT を実行します（主キーの有無で判定）
                $rt->save();

                // アクセストークン作成
                $access = $this->createAccessToken($user, $deviceName);

                return response()->json([
                    'access_token' => $access['token'],
                    'access_token_expires_at' => $access['expires_time'],
                    'refresh_token' => $newRefresh['token'],
                    'refresh_token_expires_at' => $newRefresh['expires_time'],
                    'token_type' => 'Bearer',
                ], 200);
            });
        } catch (\Throwable $e) {
            Log::error("運営会社に問い合わせてください", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => '一時的なエラーが発生しました。しばらくしてから再度お試しください',
            ], 500);
        }
    }

    /**
     * ログアウトAPI
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request)
    {
        // 既存のアクセストークンを削除
        $request->user()?->currentAccessToken()?->delete();

        // リフレッシュトークンを無効化する
        RefreshToken::where('device_name', $request->device_name)->whereNull('revoked_time')->update(['revoked_time' => now()]);

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * ログイン情報を取得するだけのAPI
     *
     * @param Request $request
     * @return void
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * 端末単位でリフレッシュトークンを削除したい用
     *
     * @param User $user
     * @param string $deviceName
     * @return void
     */
    private function revokeUserRefreshTokens(User $user, string $deviceName): void
    {
        RefreshToken::where('user_id', $user->getKey())
            ->where('device_name', $deviceName)
            ->whereNull('revoked_time')
            ->update(['revoked_time' => now()]);
    }


    /**
     * アクセストークンを作成するメソッド
     *
     * @param User $user
     * @param string $deviceName
     * @return string
     */
    private function createAccessToken(User $user, string $deviceName): array
    {
        $accessExpiresTime = Carbon::now()->addMinutes(self::ACCESS_TOKEN_MINUTES);
        $accessToken = $user->createToken($deviceName, ['*'], $accessExpiresTime)->plainTextToken;
        return ['token' => $accessToken, 'expires_time' => $accessExpiresTime->toIso8601String()];
    }

    private function createRefreshToken(User $user, string $deviceName, Request $request): array
    {
        $refreshPlain = Str::random(64);
        $refreshHash = hash('sha256', $refreshPlain);

        $refreshExpiresTime = Carbon::now()->addDays(self::REFRESH_TOKEN_DAYS);
        // lockForUpdateは排他ロックをかける
        $last = RefreshToken::orderByDesc('seq_cd')->lockForUpdate()->first();
        $seqCd = ($last->seq_cd ?? 0) + 1;

        // リフレッシュトークンをDBに保存ï
        RefreshToken::create([
            'seq_cd' => $seqCd,
            'user_id' => $user->getKey(),
            'token_hash' => $refreshHash,
            'device_name' => $deviceName,
            'user_agent' => substr((string)$request->userAgent(), 0, 2000),
            'ip_address' => $request->ip(),
            'expires_time' => $refreshExpiresTime,
        ]);
        return ['seq_cd' => $seqCd, 'token' => $refreshPlain, 'expires_time' => $refreshExpiresTime->toIso8601String()];
    }
}
