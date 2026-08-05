<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة الطلب</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            text-align: right; 
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h2>فاتورة للطلب رقم #{{ $order->order_number }}</h2>
    <p>التاريخ: {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
    <p>العميل: {{ optional($order->user)->name }}</p>

    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ optional($item->meal)->title }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-left"><strong>المجموع الفرعي</strong></td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>الضريبة</strong></td>
                <td>${{ number_format($order->tax, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>التوصيل</strong></td>
                <td>${{ number_format($order->shipping_fee, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>الإجمالي</strong></td>
                <td><strong>${{ number_format($order->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
