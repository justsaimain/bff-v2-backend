<?php

namespace App\Http\Controllers\API\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VerifyController extends Controller
{
    public function __invoke(Request $request)
    {
        $cache_date = Cache::get('user__register__' . $request->number);


        if (!$cache_date) {
            return response()->json([
                'success' => false,
                'flag' => 'not_cache_found',
                'message' => 'Register timeout, please try again.',
                'data' => null,
                'extra' => null
            ], 200);
        }

        $response = Http::get('https://verify.smspoh.com/api/v1/verify', [
            "access-token" => env('SMSPOH_TOKEN'),
            "request_id" => $request->request_id,
            "code" => $request->code
        ]);

        $data = $response->json();

        if ($data['status'] === true) {
            $user = User::create($cache_date);
            if (!$userToken = JWTAuth::fromUser($user)) {
                return response()->json([
                    'success' => false,
                    'flag' => 'invalid_credentials',
                    'message' => 'Invalid Credentials',
                    'data' => $user,
                ], 401);
            }

            return response()->json([
                'success' => true,
                'flag' => 'verified',
                'message' => 'User account verified',
                'data' => [
                    'user' => $user,
                    'token' => $userToken
                ],
                'extra' => $response->json()
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'flag' => 'wrong',
                'message' => 'Something was wrong',
                'data' => $response->json(),
                'extra' => null
            ], 200);
        }
    }
}
