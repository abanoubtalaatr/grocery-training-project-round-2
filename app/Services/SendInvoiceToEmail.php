<?php

namespace App\Services;

use App\Jobs\SendInvoiceEmailJob;

class SendInvoiceToEmail
{
    /**
     * Dispatch the job to send an invoice email.
     *
     * @param string $email       Recipient's email
     * @param string $pdfContent  The generated PDF content
     * @return void
     */
    public function send(string $email, string $pdfContent): void
    {
        // تحويل ملف الـ PDF إلى Base64 حتى يمكن تخزينه في قاعدة بيانات الطوابير (Queue) بدون مشاكل التشفير
        $base64Pdf = base64_encode($pdfContent);
        SendInvoiceEmailJob::dispatch($email, $base64Pdf);
    }
}
