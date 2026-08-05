<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .invoice {
            max-width: 700px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="invoice">

        <div class="header">
            <h1>🍽️ Your Restaurant</h1>
            <h3>Invoice #{{ $order->order_number }}</h3>
        </div>


        <div class="info">
            <p>
                <strong>Customer:</strong>
                {{ $order->user->name }}
            </p>

            <p>
                <strong>Email:</strong>
                {{ $order->user->email }}
            </p>
            @if ($order->address)
                <p>
                    <strong>Address:</strong>
                    {{ $order->address->full_address }}
                </p>
            @endif
            <p>
                <strong>Date:</strong>
                {{ $order->created_at->format('Y-m-d H:i') }}
            </p>
        </div>


        <h3>Order Details</h3>

        <table>

            <thead>
                <tr>
                    <th>Meal</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>


            <tbody>

                @foreach ($order->items as $item)
                    <tr>

                        <td>
                            {{ $item->meal->title }}
                        </td>

                        <td>
                            {{ $item->quantity }}
                        </td>

                        <td>
                            ${{ number_format($item->unit_price, 2) }}
                        </td>

                        <td>
                            ${{ number_format($item->subtotal, 2) }}
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>


        <div class="total">

            <p>
                Subtotal:
                ${{ number_format($order->subtotal, 2) }}
            </p>

            <p>
                Tax:
                ${{ number_format($order->tax, 2) }}
            </p>

            <p>
                Shipping:
                ${{ number_format($order->shipping_fee, 2) }}
            </p>


            <h2>
                Total:
                ${{ number_format($order->total, 2) }}
            </h2>

        </div>


        <div class="footer">

            <p>
                Thank you for your order ❤️
            </p>

        </div>


    </div>

</body>

</html>
