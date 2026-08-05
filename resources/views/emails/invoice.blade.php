<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body>

    <h2>Thank you for your order.</h2>

    <p>Hello {{ $order->user->firstname }},</p>

    <p>
        Your payment has been completed successfully.
    </p>

    <p>
        <strong>Order Number:</strong>
        {{ $order->order_number }}
    </p>

    <p>
        <strong>Total:</strong>
        ${{ number_format($order->total,2) }}
    </p>

    <p>
        Your invoice is attached to this email as a PDF.
    </p>

    <br>

    <p>
        Thank you for choosing us.
    </p>

</body>

</html>