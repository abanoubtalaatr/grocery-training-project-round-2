<!DOCTYPE html>
<html>
<body>
    <h2>Hello, {{ $order->user->name }}!</h2>
    <p>Thank you for your purchase. Please find your invoice for Order <strong>#{{ $order->order_number }}</strong> attached to this email.</p>
    <p>Total Paid: <strong>${{ number_format($order->total_amount, 2) }}</strong></p>
</body>
</html>