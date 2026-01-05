<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoMemos extends Model
{
    protected $table = 'co_memos';
    public $timestamps =  false;
    protected $primaryKey = 'co_cd';

    protected $fillable = [
        'memo_cd',
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
}
