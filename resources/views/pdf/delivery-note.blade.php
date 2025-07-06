<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .totals { margin-top: 20px; font-size: 14px; }
        .grand-total { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <h2>Delivery Note</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Seller</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Client</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
            <tr>
                <td>{{ $o->id }}</td>
                <td>{{ $o->seller }}</td>
                <td>{{ $o->product?->name ?? 'N/A' }}</td>
                <td>{{ $o->quantity }}</td>
                <td>{{ number_format($o->price, 0, ',', ' ') }}</td>
                <td>{{ $o->client_name }}</td>
                <td>{{ $o->orderStatus?->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <h4>Totals</h4>
        <p>Total Orders Price: {{ number_format($totalPrice, 0, ',', ' ') }} FCFA</p>
        <p>Total Shipping (1500 x {{ count($orders) }}): {{ number_format($shippingTotal, 0, ',', ' ') }} FCFA</p>
        <p class="grand-total">Grand Total (Price - Shipping): {{ number_format($grandTotal, 0, ',', ' ') }} FCFA</p>
    </div>
</body>
</html> 