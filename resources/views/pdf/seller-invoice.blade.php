<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 15px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 8px; }
        .invoice-date { font-size: 12px; color: #666; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; font-size: 10px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-section { margin-top: 25px; text-align: right; font-size: 12px; }
        .total-amount { font-size: 16px; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        @if(\App\Models\Setting::getLogoUrl())
            <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="Logo" style="max-height: 50px; max-width: 200px; margin-bottom: 10px;">
        @endif
        <div class="invoice-title">Seller Invoice - {{ $orders->first()->seller ?? 'N/A' }}</div>
        <div class="invoice-date">Delivery Date: {{ date('d/m/Y', strtotime($today)) }}</div>
        <div class="invoice-date">Generated on: {{ date('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Customer</th>
                <th>Zone</th>
                <th>Delivery Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->product?->name ?? 'N/A' }} (SKU: {{ $order->product?->sku ?? 'N/A' }})</td>
                <td style="text-align: center;">{{ $order->quantity }}</td>
                <td style="text-align: right;">{{ number_format($order->price / $order->quantity, 0, ',', ' ') }} FCFA</td>
                <td style="text-align: right;">{{ number_format($order->price, 0, ',', ' ') }} FCFA</td>
                <td>{{ $order->client_name }}<br>{{ $order->client_phone }}</td>
                <td style="text-align: center;">{{ $order->zone ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ date('H:i', strtotime($order->updated_at)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div>Total Orders: {{ $totalOrders }}</div>
        <div>Total Products Price: {{ number_format($productTotal, 0, ',', ' ') }} FCFA</div>
        <div>Total Delivery Cost: {{ number_format($deliveryCostTotal, 0, ',', ' ') }} FCFA</div>
        <div class="total-amount">Net Total Amount: {{ number_format($totalAmount, 0, ',', ' ') }} FCFA</div>
    </div>
</body>
</html> 