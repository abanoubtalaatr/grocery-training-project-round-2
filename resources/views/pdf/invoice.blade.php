<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: auto; padding: 20px; }
        .header-table, .items-table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 5px 0; vertical-align: top; }
        .title { font-size: 26px; font-weight: bold; color: #2563eb; }
        .text-right { text-align: right; }
        .items-table { margin-top: 30px; }
        .items-table th { background: #f3f4f6; color: #374151; padding: 10px; border-bottom: 2px solid #e5e7eb; text-align: left; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .total-row td { font-weight: bold; font-size: 15px; border-top: 2px solid #374151; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td class="title">INVOICE</td>
                <td class="text-right">
                    <strong>Order #:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}
                </td>
            </tr>
            <tr>
                <td style="padding-top: 20px;">
                    <strong>Billed To:</strong><br>
                    {{ $order->user->name }}<br>
                    {{ $order->user->email }}
                </td>
                <td class="text-right" style="padding-top: 20px;">
                    <strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'Card') }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>