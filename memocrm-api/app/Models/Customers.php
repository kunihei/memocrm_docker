<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Customers extends Model
{
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
    public static function coRegist(int $userCd, string $coName, string $address, string $tantoName, string $tantoTel): array
    {
        $customer = self::create([
            'user_cd' => $userCd,
            'co_name' => $coName,
            'co_address' => $address,
            'co_tanto_name' => $tantoName,
            'co_tanto_tel' => $tantoTel,
        ]);
        return $customer->toArray();
    }

    /**
     * 顧客情報の更新
     *
     * @param integer $userCd
     * @param integer $coCd
     * @param string $coName
     * @param string $address
     * @param string $tantoName
     * @param string $tantoTel
     * @return boolean
     */
    public static function coUpdate(int $userCd, int $coCd, string $coName, string $address, string $tantoName, string $tantoTel): bool
    {
        $customer = self::where(
            [
                ['user_cd', $userCd],
                ['co_cd', $coCd],
                ['del_flg', false]
            ]
        )->lockForUpdate()->first();
        if (!$customer) {
            // 該当データなし
            return false;
        }
        $customer->co_name = $coName;
        $customer->co_address = $address;
        $customer->co_tanto_name = $tantoName;
        $customer->co_tanto_tel = $tantoTel;
        $customer->update_time = Carbon::now();
        $customer->saveOrFail(); // 失敗なら例外で上位へ

        return true;
    }

    /**
     * 顧客情報の削除
     *
     * @param integer $userCd
     * @param integer $coCd
     * @return boolean
     */
    public static function coDeleete(int $userCd, int $coCd): bool
    {
        $customer = self::where(
            [
                ['user_cd', $userCd],
                ['co_cd', $coCd]
            ]
        )->lockForUpdate()->first();
        if (!$customer) {
            return false;
        }
        $customer->del_flg = true;
        $customer->update_time = Carbon::now();
        $customer->saveOrFail();

        return true;
    }

    public static function getList(int $userCd)
    {
        $customers = self::select(
            [
                'co_cd',
                'co_name',
                'co_address',
                'co_tanto_name',
                'co_tanto_tel',
            ]
        )->where(
            [
                ['user_cd', $userCd],
                ['del_flg', false]
            ]
        )->orderBy('co_cd', 'desc')->get();
        return $customers;
    }
}
