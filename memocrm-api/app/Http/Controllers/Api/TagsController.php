<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function regist(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'tag_name' => ['required', 'string', 'max:100'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();
        $userCd = $request->user()->getKey();

        try {
            $result = DB::transaction(function () use ($data, $userCd) {
                $tag = Tags::tagRegist($userCd, $data['tag_name']);
                return [
                    'message_list' => ['タグの登録に成功しました'],
                    'tag' => $tag,
                ];
            });
            return response()->json([
                'message_list' => $result['message_list'],
                'data' => $result['tag'],
            ], 201);
        } catch (\Throwable $e) {
            Log::error(
                'catch: タグの登録に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]
            );
            return response()->json([
                'message_list' => ['タグの登録に失敗しました。']
            ], 500);
        }
    }

    public function update(Request $request) {
        $valid = Validator::make($request->all(), [
            'tag_cd' => ['required', 'integer'],
            'tag_name' => ['required', 'string', 'max:100'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();
        $userCd = $request->user()->getKey();

        try {
            $result = DB::transaction(function () use ($data, $userCd) {
                $updatedFlg = Tags::tagUpdate($userCd, $data['tag_cd'], $data['tag_name']);
                return $updatedFlg;
            });

            if (!$result) {
                return response()->json([
                    'message_list' => ['対象のタグが存在しません'],
                ], 404);
            }
            return response()->json([
                'message_list' => ['タグの更新に成功しました'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: タグの更新に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]
            );
            return response()->json([
                'message_list' => ['タグの更新に失敗しました']
            ], 500);
        }
    }

    public function delete(Request $request) {
        $valid = Validator::make($request->all(), [
            'tag_cd' => ['required', 'integer'],
        ]);

        if ($valid->fails()) {
            return response()->json([
                'message_list' => ['バリデーションエラー'],
                'errors' => $valid->errors(),
            ], 422);
        }

        $data = $valid->validated();
        $userCd = $request->user()->getKey();

        try {
            $result = DB::transaction(function () use ($data, $userCd) {
                $updatedFlg = Tags::tagDelete($userCd, $data['tag_cd']);
                return $updatedFlg;
            });

            if (!$result) {
                return response()->json([
                    'message_list' => ['対象のタグが存在しません'],
                ], 404);
            }
            return response()->json([
                'message_list' => ['タグの削除に成功しました'],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: タグの削除に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]
            );
            return response()->json([
                'message_list' => ['タグの削除に失敗しました']
            ], 500);
        }
    }

    public function list(Request $request) {
        $userCd = $request->user()->getKey();
        try {
            $tags = Tags::tagList($userCd);
            return response()->json([
                'message_list' => ['タグ一覧の取得に成功しました。'],
                'data' => $tags,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'catch: タグ一覧の取得に失敗しました。',
                [
                    'error' => $e->getMessage(),
                    'user_cd' => $userCd,
                ]
            );
            return response()->json([
                'message_list' => ['タグ一覧の取得に失敗しました。'],
            ], 500);
        }
    }
}
