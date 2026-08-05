<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
</head>

<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:40px 0;">
        <tr>
            <td align="center">

                <table width="650" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:10px;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#198754;padding:30px;">
                            <h1 style="margin:0;color:#ffffff;">
                                Restaurant
                            </h1>

                            <p style="margin-top:10px;color:#ffffff;">
                                Your order has been confirmed
                            </p>
                        </td>
                    </tr>

                    <!-- Welcome -->
                    <tr>
                        <td style="padding:30px;">

                            <h2 style="margin-top:0;">
                                Hello {{ $order->user->name }},
                            </h2>

                            <p>
                                Thank you for choosing our restaurant.
                            </p>

                            <p>
                                Your order has been successfully placed.
                            </p>

                            <p>
                                Your invoice is attached as a PDF file with this email.
                            </p>

                        </td>
                    </tr>

                    <!-- Order Summary -->
                    <tr>
                        <td style="padding:0 30px 30px;">

                            <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;">

                                <tr>
                                    <td style="background:#f8f9fa;"><strong>Order Number</strong></td>
                                    <td style="background:#f8f9fa;">{{ $order->order_number }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>{{ ucfirst($order->status) }}</td>
                                </tr>

                                <tr>
                                    <td style="background:#f8f9fa;"><strong>Payment Method</strong></td>
                                    <td style="background:#f8f9fa;">
                                        {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Delivery Type</strong></td>
                                    <td>{{ ucfirst($order->delivery_type) }}</td>
                                </tr>

                                <tr>
                                    <td style="background:#f8f9fa;"><strong>Total</strong></td>
                                    <td style="background:#f8f9fa;">
                                        {{ number_format($order->total, 2) }} EGP
                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>

                    <!-- Meals -->
                    <tr>
                        <td style="padding:0 30px 30px;">

                            <h3>Order Items</h3>

                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">

                                <thead>

                                    <tr style="background:#198754;color:white;">

                                        <th align="left">Meal</th>

                                        <th>Qty</th>

                                        <th>Price</th>

                                        <th>Total</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($order->items as $item)
                                        <tr>

                                            <td>{{ $item->meal->title }}</td>

                                            <td align="center">
                                                {{ $item->quantity }}
                                            </td>

                                            <td align="center">
                                                {{ number_format($item->unit_price, 2) }}
                                            </td>

                                            <td align="center">
                                                {{ number_format($item->subtotal, 2) }}
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:25px;background:#f8f9fa;">

                            <p style="margin:0;font-size:15px;">
                                Thank you for your order ❤️
                            </p>

                            <p style="margin-top:10px;color:#666;">
                                If you have any questions, feel free to contact us.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
