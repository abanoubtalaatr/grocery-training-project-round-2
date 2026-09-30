<?php

namespace App\Services\Email;

use App\Jobs\SendEmailJob;
use App\Models\Order;
use App\Services\Email\Contracts\EmailServiceInterface;

class EmailService implements EmailServiceInterface
{
    public function sendOrderConfirmation(Order $order): void
    {
        SendEmailJob::dispatch(
            new Mail\OrderConfirmationMail($order)
        );
    }

    public function sendPaymentReceipt(Order $order): void
    {
        SendEmailJob::dispatch(
            new Mail\PaymentReceiptMail($order)
        );
    }

    public function sendInvoice(Order $order): void
    {
        SendEmailJob::dispatch(
            new Mail\InvoiceMail($order)
        );
    }
}