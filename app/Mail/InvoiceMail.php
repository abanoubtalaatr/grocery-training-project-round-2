<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Invoice #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                function () {
                    return Pdf::loadView('pdf.invoice', [
                        'order' => $this->order,
                    ])->output();
                },
                'invoice-'.$this->order->id.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}