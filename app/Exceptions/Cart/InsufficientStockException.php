<?php

namespace App\Exceptions\Cart;

class InsufficientStockException extends CartException
{
    public function __construct(int $available)
    {
        parent::__construct("Only {$available} items available in stock");
    }
}