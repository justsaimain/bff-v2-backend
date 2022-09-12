<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ForgetVerifyController extends Controller
{
    public function __invoke(Request $request)
    {

        $otp = OtpCode::where('phone', $request->phone)
                     ->where('code', $request->code)
                     ->where("is_used", false)
                     ->whereDate('expired_in', '<', Carbon::now()->toDateTimeString())
                     ->first();
        if ($otp) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $user->password = $request->password;
                $user->update();
                $otp->is_used = true;
                $otp->update();

                return response()->json([
                    'success' => true,
                    'flag' => 'success_change_password',
                    'message' => 'Successfully changed your password',
                    'data' => $user,
                    'extra' => null
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'flag' => 'not_found',
                    'message' => 'User not found',
                    'data' => null,
                    'extra' => null
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'flag' => 'not_found',
                'message' => 'OTP not valid',
                'data' => null,
                'extra' => null
            ], 200);
        }
    }
}
