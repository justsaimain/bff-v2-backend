<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $request->validate([
            'phone' => 'required|unique:users,phone|max:255',
        ]);

        Cache::put('user__register__' . $request->phone, $request->all(), Carbon::now()->addMinute());


        $response = Http::get('https://verify.smspoh.com/api/v1/request', [
            "access-token" => env('SMSPOH_TOKEN'),
            "number" => $request->phone,
            "brand_name" => "BFF Sports",
            "code_length" => 6,
            "sender_name" => "BFF Sports",
            "template" => "{brand_name} အတွက် သင်၏အတည်ပြုရန်ကုဒ်နံပါတ်မှာ {code} ဖြစ်ပါတယ်",
        ]);

        return response()->json([
            'success' => true,
            'flag' => 'verify_otp',
            'message' => 'Please verify your phone number',
            'data' => $response->json(),
            'extra' => null
        ], 200);
    }
}
