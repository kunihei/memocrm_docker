<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MemoTags extends Model
{
    protected $table = 'memo_tags';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'co_cd',
        'memo_cd',
        'tag_cd',
    ];


    /**
     * タグの登録
     *
     * @param integer $coCd
     * @param integer $memoCd
     * @param integer $tagCd
     * @return MemoTags
     */
    public static function regist(int $coCd, int $memoCd, int $tagCd): MemoTags
    {
        return self::firstOrCreate([
            'co_cd' => $coCd,
            'memo_cd' => $memoCd,
            'tag_cd' => $tagCd,
        ]);
    }

    /**
     * メモに紐づくタグの一覧を取得
     *
     * @param integer $userCd
     * @param integer $coCd
     * @param Collection $memoCds
     * @return Collection
     */
    public static function listByMemoCds(int $userCd, int $coCd, Collection $memoCds): Collection
    {
        if ($memoCds->isEmpty()) {
            // メモコードが空なら空のコレクションを返す
            return collect();
        }

        return DB::table('memo_tags')
            ->join('tags', 'tags.tag_cd', '=', 'memo_tags.tag_cd')
            ->select([
                'memo_tags.memo_cd',
                'tags.tag_cd',
                'tags.tag_name',
            ])
            ->where('memo_tags.co_cd', $coCd)
            ->whereIn('memo_tags.memo_cd', $memoCds)
            ->where('tags.user_cd', $userCd)
            ->where('tags.del_flg', false)
            ->orderBy('tags.tag_cd', 'DESC')
            ->get()
            ->groupBy('memo_cd');
    }
}
