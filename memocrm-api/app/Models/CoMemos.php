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

    public static function memoRegist(int $coCd, string $title, string $content): array {
        $comemo = self::create([
            'co_cd' => $coCd,
            'title' => $title,
            'content' => $content,
        ]);
        if (empty($comemo->memo_cd)) {
            throw new \RuntimeException("メモの登録に失敗しました。");
        }
        return $comemo->toArray();
    }

    public static function memoUpdate(int $coCd, string $title, string $content): bool {

        $comemo = self::where('co_cd', $coCd)->lockForUpdate()->first();

        if (!$comemo) {
            return false;
        }
        
        $comemo->title = $title ?? '';
        $comemo->content = $content ?? '';
        $comemo->update_time = Carbon::now();
        $comemo->saveOrFail();

        return true;
    }
}
