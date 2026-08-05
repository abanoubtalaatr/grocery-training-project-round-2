<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
</head>
<body>

    <h2>Hello {{ $order->user->name }}</h2>

    <p>
        Thank you for your order.
    </p>

    <p>
        Your invoice has been generated successfully.
    </p>

    <p>
        Order Number: #{{ $order->id }}
    </p>

    <p>
        Total Amount: {{ $order->total }}
    </p>

    <p>
        Please find your invoice attached as a PDF file.
    </p>

    <br>

    <p>
        Regards,<br>
        Grocery Store Team
    </p>

</body>
</html>