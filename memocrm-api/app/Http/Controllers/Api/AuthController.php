<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    private const ACCESS_TOKEN_MINUTES = 30;

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
            'email' => ['required', 'email', 'max:200'],
            // パスワードは必須、文字列かチェック
            'password' => ['required', 'string', 'max:20'],
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
            Log::error('ログイン失敗', ['request' => $this->maskSensitive($request->all())]);
            return response()->json([
                'message' => 'メールアドレスかパスワードが違います',
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
                $refresh = RefreshToken::issuance($user, $deviceName, $request->userAgent(), $request->ip());

                return response()->json([
                    'access_token' => $access['token'],
                    'access_token_expires_at' => $access['expires_time'],
                    'refresh_token' => $refresh['token'],
                    'refresh_token_expires_at' => $refresh['expires_time'],
                    'token_type' => 'Bearer',
                ], 200);
            });
        } catch (\Throwable $e) {
            Log::error("予期せぬエラーが起きました", ['error' => $e->getMessage()]);
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
        $valid = Validator::make($request->all(), [
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
                $nowTime = Carbon::now();
                $rt = RefreshToken::where(
                    [
                        ['token_hash', $incomingHash],
                        ['device_name', $deviceName],
                        ['expires_time', '>', $nowTime],
                    ]
                )
                    ->whereNull('revoked_time')
                    ->lockForUpdate()->first();

                if (!$rt) {
                    Log::error('ユーザー情報なし', ['request' => $this->maskSensitive($request->all())]);
                    return response()->json([
                        'message' => '長期間操作がありませんでした。再度ログインしてください。',
                    ], 422);
                }
                $user = $rt->user;
                if (!$user) {
                    Log::error('ユーザー情報なし', ['request' => $this->maskSensitive($request->all())]);
                    return response()->json([
                        'message' => '長期間操作がありませんでした。再度ログインしてください。',
                    ], 422);
                }

                $rt->revoked_time = $nowTime;

                // リフレッシュトークンの作成
                $newRefresh = RefreshToken::issuance($user, $deviceName, $request->userAgent(), $request->ip());

                // 既存のリフレッシュトークンのデータでここに移動したことを既存データに残す
                $rt->replaced_by_seq_cd = $newRefresh['seq_cd'];
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
            Log::error("予期せぬエラーが起きました", ['error' => $e->getMessage()]);
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

        $deviceName = $request->input('device_name', 'mobile');
        // リフレッシュトークンを無効化する
        try {
            RefreshToken::where(
                [
                    ['device_name', $deviceName],
                    ['user_cd', $request->user()->getKey()],
                ]
            )
                ->whereNull('revoked_time')
                ->update(['revoked_time' => now()]);
        } catch (\Throwable $e) {
            Log::error('予期せぬエラーが起きました', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => '一時的なエラーが発生しました。しばらくしてから再度お試しください',
            ], 500);
        }


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
        RefreshToken::where('user_cd', $user->getKey())
            ->where('device_name', $deviceName)
            ->whereNull('revoked_time')
            ->update(['revoked_time' => now()]);
    }


    /**
     * アクセストークンを作成するメソッド
     *
     * @param User $user
     * @param string $deviceName
     * @return array
     */
    private function createAccessToken(User $user, string $deviceName): array
    {
        $accessExpiresTime = Carbon::now()->addMinutes(self::ACCESS_TOKEN_MINUTES);
        $accessToken = $user->createToken($deviceName, ['*'], $accessExpiresTime)->plainTextToken;
        return ['token' => $accessToken, 'expires_time' => $accessExpiresTime->toIso8601String()];
    }

    /**
     * ログ出力用：機密キーをマスクする
     *
     * @param array $data
     * @return array
     */
    private function maskSensitive(array $data): array
    {
        $maskKeys = ['password', 'refresh_token', 'access_token'];
        foreach ($maskKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '****';
            }
        }
        return $data;
    }
}
