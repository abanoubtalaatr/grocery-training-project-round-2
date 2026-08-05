<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public PDF $pdf
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice #' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdf->output(),
                'invoice-' . $this->order->order_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}