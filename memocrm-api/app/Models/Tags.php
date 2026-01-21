<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tags extends Model {
    protected $table = 'tags';
    public $timestamps = false;
    protected $primaryKey = 'tag_cd';

    protected $fillable = [
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
    public static function tagRegist(string $tagName): Tags {
        $tag = self::create([
            'tag_name' => $tagName,
        ]);
        return $tag;
    }
}