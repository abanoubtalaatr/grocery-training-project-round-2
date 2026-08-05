<!doctype html>
<html lang="en">
<head>
    <style>
        body {
            font-family: sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->invoice_number }}</h1>
    <p>Billed to: {{ $invoice->user->email }}</p>
    <p>Date to: {{ $invoice->created_at->format('F j, Y') }}</p>

    <table>
        <tr>
            <th>Description</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>Invoice Total</td>
            <td>{{ number_format($invoice->total, 2)  }}</td>
        </tr>
    </table>
</body>
</html>