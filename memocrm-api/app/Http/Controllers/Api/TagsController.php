<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use RuntimeException;

class TagsController extends Controller
{
    public function regist(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'tag_name' => ['required', 'string', 'max:100'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'erroes' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();

        try {
            $result = DB::transaction(function () use ($data) {
                $tag = Tags::tagRegist($data['tag_name']);
                if (!$tag) {
                    throw new RuntimeException('タグの登録に失敗しました。');
                }
                return [
                    'message' => 'タグの登録に成功しました。',
                    'tag' => $tag,
                ];
            });
            return response()->json([
                'message' => $result['message'],
                'tag' => $result['tag'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: タグの登録に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]
            );
            return response()->json([
                'message' => 'タグの登録に失敗しました。'
            ], 500);
        }
    }
}
