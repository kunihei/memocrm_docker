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
            'co_name' => ['required', 'string'],
            'co_address' => ['required', 'string'],
            'tanto_name' => ['required', 'string'],
            'tanto_tel' => ['required', 'string'],
        ]);
        if ($validated->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validated->errors(),
            ], 422);
        }

        $data = $validated->validated();

        try {
            return DB::transaction(function () use ($data, $request) {
                $result = Customers::regist(
                    $request->user()->getKey(),
                    $data['co_name'],
                    $data['co_address'],
                    $data['tanto_name'],
                    $data['tanto_tel'],
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
            Log::error('catch: 顧客情報の登録に失敗', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => '顧客情報の登録に失敗しました。',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
