<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function index()
    {
        return view('prediction.index');
    }

    public function show($id)
    {
        $prediction  = Prediction::find($id);
        return $prediction;
    }
}
