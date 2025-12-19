<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomersController extends Controller {
    public function regist(Request $request) {
        return response()->json([
            'requst' => $request->all(),
        ]);
    }
}
