<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $order->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        .header {
            text-align: center;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Grocery Store</h1>
    <h2>Invoice #{{ $order->id }}</h2>
</div>

<hr>

<p>
    Customer:
    {{ $order->user->name }}
</p>

<p>
    Email:
    {{ $order->user->email }}
</p>

<hr>

<h3>Order Details</h3>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr>
    </thead>

    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product->name }}
                </td>

                <td>
                    {{ $item->quantity }}
                </td>

                <td>
                    {{ $item->price }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<h3>
    Total: {{ $order->total }}
</h3>

<p>
    Thank you for shopping with us.
</p>

</body>
</html>