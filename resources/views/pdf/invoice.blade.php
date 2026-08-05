<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .totals { margin-top: 15px; width: 300px; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand-total { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <h2>Invoice {{ $receipt['invoice_number'] }}</h2>
    <p>Date: {{ \Carbon\Carbon::parse($receipt['date'])->format('d M Y, H:i') }}</p>

    <p>
        <strong>Customer:</strong> {{ $receipt['customer']['name'] }}<br>
        <strong>Email:</strong> {{ $receipt['customer']['email'] }}<br>
        @if($receipt['customer']['phone'])
            <strong>Phone:</strong> {{ $receipt['customer']['phone'] }}<br>
        @endif
    </p>

    @if($receipt['delivery_address'])
        <p>
            <strong>Delivery Address:</strong><br>
            {{ $receipt['delivery_address']['full_address'] ?? $receipt['delivery_address']['street_address'] }}
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt['items'] as $item)
                <tr>
                    <td>{{ $item['meal']['title'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ number_format($item['unit_price'], 2) }}</td>
                    <td>{{ number_format($item['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td>{{ number_format($receipt['pricing']['subtotal'], 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td>{{ number_format($receipt['pricing']['tax'], 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td>-{{ number_format($receipt['pricing']['discount'], 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total</td>
            <td>{{ number_format($receipt['pricing']['total'], 2) }}</td>
        </tr>
    </table>
</body>
</html>