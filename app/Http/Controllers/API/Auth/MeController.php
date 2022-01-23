<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'flag' => 'authenticated',
            'message' => 'Authenticated',
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'region' => $user->region,
                'profile' => $user->profile,
                'fav_team' => $user->fav_team
            ]
        ], 200);
    }
}
