<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    public Order $order;
    public string  $pdfBinaryContent;
    /**
     * Create a new message instance.
     */
    public function __construct(Order $order,string $pdfBinaryContent)
    {
        $this->order=$order;
        $this->pdfBinaryContent=$pdfBinaryContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return  new Envelope(
            subject: "Invoice for Order #{$this->order->order_number}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
       return new Content(
            view: 'emails.invoice-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
       return [
            Attachment::fromData(
                fn () => $this->pdfBinaryContent,
                "Invoice-{$this->order->order_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
