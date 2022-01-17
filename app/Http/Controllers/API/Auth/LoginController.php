<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function __invoke(Request $request)
    {
        if (!$token = auth('api')->attempt($request->only('phone', 'password'))) {
            return response([
                'success' =>  false,
                'message' => 'Incorrect credentials',
                'data' => []
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' =>  'Login Success',
            'data' => [
                'token' => $token
            ]
        ], 200);
    }
}
