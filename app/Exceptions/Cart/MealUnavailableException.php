<?php

namespace App\Exceptions\Cart;

class MealUnavailableException extends CartException
{
    public function __construct()
    {
        parent::__construct('This meal is currently unavailable');
    }
}