<x-mail::message>
# Invoice #{{ $order->order_number }}

Hi {{ $order->user->firstname ?? 'there' }},

Thanks for your order! Please find your invoice attached to this email.

**Order Number:** {{ $order->order_number }}
**Total:** {{ number_format($order->total, 2) }}
**Status:** {{ ucfirst($order->status) }}

<x-mail::button :url="config('app.url')">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>