<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PDFInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $pdfPath;
    public $invoiceData;

    public function __construct($user, $pdfPath, $invoiceData = [])
    {
        $this->user = $user;
        $this->pdfPath = $pdfPath;
        $this->invoiceData = $invoiceData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'فاتورتك من نظامنا',
            to: [$this->user->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pdf-invoice',
            with: [
                'userName' => $this->user->name,
                'invoiceData' => $this->invoiceData,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('invoice-' . now()->format('Y-m-d') . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}