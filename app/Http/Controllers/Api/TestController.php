<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class TestController extends Controller
{
   public function index()
   {
      return response()->json([
         'success' => true,
         'message' => 'Test API',
         'data' => ['test' => 'This is a test API endpoint.'],
      ]);
   }
}
