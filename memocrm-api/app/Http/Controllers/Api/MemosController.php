<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoMemos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class MemosController extends Controller
{
    /**
     * メモ情報の更新
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'co_cd' => ['required', 'integer'],
            'memo_cd' => ['required', 'integer'],
            'memo_title' => ['requiered', 'string', 'max:100'],
            'memo_content' => ['required', 'string', 'max:2000'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {
                $updated = CoMemos::memoUpdate(
                    $data['memo_cd'],
                    $data['co_cd'],
                    $data['memo_title'],
                    $data['memo_content']
                );

                if (!$updated) {
                    throw new \RuntimeException('メモ情報の更新に失敗しました');
                }

                return ['message' => 'メモ情報の更新に成功しました'];
            });

            return response()->json([
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: メモ情報の更新に失敗',
                [
                    'error' => $e->getMessage(),
                    'request' => $data
                ]
            );
            return response()->json([
                'message' => 'メモ情報の更新に失敗しました',
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
        
    }

    /**
     * メモ情報の取得API
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        
    }
}
