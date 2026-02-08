<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Tags extends Model
{
    protected $table = 'tags';
    public $timestamps = false;
    protected $primaryKey = 'tag_cd';

    protected $fillable = [
        'user_cd',
        'tag_name',
        'update_time',
        'del_flg',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];


    /**
     * タグの登録
     *
     * @param string $tagName
     * @return Tags
     */
    public static function tagRegist(int $userCd, string $tagName): Tags
    {
        $tag = self::create([
            'user_cd' => $userCd,
            'tag_name' => $tagName,
        ]);

        return $tag;
    }

    /**
     * タグの更新
     *
     * @param integer $userCd
     * @param integer $tagCd
     * @param string $tagName
     * @return boolean
     */
    public static function tagUpdate(int $userCd, int $tagCd, string $tagName): bool {
        $tag = self::where([
            ['user_cd', $userCd],
            ['tag_cd', $tagCd],
            ['del_flg', false],])->lockForUpdate()->first();
            if (!$tag) {
                return false;
            }
            $tag->tag_name = $tagName;
            $tag->update_time = now();
            $tag->saveOrFail();

            return true;
    }

    /**
     * タグの削除
     *
     * @param integer $userCd
     * @param integer $tagCd
     * @return boolean
     */
    public static function tagDelete(int $userCd, int $tagCd): bool {
        $tag = self::where([
            ['user_cd', $userCd],
            ['tag_cd', $tagCd],
            ['del_flg', false],
        ])->lockForUpdate()->first();
        if (!$tag) {
            return false;
        }
        $tag->del_flg = true;
        $tag->update_time = now();
        $tag->saveOrFail();
        return true;
    }

    /**
     * タグ一覧を取得
     *
     * @param integer $userCd
     * @return Collection
     */
    public static function tagList(int $userCd): Collection
    {
        return self::select([
            'tag_cd',
            'tag_name',
        ])->where([
            ['user_cd', $userCd],
            ['del_flg', false]
        ])->get();
    }
}
