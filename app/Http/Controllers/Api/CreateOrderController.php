<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use App\Jobs\SendInvoiceJob;
use App\Jobs\CallingCompanyJob;

class CreateOrderController extends Controller
{
    public function createAnOrder(Request $request)
    {
        
        // SendEmailJob::dispatch($request->all());
        // SendInvoiceJob::dispatch($request->all());
        // CallingCompanyJob::dispatch($request->all());
        // create an order
        // send notification to the user
        // create invoice
        // send email to the user

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'queued' => true,
        ]);
    }
}
