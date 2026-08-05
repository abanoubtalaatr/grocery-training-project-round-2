<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans,sans-serif;
            font-size:13px;
            color:#333;
        }

        h1,h2,h3,h4{
            margin:0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:8px;
        }

        table th{
            background:#f5f5f5;
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .header{
            margin-bottom:25px;
        }

        .section{
            margin-top:20px;
        }

        .total-table{
            width:40%;
            float:right;
            margin-top:20px;
        }

        .footer{
            margin-top:60px;
            text-align:center;
            color:#777;
            font-size:11px;
        }

    </style>

</head>

<body>

<div class="header">

    <h2>Invoice</h2>

    <br>

    <table>

        <tr>
            <td>

                <strong>Invoice #</strong><br>

                INV-{{ str_pad($order->id,8,'0',STR_PAD_LEFT) }}

            </td>

            <td>

                <strong>Order #</strong><br>

                {{ $order->order_number }}

            </td>

            <td>

                <strong>Date</strong><br>

                {{ optional($order->placed_at)->format('Y-m-d H:i') }}

            </td>

        </tr>

    </table>

</div>

<div class="section">

    <h3>Customer Information</h3>

    <table>

        <tr>

            <td>Name</td>

            <td>

                {{ $order->user->firstname }}

                {{ $order->user->lastname }}

            </td>

        </tr>

        <tr>

            <td>Email</td>

            <td>{{ $order->user->email }}</td>

        </tr>

        <tr>

            <td>Phone</td>

            <td>

                {{ $order->user->country_code }}

                {{ $order->user->phone }}

            </td>

        </tr>

    </table>

</div>

@if($order->address)

<div class="section">

    <h3>Delivery Address</h3>

    <table>

        <tr>

            <td>

                {{ $order->address->full_address }}

            </td>

        </tr>

    </table>

</div>

@endif


<div class="section">

    <h3>Order Items</h3>

    <table>

        <thead>

        <tr>

            <th>#</th>

            <th>Meal</th>

            <th>Qty</th>

            <th>Unit Price</th>

            <th>Total</th>

        </tr>

        </thead>

        <tbody>

        @foreach($order->items as $item)

            <tr>

                <td class="text-center">

                    {{ $loop->iteration }}

                </td>

                <td>

                    {{ $item->meal->title }}

                </td>

                <td class="text-center">

                    {{ $item->quantity }}

                </td>

                <td class="text-right">

                    ${{ number_format($item->unit_price,2) }}

                </td>

                <td class="text-right">

                    ${{ number_format($item->subtotal,2) }}

                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>


<table class="total-table">

    <tr>

        <td>Subtotal</td>

        <td class="text-right">

            ${{ number_format($order->subtotal,2) }}

        </td>

    </tr>

    <tr>

        <td>Tax</td>

        <td class="text-right">

            ${{ number_format($order->tax,2) }}

        </td>

    </tr>

    <tr>

        <td>Discount</td>

        <td class="text-right">

            ${{ number_format($order->discount,2) }}

        </td>

    </tr>

    <tr>

        <th>Total</th>

        <th class="text-right">

            ${{ number_format($order->total,2) }}

        </th>

    </tr>

</table>


<div style="clear:both"></div>

<div class="footer">

    Thank you for your purchase.

</div>

</body>

</html>