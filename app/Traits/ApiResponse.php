<?php

namespace App\Traits;

trait ApiResponse
{
    public function Success(mixed $data , $message = "success" , $status = 200)
    {
        return response()->json([
            "message" => $message,
            "data" => $data
        ] , $status);
    }

    public function Error(mixed $data , $message = "error" , $status = 400)
    {
        return response()->json([
            "message" => $message,
            "data" => $data
        ] , $status);
    }
}