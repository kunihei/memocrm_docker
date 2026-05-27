<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoMemos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use RuntimeException;

class MemosController extends Controller
{

    public function regist(Request $request) {
        $valid = Validator::make($request->all(),[
            'co_cd' => ['required', 'integer'],
            'memo_title' => ['required', 'string', 'max:100'],
            'memo_content' => ['required', 'string', 'max:2000'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {
                $coMemo = CoMemos::memoRegist((int)$data['co_cd'], $data['memo_title'], $data['memo_content']);
                if (!$coMemo) {
                    throw new RuntimeException('メモの登録に失敗しました。');
                }
                return [
                    'message_list' => ['メモの登録に成功しました。'],
                    'data' => $coMemo
                ];
            });
            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error(
                'catch: メモの登録に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]
            );
            return response()->json([
                'message_list' => ['メモの登録に失敗しました。']
            ], 500);
        }
    }

    /**
     * メモ情報の更新
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $valid = Validator::make($request->all(),[
            'memo_cd' => ['required', 'integer'],
            'co_cd' => ['required', 'integer'],
            'memo_title' => ['required', 'string', 'max:100'],
            'memo_content' => ['required', 'string', 'max:2000'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'], 
                'errors' => $valid->errors()
                ], 422);
        }
        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {

                $coMemo = CoMemos::memoUpdate((int)$data['memo_cd'], (int)$data['co_cd'], $data['memo_title'], $data['memo_content']);
                if (!$coMemo) {
                    throw new RuntimeException('メモ情報の更新に失敗しました。');
                }
                return ['message_list' => ['メモ情報の更新に成功しました。']];
            });

            return response()->json($result);
        } catch (\Throwable $e) {

            Log::error(
                'catch: メモ情報の更新に失敗しました',
                [
                    'error' => $e->getMessage(),
                    'data' => [
                        'memo_cd' => $data['memo_cd'] ?? NULL,
                        'co_cd' => $data['co_cd'] ?? NULL,
                    ],
                ]);
            return response()->json([
                'message_list' => ['メモ情報の更新に失敗しました。']
                ], 500);
        }

    }

    /**
     * メモ情報の削除
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        $valid = Validator::make($request->all(),[
            'co_cd' => ['required', 'integer'],
            'memo_cd' => ['required', 'integer'],
        ]);
        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['不正なアクセス'],
            ], 422);
        }
        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {
                $coMemo = CoMemos::memoDelete((int)$data['co_cd'], (int)$data['memo_cd']);
                if (!$coMemo) {
                    return [
                        'status' => 'error',
                        'message_list' => ['メモの削除に失敗しました。']
                        ];
                }
                return [
                    'status' => 'success',
                    'message_list' => ['メモの削除に成功しました。']
                    ];
            });

            if ($result['status'] === 'error') {
                return response()->json([
                    'message_list' => $result['message_list'],
                ], 422);
            }
            return response()->json([
                'message_list' => $result['message_list'],
            ]);
        } catch (\Throwable $e) {
            Log::error('catch: メモの削除に失敗しました', [
                'error' => $e->getMessage(),
                'data' => [
                    'memo_cd' => $data['memo_cd'] ?? NULL,
                    'co_cd' => $data['co_cd'] ?? NULL,
                ],
            ]);
            return response()->json([
                'message_list' => ['メモの削除に失敗しました。']
                ], 500);
        }
    }

    /**
     * メモ一覧取得API
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $valid = Validator::make(['co_cd' => $request->route('co_cd')], [
            'co_cd' => ['required', 'integer'],
        ]);
        
        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['不正なアクセス'],
            ], 422);
        }
        $data = $valid->validated();
        try {
            $memos = CoMemos::memoList((int)$data['co_cd']);
            return response()->json([
                'message_list' => ['メモ一覧の取得に成功しました。'],
                'data' => $memos,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: メモ一覧の取得に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => ['co_cd' => $data['co_cd'] ?? null]
                ],
            );

            return response()->json([
                'message_list' => ['メモ一覧の取得に失敗しました。']
            ], 500);
        }
    }
}
