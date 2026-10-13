<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 3px 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 45%;
            margin-left: auto;
            margin-top: 20px;
        }

        .totals td {
            border: none;
            padding: 5px 8px;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #333 !important;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>

</head>

<body>

    <div class="header">
        <h1>{{ config('app.name') }} - Invoice</h1>

        <p>
            <strong>Order Number:</strong>
            {{ $order->order_number }}
        </p>

        <p>
            <strong>Order Date:</strong>
            {{ optional($order->placed_at ?? $order->created_at)->format('F j, Y g:i A') }}
        </p>
        

    </div>

    <div class="section">
        <div class="section-title">Customer Information</div>

        
        <p>
            <strong>Name:</strong>
            {{ $order->user->name ?? 'N/A' }}
        </p>

        <p>
            <strong>Email:</strong>
            {{ $order->user->email ?? 'N/A' }}
        </p>
        

    </div>

    <div class="section">
        <div class="section-title">Order Items</div>

        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->meal->title ?? 'Meal' }}
                        </td>

                        <td class="text-right">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            {{ number_format((float) $item->unit_price, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format((float) $item->discount_amount, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format((float) $item->subtotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        

    </div>

    <div class="section">
        <div class="section-title">Order Summary</div>

        
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">
                    {{ number_format((float) $order->subtotal, 2) }}
                </td>
            </tr>

            <tr>
                <td>Tax</td>
                <td class="text-right">
                    {{ number_format((float) $order->tax, 2) }}
                </td>
            </tr>

            <tr>
                <td>Discount</td>
                <td class="text-right">
                    -{{ number_format((float) $order->discount, 2) }}
                </td>
            </tr>

            <tr>
                <td>Shipping Fee</td>
                <td class="text-right">
                    {{ number_format((float) $order->shipping_fee, 2) }}
                </td>
            </tr>

            <tr class="total">
                <td>Total</td>
                <td class="text-right">
                    {{ number_format((float) $order->total, 2) }}
                </td>
            </tr>
        </table>
        

    </div>

    <div class="section">
        <div class="section-title">Payment & Delivery</div>

        
        <p>
            <strong>Payment Method:</strong>
            {{ str_replace('_', ' ', ucfirst($order->payment_method)) }}
        </p>

        <p>
            <strong>Delivery Type:</strong>
            {{ ucfirst($order->delivery_type) }}
        </p>

        @if ($order->delivery_speed)
            <p>
                <strong>Delivery Speed:</strong>
                {{ $order->delivery_speed }}
            </p>
        @endif

        @if ($order->schedule_delivery)
            <p>
                <strong>Scheduled Delivery:</strong>
                {{ $order->schedule_delivery }}
            </p>
        @endif
    

    </div>

    @if ($order->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <p>{{ $order->notes }}</p>
        </div>
    @endif

    <div class="footer">
        Thank you for your order!
    </div>

</body>

</html>
