<html>
<head>
    <meta charset="utf-8" />
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Order Confirmation</h1>
    <p>Dear {{ $order->user->full_name ?? $order->user->username }},</p>
    <p>Your order has been received and is being processed.</p>
    <ul>
        <li>Order Number: {{ $order->order_number ?? $order->id }}</li>
        <li>Total: {{ number_format($order->total, 2) }}</li>
    </ul>

    <p>We will notify you when the order status changes.</p>

    <p>Regards,<br/>The Team</p>
</body>
</html>
