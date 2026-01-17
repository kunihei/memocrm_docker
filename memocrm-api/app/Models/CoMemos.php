<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CoMemos extends Model
{
    protected $table = 'co_memos';
    public $timestamps =  false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'co_cd',
        'title',
        'content',
        'del_flg',
        'update_time',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * 顧客に紐づくメモを登録
     *
     * @param integer $coCd
     * @param string $title
     * @param string $content
     * @return CoMemos
     */
    public static function memoRegist(int $coCd, string $title, string $content): CoMemos
    {
        $nextMemoCd = (int) self::where('co_cd', $coCd)->max('memo_cd') + 1;
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
     * @param integer $memoCd
     * * @param integer $coCd
     * @param string $title
     * @param string $content
     * @return boolean
     */
    public static function memoUpdate(int $memoCd, int $coCd, string $title, string $content): bool
    {

        $coMemo = self::where(['memo_cd' => $memoCd, 'co_cd' => $coCd])->lockForUpdate()->first();

        if (!$coMemo) {
            return false;
        }

        // $coMemo->title = $title;
        // $coMemo->content = $content;
        // $coMemo->update_time = Carbon::now();
        // $coMemo->saveOrFail();
        $updated = self::where([
            'memo_cd' => $memoCd,
            'co_cd' => $coCd
        ])
            ->update([
                'title' => $title,
                'content' => $content,
                'update_time' => Carbon::now()
            ]);
        if ($updated === 0) {
            return false;
        }

        return true;
    }

    public static function memoDelete(int $coCd, int $memoCd): bool
    {
        $memo = CoMemos::where(
            [
                ['co_cd', $coCd],
                ['memo_cd', $memoCd],
            ]
        )->lockForUpdate()->first();

        if (!$memo) {
            return false;
        }

        // $memo->del_flg = true;
        // $memo->update_time = Carbon::now();
        // $memo->saveOrFail();
        $updated = self::where([
            'co_cd' => $coCd,
            'memo_cd' => $memoCd,
        ])->update([
            'del_flg' => true,
            'update_time' => Carbon::now(),
        ]);

        if ($updated === 0) {
            return false;
        }

        return true;
    }

    public static function memoList(int $coCd): Collection
    {
        $memos = self::select(
            [
                'co_cd',
                'memo_cd',
                'title',
                'content',
                'create_time',
                'update_time'
            ]
        )->where(
            [
                ['co_cd', $coCd],
                ['del_flg', false]
            ]
        )->orderBy('memo_cd', 'desc')->get();

        return $memos;
    }
}
