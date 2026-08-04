<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testController extends Controller
{
    public function forTest()
    {
        return response()->json('This Message For Test', 200);
    }
}
