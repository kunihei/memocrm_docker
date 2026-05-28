<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CoMemos extends Model
{
    protected $table = 'co_memos';
    public $timestamps =  false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'co_cd',
        'memo_cd',
        'title',
        'content',
        'del_flg',
        'update_time',
    ];

    protected $casts = [
        'create_time' => 'datetime:Y-m-d H:i:s',
        'update_time' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 顧客に紐づくメモを登録
     *
     * @param integer $userCd
     * @param integer $coCd
     * @param string $title
     * @param string $content
     * @return CoMemos
     */
    public static function memoRegist(int $userCd, int $coCd, string $title, string $content): CoMemos
    {

        if (!self::customerExists($userCd, $coCd)) {
            throw new \RuntimeException('顧客が存在しません。');
        }
        $nextMemoCd = (int) self::where('co_cd', $coCd)->lockForUpdate()->max('memo_cd') + 1;

        $coMemo = self::create([
            'co_cd' => $coCd,
            'memo_cd' => $nextMemoCd,
            'title' => $title,
            'content' => $content,
        ]);

        return $coMemo;
    }

    /**
     * 顧客に紐づくメモの更新
     *
     * @param integer $userCd
     * @param integer $memoCd
     * @param integer $coCd
     * @param string $title
     * @param string $content
     * @return boolean
     */
    public static function memoUpdate(int $userCd, int $memoCd, int $coCd, string $title, string $content): bool
    {

        if (!self::customerExists($userCd, $coCd)) {
            throw new \RuntimeException('顧客が存在しません。');
        }

        $coMemo = self::where(
            [
                ['memo_cd', $memoCd],
                ['co_cd', $coCd],
                ['del_flg', false],
            ]
        )->lockForUpdate()->first();

        if (!$coMemo) {
            return false;
        }

        $updated = self::where([
            ['co_cd', $coCd],
            ['memo_cd', $memoCd],
            ['del_flg', false],
        ])->update([
            'title' => $title,
            'content' => $content,
            'update_time' => now(),
        ]);

        if ($updated === 0) {
            return false;
        }

        return true;
    }

    /**
     * メモの削除
     *
     * @param integer $userCd
     * @param integer $coCd
     * @param integer $memoCd
     * @return boolean
     */
    public static function memoDelete(int $userCd, int $coCd, int $memoCd): bool
    {

        if (!self::customerExists($userCd, $coCd)) {
            throw new \RuntimeException('顧客が存在しません。');
        }

        $coMemo = CoMemos::where(
            [
                ['co_cd', $coCd],
                ['memo_cd', $memoCd],
                ['del_flg', false],
            ]
        )->lockForUpdate()->first();

        if (!$coMemo) {
            return false;
        }

        $updated = self::where([
            ['co_cd', $coCd],
            ['memo_cd', $memoCd],
            ['del_flg', false],
        ])->update([
            'del_flg' => true,
            'update_time' => now(),
        ]);

        if ($updated === 0) {
            return false;
        }

        return true;
    }

    /**
     * メモの一覧取得
     *
     * @param integer $userCd
     * @param integer $coCd
     * @return Collection
     */
    public static function memoList(int $userCd, int $coCd): Collection
    {
        $memos = self::select(
            [
                'co_memos.co_cd',
                'co_memos.memo_cd',
                'co_memos.title',
                'co_memos.content',
                'co_memos.create_time',
                'co_memos.update_time'
            ]
        )->join('customers', 'customers.co_cd', '=', 'co_memos.co_cd')
            ->where('customers.user_cd', $userCd)
            ->where('customers.del_flg', false)
            ->where('co_memos.co_cd', $coCd)
            ->where('co_memos.del_flg', false)
            ->orderBy('co_memos.memo_cd', 'desc')->get();

        return $memos;
    }

    private static function customerExists(int $userCd, int $coCd): bool
    {
        return DB::table('customers')
            ->where('user_cd', $userCd)
            ->where('co_cd', $coCd)
            ->where('del_flg', false)
            ->lockForUpdate()
            ->exists();
    }
}
