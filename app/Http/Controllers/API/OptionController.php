<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function __invoke()
    {
        $options = Option::first();
        return response()->json([
            'success' => true,
            'flag' => 'options',
            'message' => 'Get Options',
            'data' => $options
        ], 200);
    }
}
