<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Invoice for Order #{{ $order->order_number }}</h2>
    <p>Hi {{ $order->user->name }},</p>
    <p>Thank you for your order. Here are your invoice details:</p>

    <table style="width: 100%; border-collapse: collapse;">
        @foreach ($order->items as $item)
            <tr style="border-bottom: 1px solid #ddd;">
                <td>{{ $item->meal->title }}</td>
                <td>x{{ $item->quantity }}</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <p><strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}</p>
    <p><strong>Tax:</strong> ${{ number_format($order->tax, 2) }}</p>
    <p><strong>Shipping:</strong> ${{ number_format($order->shipping_fee, 2) }}</p>
    <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>

    <p>Thanks for shopping with us!</p>
</body>
</html>