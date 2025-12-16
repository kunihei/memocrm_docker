<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Carbon\Carbon;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['メールアドレスかパスワードが違います'],
            // ]);
            return response()->json([
                'error' => 'メールアドレスかパスワードが違います',
            ]);
        }

        $tokenName = $request->input('device_name', 'mobile');

        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json([
            'ok' => true,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
