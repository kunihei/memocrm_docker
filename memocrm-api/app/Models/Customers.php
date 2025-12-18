<?php

namespace App\Models;

use Illuminate\Database\Eloquent\model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customers extends model {
    protected $table = 'customers';
    public $timestamps =  false;
    protected $primaryKey = 'co_cd';

    protected $fillable = [
        'co_cd',
        'user_id',
        'co_name',
        'co_address',
        'co_tanto_name',
        'co_tanto_tel',
        'update_time',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
}