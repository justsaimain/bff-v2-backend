<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class GetOTPController extends Controller
{
    public function __invoke(Request $request)
    {

        $response = Http::get('https://verify.smspoh.com/api/v1/request', [
            "access-token" => env('SMSPOH_TOKEN'),
            "number" => $request->number,
            "brand_name" => "BFF Sports",
            "code_length" => 6,
            "sender_name" => "BFF Sports",
            "template" => "{brand_name} အတွက် သင်၏အတည်ပြုရန်ကုဒ်နံပါတ်မှာ {code} ဖြစ်ပါတယ်",
        ]);

        return response()->json([
            'success' => true,
            'flag' => 'resend_otp',
            'message' => 'Resend OTP Code',
            'data' => [
                'number' => $request->number
            ],
            'extra' => $response->json()
        ], 200);
    }
}
