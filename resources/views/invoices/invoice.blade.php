<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
        }

        h1 {
            text-align: center;
        }

        .right {
            text-align: right;
        }
    </style>

</head>

<body>

    <h1>Invoice</h1>

    <hr>

    <h3>Customer Information</h3>

    <p>

        <strong>Name:</strong>

        {{ $order->user->name }}

    </p>

    <p>

        <strong>Email:</strong>

        {{ $order->user->email }}

    </p>

    <p>

        <strong>Order Number:</strong>

        {{ $order->order_number }}

    </p>

    <p>

        <strong>Payment:</strong>

        {{ $order->payment_method }}

    </p>

    <p>

        <strong>Status:</strong>

        {{ $order->status }}

    </p>

    <hr>

    <h3>Delivery Address</h3>

    <p>

        {{ optional($order->address)->full_address }}

    </p>

    <table>

        <thead>

            <tr>

                <th>Meal</th>

                <th>Qty</th>

                <th>Price</th>

                <th>Total</th>

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

                        {{ number_format($item->unit_price, 2) }}

                    </td>

                    <td>

                        {{ number_format($item->subtotal, 2) }}

                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    <br>

    <table>

        <tr>

            <td>Subtotal</td>

            <td class="right">

                {{ number_format($order->subtotal, 2) }}

            </td>

        </tr>

        <tr>

            <td>Tax</td>

            <td class="right">

                {{ number_format($order->tax, 2) }}

            </td>

        </tr>

        <tr>

            <td>Discount</td>

            <td class="right">

                {{ number_format($order->discount, 2) }}

            </td>

        </tr>

        <tr>

            <td>Shipping</td>

            <td class="right">

                {{ number_format($order->shipping_fee, 2) }}

            </td>

        </tr>

        <tr>

            <th>Grand Total</th>

            <th class="right">

                {{ number_format($order->total, 2) }}

            </th>

        </tr>

    </table>

</body>

</html>
