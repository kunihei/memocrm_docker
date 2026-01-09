<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoMemos extends Model
{
    protected $table = 'co_memos';
    public $timestamps =  false;
    protected $primaryKey = 'memo_cd';

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

    public static function memoRegist(int $coCd, string $title, string $content): CoMemos {
        $coMemo = self::create([
            'co_cd' => $coCd,
            'title' => $title,
            'content' => $content,
        ]);

        return $coMemo;
    }

    public static function memoUpdate(int $memoCd, string $title, string $content): bool {

        $coMemo = self::where('memo_cd', $memoCd)->lockForUpdate()->first();

        if (!$coMemo) {
            return false;
        }

        $coMemo->title = $title;
        $coMemo->content = $content;
        $coMemo->update_time = Carbon::now();
        $coMemo->saveOrFail();

        return true;
    }
}
