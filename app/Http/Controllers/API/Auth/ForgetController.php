<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ForgetController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        $otp = OtpCode::where('phone', $request->phone)
        ->where("is_used", false)
        ->whereDate('expired_in', '<', Carbon::now()->toDateTimeString())
        ->first();

        if($otp){
            return response()->json([
                'success' => false,
                'flag' => 'already_exist',
                'message' => 'OTP already exist',
                'data' => $otp,
                'extra' => null
            ], 200);
        }else{
            if ($user) {
                $otp_code = random_int(100000, 999999);
                OtpCode::create([
                 "phone"=> $request->phone,
                 "code" => $otp_code,
                 "type" => "forget_password",
                 "expired_in" => Carbon::now()->addMinutes(3)
                ]);
    
                $response = Http::withToken(env('SMSPOH_TOKEN'))->post('https://smspoh.com/api/v2/send', [
                    "to" => $request->phone,
                    "sender" => "BFF Sports",
                    "message" => "Your OTP code for forget password is " . $otp_code,
                ]);
    
                return response()->json([
                    'success' => true,
                    'flag' => 'forget_otp',
                    'message' => 'Please verify your phone number',
                    'data' => $response->json(),
                    'extra' => null
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'flag' => 'not_found',
                    'message' => 'User not found',
                    'data' => null,
                    'extra' => null
                ], 200);
            }
        }
        
        
    }
}
