<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $options = Option::first();
        return view('setting', compact('options'));
    }

    public function updateCurrentGameWeek(Request $request)
    {
        $request->validate([
            'gameweek' => 'required'
        ]);

        $options = Option::find(1);
        $options->current_gameweek = $request->gameweek;
        $options->update();
        return back();
    }
}
