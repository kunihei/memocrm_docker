<?php

namespace App\Models;

use Illuminate\Database\Eloquent\model;

class Customers extends model {
    protected $table = 'customers';
    public $timestamps =  false;
    protected $primaryKey = 'co_cd';

    protected $fillable = [
        'user_cd',
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


    /**
     * 顧客情報の登録
     *
     * @param integer $userCd
     * @param string $coName
     * @param string $address
     * @param string $tantoName
     * @param string $tantoTel
     * @return array
     */
    public static function regist(int $userCd, string $coName, string $address, string $tantoName, string $tantoTel): array {
        $customer = self::create([
            'user_cd' => $userCd,
            'co_name' => $coName,
            'co_address' => $address,
            'co_tanto_name' => $tantoName,
            'co_tanto_tel' => $tantoTel,
        ]);
        return $customer->toArray();
    }
}