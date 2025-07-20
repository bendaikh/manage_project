<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            margin: 15px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .invoice-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary {
            margin-bottom: 25px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .summary-row {
            margin-bottom: 8px;
        }
        .summary-row:after {
            content: "";
            display: table;
            clear: both;
        }
        .summary-label {
            float: left;
            font-weight: bold;
        }
        .summary-value {
            float: right;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
            font-size: 10px;
        }
        td {
            font-size: 10px;
        }
        .total-section {
            margin-top: 25px;
            text-align: right;
            font-size: 12px;
        }
        .total-amount {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .customer-info {
            font-size: 9px;
            line-height: 1.3;
        }
        .product-info {
            font-size: 9px;
            line-height: 1.3;
        }
        .order-id {
            font-weight: bold;
            font-size: 10px;
        }
        .price {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(\App\Models\Setting::getLogoUrl())
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="Logo" style="max-height: 50px; max-width: 200px;">
            </div>
        @endif
        <div class="invoice-title">{{ \App\Models\Setting::getValue('app_name', 'DELIVERY INVOICE') }}</div>
        <div class="invoice-date">Generated on: {{ date('d/m/Y H:i') }}</div>
        <div class="invoice-date">Delivery Date: {{ date('d/m/Y', strtotime($today)) }}</div>
        <div class="invoice-date">Country: {{ \App\Models\Setting::getCountry() }}</div>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Orders Delivered:</span>
            <span class="summary-value">{{ $totalOrders }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Delivery Price per Order:</span>
            <span class="summary-value">{{ number_format(\App\Models\Setting::getDeliveryPrice(), 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Delivery Cost:</span>
            <span class="summary-value">{{ number_format($totalOrders * \App\Models\Setting::getDeliveryPrice(), 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Amount:</span>
            <span class="summary-value">{{ number_format($totalAmount, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Seller</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Delivery Cost</th>
                <th>Total Price</th>
                <th>Delivery Time</th>
                <th>Zone</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td class="order-id">#{{ $order->id }}</td>
                <td style="text-align: center;">{{ $order->seller ?? 'N/A' }}</td>
                <td class="customer-info">
                    <strong>{{ $order->client_name }}</strong><br>
                    {{ $order->client_address }}<br>
                    {{ $order->client_phone }}
                </td>
                <td class="product-info">
                    <strong>{{ $order->product?->name ?? 'N/A' }}</strong><br>
                    SKU: {{ $order->product?->sku ?? 'N/A' }}
                </td>
                <td style="text-align: center;">{{ $order->quantity }}</td>
                <td class="price">{{ number_format($order->price / $order->quantity, 0, ',', ' ') }} FCFA</td>
                <td class="price">{{ number_format(\App\Models\Setting::getDeliveryPrice(), 0, ',', ' ') }} FCFA</td>
                <td class="price">{{ number_format($order->price, 0, ',', ' ') }} FCFA</td>
                <td style="text-align: center;">{{ date('H:i', strtotime($order->updated_at)) }}</td>
                <td style="text-align: center;">{{ $order->zone ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div style="margin-bottom: 8px;">
            <span>Total Orders: </span>
            <span>{{ $totalOrders }}</span>
        </div>
        <div>
            <span>Total Amount: </span>
            <span class="total-amount">{{ number_format($totalAmount, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <div class="footer">
        <p>This invoice contains all orders delivered on {{ date('d/m/Y', strtotime($today)) }}</p>
        <p>Generated automatically by the system</p>
    </div>
</body>
</html> 