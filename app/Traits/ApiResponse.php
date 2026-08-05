<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data, $message = 'Success', $code = 200, $message_2 = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'message_2' => $message_2,
        ], $code);
    }
    public function error($message = 'Error', $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}