<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 15px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 8px; }
        .invoice-date { font-size: 12px; color: #666; margin-bottom: 5px; }
        .week-period { font-size: 14px; font-weight: bold; color: #444; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; font-size: 10px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-section { margin-top: 25px; text-align: right; font-size: 12px; }
        .total-amount { font-size: 16px; font-weight: bold; color: #333; }
        .status-badge { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 10px; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        @php($logoData = \App\Models\Setting::getLogoDataUri())
        @if($logoData)
            <img src="{{ $logoData }}" alt="Logo" style="max-height: 50px; max-width: 200px; margin-bottom: 10px;">
        @endif
        <div class="invoice-title">Weekly Seller Invoice - {{ $invoice->seller }}</div>
        <div class="week-period">Week: {{ $invoice->week_period }}</div>
        <div class="invoice-date">Generated on: {{ date('d/m/Y H:i') }}</div>
        <div class="invoice-date">
            Status: 
            <span class="status-badge status-{{ $invoice->status }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
        @if($invoice->approved_at)
        <div class="invoice-date">Approved on: {{ $invoice->approved_at->format('d/m/Y H:i') }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Purchase Price</th>
                <th>Total Price</th>
                <th>Customer</th>
                <th>Zone</th>
                <th>Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->product?->name ?? 'N/A' }} (SKU: {{ $order->product?->sku ?? 'N/A' }})</td>
                <td style="text-align: center;">{{ $order->quantity }}</td>
                <td style="text-align: right;">{{ number_format($order->price / $order->quantity, 0, ',', ' ') }} FCFA</td>
                <td style="text-align: right;">
                    @if($order->product && $order->product->is_company_product)
                        {{ number_format($order->product->purchase_price, 0, ',', ' ') }} FCFA
                    @else
                        0 FCFA
                    @endif
                </td>
                <td style="text-align: right;">{{ number_format($order->price, 0, ',', ' ') }} FCFA</td>
                <td>{{ $order->client_name }}<br>{{ $order->client_phone }}</td>
                <td style="text-align: center;">{{ $order->zone ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ date('d/m/Y H:i', strtotime($order->updated_at)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div>Total Orders: {{ $totalOrders }}</div>
        <div>Total Products Price: {{ number_format($productTotal, 0, ',', ' ') }} FCFA</div>
        @if($purchasePriceTotal > 0)
        <div>Total Purchase Price (Company Products): -{{ number_format($purchasePriceTotal, 0, ',', ' ') }} FCFA</div>
        @endif
        <div>Total Delivery Cost: -{{ number_format($deliveryCostTotal, 0, ',', ' ') }} FCFA</div>
        <div class="total-amount">Net Total Amount: {{ number_format($totalAmount, 0, ',', ' ') }} FCFA</div>
        
        @if(isset($invoice->advances) && $invoice->advances->count() > 0)
        <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
            <strong>Advance Charges:</strong><br>
            @foreach($invoice->advances as $advance)
            <div style="margin-top: 5px;">
                <span style="color: #dc3545; font-weight: bold;">- {{ number_format($advance->amount, 0, ',', ' ') }} FCFA</span>
                <span style="font-size: 9px; color: #666;">({{ $advance->created_at->format('d/m/Y H:i') }})</span>
                @if($advance->note)
                <div style="margin-left: 15px; font-style: italic; font-size: 9px;">{{ $advance->note }}</div>
                @endif
            </div>
            @endforeach
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ccc;">
                <strong>Total Advances:</strong> <span style="color: #dc3545;">- {{ number_format($invoice->total_advances, 0, ',', ' ') }} FCFA</span>
            </div>
            <div style="margin-top: 10px; font-size: 14px; font-weight: bold; color: #007bff;">
                Final Amount Due: {{ number_format($totalAmount - $invoice->total_advances, 0, ',', ' ') }} FCFA
            </div>
        </div>
        @endif
    </div>

    @if($invoice->notes)
    <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007bff;">
        <strong>Notes:</strong><br>
        {{ $invoice->notes }}
    </div>
    @endif
</body>
</html>
