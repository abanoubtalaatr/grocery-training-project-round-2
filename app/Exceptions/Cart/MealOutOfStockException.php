<?php

namespace App\Exceptions\Cart;

class MealOutOfStockException extends CartException
{
    public function __construct()
    {
        parent::__construct('This meal is out of stock');
    }
}