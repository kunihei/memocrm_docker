<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoMemos;
use App\Models\Customers;
use App\Models\MemoTags;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Data\CustomerData;

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
        $validator = Validator::make($request->all(), [
            'co_name' => ['required', 'string', 'max:100'],
            'co_address' => ['required', 'string', 'max:200'],
            'tanto_name' => ['required', 'string', 'max:100'],
            'tanto_tel' => ['required', 'string', 'max:15', 'regex:/^[0-9-+()]*$/'],
            'memo_title' => ['nullable', 'string', 'max:100'],
            'memo_content' => ['nullable', 'string', 'max:2000'],
            'tag_cd' => ['nullable', 'array'],
            'tag_cd.*' => ['integer', 'distinct'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $userCd = $request->user()->getKey();
        $customerData = CustomerData::fromArray($data);
        $memoTitle = trim($data['memo_title'] ?? '');
        $memoContent = trim($data['memo_content'] ?? '');
        $hasMemo = $memoTitle !== '' || $memoContent !== '';
        $tagCds = $data['tag_cd'] ?? [];

        if (!empty($tagCds) && !$hasMemo) {
            return response()->json([
                'message_list' => ['タグを登録する場合はメモを入力してください'],
            ], 422);
        }

        if (!empty($tagCds)) {
            $tagCount = Tags::countActiveByUserAndTagCds($userCd, $tagCds);

            if ($tagCount !== count($tagCds)) {
                return response()->json([
                    'message_list' => ['不正なタグが含まれています'],
                ], 422);
            }
        }

        try {
            $result = DB::transaction(function () use ($userCd, $customerData, $hasMemo, $memoTitle, $memoContent, $tagCds) {
                $customer = Customers::coRegist(
                    $userCd,
                    $customerData
                );

                if ($hasMemo) {
                    $coMemo = CoMemos::memoRegist(
                        $userCd,
                        $customer->co_cd,
                        $memoTitle,
                        $memoContent
                    );

                    // 追加に失敗した場合は例外を投げる
                    if (!$coMemo) {
                        throw new \RuntimeException('メモの登録に失敗しました');
                    }

                    foreach ($tagCds as $tagCd) {
                        MemoTags::regist(
                            $customer->co_cd,
                            $coMemo->memo_cd,
                            $tagCd
                        );
                    }
                }

                return ['message_list' => ['顧客情報の登録に成功しました'],];
            });

            return response()->json([
                'message_list' => $result['message_list'],
            ], 201);
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の登録に失敗',
                [
                    'error' => $e->getMessage(),
                    'request' => $data
                ]
            );
            return response()->json([
                'message_list' => ['顧客情報の登録に失敗しました'],
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
        $validator = Validator::make($request->all(), [
            'co_cd' => ['required', 'integer'],
            'co_name' => ['required', 'string', 'max:100'],
            'co_address' => ['required', 'string', 'max:200'],
            'tanto_name' => ['required', 'string', 'max:100'],
            'tanto_tel' => ['required', 'string', 'max:15', 'regex:/^[0-9-+()]*$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $userCd = $request->user()->getKey();
        $customerData = CustomerData::fromArray($data);
        $coCd = $data['co_cd'];

        try {
            $result = DB::transaction(function () use ($userCd, $coCd, $customerData) {
                $updated = Customers::coUpdate(
                    $userCd,
                    $coCd,
                    $customerData,
                );

                if (!$updated) {
                    return null;
                }

                return ['message_list' => ['顧客情報の更新に成功しました']];
            });

            if ($result === null) {
                return response()->json([
                    'message_list' => ['対象の顧客が存在しません'],
                ], 404);
            }

            return response()->json([
                'message_list' => $result['message_list'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の更新に失敗',
                [
                    'error' => $e->getMessage(),
                    'request' => $data
                ]
            );
            return response()->json([
                'message_list' => ['顧客情報の更新に失敗しました'],
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
        $validator = Validator::make($request->all(), [
            'co_cd' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message_list' => ['不正なアクセス'],
            ], 422);
        }
        $data = $validator->validated();
        $userCd = $request->user()->getKey();

        try {
            $result = DB::transaction(function () use ($userCd, $data) {
                $isDeleted = Customers::coDelete($userCd, $data['co_cd']);
                if (!$isDeleted) {
                    return null;
                }
                return ['message_list' => ['顧客情報の削除に成功しました']];
            });

            if ($result === null) {
                return response()->json([
                    'message_list' => ['対象の顧客が存在しません'],
                ], 404);
            }

            return response()->json([
                'message_list' => $result['message_list'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報の削除に失敗',
                ['error' => $e->getMessage(), 'request' => $data]
            );
            return response()->json([
                'message_list' => ['顧客情報の削除に失敗しました'],
            ], 500);
        }
    }

    /**
     * 顧客情報の取得API
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $userCd = $request->user()->getKey();

        try {
            $customers = Customers::getList($userCd);

            return response()->json([
                'message_list' => $customers->isEmpty() ? ['顧客情報はありません'] : ['正常終了'],
                'data' => $customers->toArray(),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: 顧客情報取得に失敗',
                ['error' => $e->getMessage(), 'user_cd' => $userCd]
            );
            return response()->json(
                [
                    'message_list' => ['顧客情報取得に失敗しました'],
                ],
                500
            );
        }
    }
}
