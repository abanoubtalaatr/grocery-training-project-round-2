<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendInvoiceJob;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        SendInvoiceJob::dispatch();

        return response()->json([
            "status"=>true,
            "message"=>"Invoice Added To Queue"
        ]);
    }
}