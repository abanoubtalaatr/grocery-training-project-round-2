@component('mail::message')
    # Your Invoice is Ready

    Hi {{ $invoice->user->email }},

    Your invoice <b>{{ $invoice->invoice_number }}</b> for <b>{{ number_format($invoice->total, 2) }}</b> is attached to this email as a PDF.

    Thanks,
    {{ config('app.name') }}
@endcomponent
