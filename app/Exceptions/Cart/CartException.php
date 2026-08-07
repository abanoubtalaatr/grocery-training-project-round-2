<?php

namespace App\Exceptions\Cart;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class CartException extends Exception
{
    protected int $status = 400;

    public function render(): JsonResponse
    {
       return response()->json([
           'success' => false,
           'message' => $this->getMessage(),
       ], $this->status);
    }
}