<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoMemos extends Model
{
    protected $table = 'co_memos';
    public $timestamps =  false;
    protected $primaryKey = 'co_cd';

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
        return $comemo->toArray();
    }
}
