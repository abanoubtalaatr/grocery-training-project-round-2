<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderProcessingController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => $request->user()->id,
            'subtotal' => $request->subtotal ?? 100,
            'total' => $request->total ?? 100,
        ]);

        ProcessOrderJob::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Order is being processed',
            'data' => $order,
        ], 202);
    }

    public function show(Order $order)
    {
        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}