<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $user = User::create($request->all());

        return response()->json([
            'success' => true,
            'flag' => 'verify_otp',
            'message' => 'User account created',
            'data' => $user
        ], 200);
    }
}
