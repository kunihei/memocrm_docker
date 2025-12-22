<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * 顧客情報登録
     *
     * @param Request $request
     * @return void
     */
    public function regist(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'co_name' => ['required', 'string', 'max:100'],
            'co_address' => ['required', 'string', 'max:200'],
            'tanto_name' => ['required', 'string', 'max:100'],
            'tanto_tel' => ['required', 'string', 'max:15'],
        ]);
        if ($validated->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validated->errors(),
            ], 422);
        }

        $data = $validated->validated();
        $userCd = $request->user()->getKey();

        try {
            return DB::transaction(function () use ($userCd, $data) {
                $result = Customers::coRegist(
                    $userCd,
                    $data['co_name'],
                    $data['co_address'],
                    $data['tanto_name'],
                    $data['tanto_tel']
                );

                // 追加に失敗した場合は例外を投げる
                if (!$result) {
                    throw new \Exception('顧客情報の登録に失敗しました。');
                }

                return response()->json([
                    'message' => '顧客情報の登録に成功しました。',
                    'customer' => $result,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の登録に失敗',
                [
                    'error' => $e->getMessage(),
                    'request' => $data
                ]
            );
            return response()->json([
                'message' => '顧客情報の登録に失敗しました。',
            ], 500);
        }
    }

    /**
     * 顧客情報の更新
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'co_cd' => ['required', 'integer'],
            'co_name' => ['required', 'string', 'max:100'],
            'co_address' => ['required', 'string', 'max:200'],
            'tanto_name' => ['required', 'string', 'max:100'],
            'tanto_tel' => ['required', 'string', 'max:15']
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();
        $userCd = $request->user()->getKey();

        try {
            return DB::transaction(function () use ($userCd, $data) {
                $result = Customers::coUpdate(
                    $userCd,
                    $data['co_cd'],
                    $data['co_name'],
                    $data['co_address'],
                    $data['tanto_name'],
                    $data['tanto_tel']
                );
                if (!$result) {
                    return response()->json([
                        'message' => '顧客情報がありません',
                    ], 404);
                }
                return response()->json([
                    'message' => '顧客情報の更新に成功しました',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の更新に失敗',
                [
                    'error' => $e->getMessage(),
                    'request' => $data
                ]
            );
            return response()->json([
                'message' => '顧客情報の更新に失敗しました。',
            ], 500);
        }
    }

    /**
     * 顧客情報の削除
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        $valid = validator::make($request->all(), [
            'co_cd' => ['required', 'integer'],
        ]);
        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $valid->errors(),
            ], 422);
        }
        $data = $valid->validated();
        $userCd = $request->user()->getKey();

        try {
            return DB::transaction(function () use ($userCd, $data) {
                $customer = Customers::coDeleete($userCd, $data['co_cd']);
                if (!$customer) {
                    return response()->json([
                        'message' => '顧客情報がありません',
                    ], 404);
                }
                return response()->json([
                    'message' => '顧客情報の削除に成功しました',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の削除に失敗',
                ['error' => $e->getMessage(), 'request' => $data]
            );
            return response()->json([
                'message' => '顧客情報の削除に失敗しました。',
            ], 500);
        }
    }
}
