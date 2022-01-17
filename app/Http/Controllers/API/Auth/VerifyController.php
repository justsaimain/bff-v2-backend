<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyController extends Controller
{
    public function __invoke(Request $request)
    {

        $user = User::where('phone', $request->number)->first();

        $response = Http::get('https://verify.smspoh.com/api/v1/verify', [
            "access-token" => env('SMSPOH_TOKEN'),
            "request_id" => $request->request_id,
            "code" => $request->code
        ]);

        $data = $response->json();

        if ($data['status'] === true) {
            $user->phone_verified_at = Carbon::now();

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
                'data' => $user,
                'extra' => $response->json()
            ], 200);
        }
    }
}
