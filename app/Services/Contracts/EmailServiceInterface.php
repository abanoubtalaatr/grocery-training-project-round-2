<?php

namespace App\Services\Email\Contracts;

use App\Models\Order;

interface EmailServiceInterface
{
    public function sendOrderConfirmation(Order $order): void;

    public function sendPaymentReceipt(Order $order): void;

    public function sendInvoice(Order $order): void;
}