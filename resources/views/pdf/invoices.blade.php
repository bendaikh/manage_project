<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 15px; }
        .page-break { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .invoice-header { margin-bottom: 15px; }
        .total-amount { font-weight: bold; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
    @foreach($orders as $o)
        <div class="invoice-header">
            <h2>Invoice #{{ $o->id }}</h2>
            <p>Date: {{ $o->created_at->format('d/m/Y') }}</p>
            <p>Seller: {{ $o->seller }}</p>
            <p>Client: {{ $o->client_name }} - {{ $o->client_address }} - {{ $o->client_phone }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $o->product?->name ?? 'N/A' }}</td>
                    <td>{{ $o->quantity }}</td>
                    <td>{{ number_format($o->price / $o->quantity, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($o->price, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
        <p class="total-amount">Total: {{ number_format($o->price, 0, ',', ' ') }} FCFA</p>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html> 