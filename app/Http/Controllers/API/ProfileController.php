<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'profile' => 'required'
        ]);

        try {
            $userId = Auth::guard('api')->user()->id;
            $user = User::find($userId);
            $file = $request->file('profile');
            $file_name = $userId.'.'.$file->getClientOriginalExtension();

            if (Storage::exists('public/profiles/'.$file_name)) {
                Storage::delete('public/profiles/'. $file_name);
            }

            $file_path = $file->storeAs('profiles', $file_name, 'public');
            $user->profile = $file_name;
            $user->update();

            return response()->json([
            'success' => true,
            'flag' => 'profile_update',
            'message' => 'Profile Updated',
            'data' => Storage::url('public/profiles/'.$file_name),
            'extra' => $user,
        ], 200);
        } catch (\Throwable $th) {
            return $th;
        }
      



        return 'success';
    }
}
