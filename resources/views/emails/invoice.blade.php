<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice</title>
</head>
<body>
    <h1>Invoice for Order {{ $order->order_number ?? $order->id }}</h1>
    <p>Dear {{ $order->user->full_name ?? $order->user->username }},</p>
    <p>Thank you for your order. Below is a summary:</p>

    <h3>Order Details</h3>
    <ul>
        <li>Order Number: {{ $order->order_number ?? $order->id }}</li>
        <li>Status: {{ $order->status }}</li>
        <li>Total: {{ number_format($order->total, 2) }}</li>
        <li>Placed at: {{ $order->placed_at ?? $order->created_at }}</li>
    </ul>

    <h3>Items</h3>
    <ul>
        @foreach($order->items as $item)
            <li>{{ $item->meal->title ?? 'Item' }} × {{ $item->quantity }} — {{ number_format($item->subtotal, 2) }}</li>
        @endforeach
    </ul>

    <p>If you have any questions, reply to this email.</p>

    <p>Regards,<br/>The Team</p>
</body>
</html>
