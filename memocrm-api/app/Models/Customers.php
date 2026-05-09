<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Data\CustomerData;

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
     * @param CustomerData $data
     * @return Customers
     */
    public static function coRegist(int $userCd, CustomerData $data): Customers
    {
        $customer = self::create([
            'user_cd' => $userCd,
            'co_name' => $data->coName,
            'co_address' => $data->coAddress,
            'co_tanto_name' => $data->tantoName,
            'co_tanto_tel' => $data->tantoTel,
        ]);

        return $customer;
    }

    /**
     * 顧客情報の更新
     *
     * @param integer $userCd
     * @param integer $coCd
     * @param CustomerData $data
     * @return boolean
     */
    public static function coUpdate(int $userCd, int $coCd, CustomerData $data): bool
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
        $customer->co_name = $data->coName;
        $customer->co_address = $data->coAddress;
        $customer->co_tanto_name = $data->tantoName;
        $customer->co_tanto_tel = $data->tantoTel;
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

    /**
     * 顧客情報の取得
     *
     * @param integer $userCd
     * @return Collection
     */
    public static function getList(int $userCd): Collection
    {
        $customers = self::select(
            [
                'customers.co_cd',
                'customers.co_name',
                'customers.co_address',
                'customers.co_tanto_name',
                'customers.co_tanto_tel',
                DB::raw('COUNT(co_memos.memo_cd) as memo_count'),
            ]
        )->join('co_memos', function ($join) {
            $join->on('customers.co_cd', '=', 'co_memos.co_cd')
                ->where('co_memos.del_flg', false);
        })->where(
            [
                ['customers.user_cd', $userCd],
                ['customers.del_flg', false]
            ]
        )->groupBy(
            [
                'customers.co_cd',
                'customers.co_name',
                'customers.co_address',
                'customers.co_tanto_name',
                'customers.co_tanto_tel',
            ]
        )->orderBy('co_cd', 'desc')->get();

        return $customers;
    }
}
