<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    public function regist(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'co_name' => ['required', 'string'],
            'co_address' => ['required', 'string'],
            'co_tanto_name' => ['required', 'string'],
            'co_tanto_tel' => ['required', 'string'],
        ]);
        if ($validated->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validated->errors(),
            ], 422);
        }
        return response()->json([
            'requst' => $request->all(),
        ]);
    }
}
