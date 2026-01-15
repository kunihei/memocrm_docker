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
            'title' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー', 
                'errors' => $valid->errors()
                ], 422);
        }
        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {
                $coMemo = CoMemos::memoUpdate((int)$data['memo_cd'], (int)$data['co_cd'], $data['title'], $data['content']);

                if (!$coMemo) {
                    throw new RuntimeException('メモ情報の更新に失敗しました。');
                }

                return ['message' => 'メモ情報の更新に成功しました。'];
            });

            return response()->json([
                'message' => $result['message'],
            ]);

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
                'message' => 'メモ情報の更新に失敗しました'
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
     * メモ一覧取得API
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        
    }
}
