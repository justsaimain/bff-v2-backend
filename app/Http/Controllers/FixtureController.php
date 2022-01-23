<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function index()
    {
        return view('fixture.index');
    }
}
