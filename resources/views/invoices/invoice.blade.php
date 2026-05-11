<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }

        .page {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .muted {
            color: #6b7280;
        }

        .invoice-box {
            text-align: right;
        }

        .invoice-box h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .meta,
        .totals {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            vertical-align: top;
            padding: 0 0 18px;
        }

        .panel {
            background: #f9fafb;
            border-radius: 10px;
            padding: 14px 16px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items th,
        .items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 10px;
            text-align: left;
        }

        .items th {
            background: #111827;
            color: #fff;
            font-weight: 600;
        }

        .items td:last-child,
        .items th:last-child,
        .items td.num {
            text-align: right;
        }

        .totals {
            margin-top: 18px;
        }

        .totals td {
            padding: 6px 0;
        }

        .totals .label {
            text-align: right;
            color: #6b7280;
            padding-right: 12px;
        }

        .totals .value {
            text-align: right;
            width: 140px;
            font-weight: 700;
        }

        .footer {
            margin-top: 28px;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <div class="brand">NextAct E-Commerce</div>
                <div class="muted">Invoice for completed sale</div>
            </div>

            <div class="invoice-box">
                <h1>Invoice</h1>
                <div><strong>{{ $invoice->invoice_number }}</strong></div>
                <div class="muted">Sale reference: {{ $sale->reference }}</div>
            </div>
        </div>

        <table class="meta">
            <tr>
                <td style="width: 55%;">
                    <div class="panel">
                        <strong>Bill To</strong><br>
                        {{ $sale->client?->name ?? 'Walk-in Client' }}<br>
                        @if($sale->client?->phone)
                            {{ $sale->client->phone }}<br>
                        @endif
                        @if($sale->client?->address)
                            {{ $sale->client->address }}
                        @endif
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="panel">
                        <strong>Invoice Details</strong><br>
                        Date: {{ $sale->created_at?->format('M d, Y') }}<br>
                        Status: {{ ucfirst($sale->status) }}<br>
                        Total: {{ number_format((float) $invoice->total, 2) }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Reference</th>
                    <th class="num">Qty</th>
                    <th class="num">Price</th>
                    <th class="num">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'Product' }}</td>
                        <td>{{ $item->product?->reference ?? 'N/A' }}</td>
                        <td class="num">{{ $item->quantity }}</td>
                        <td class="num">{{ number_format((float) $item->price, 2) }}</td>
                        <td class="num">{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Grand Total</td>
                <td class="value">{{ number_format((float) $invoice->total, 2) }}</td>
            </tr>
        </table>

        <div class="footer">
            Thank you for your business.
        </div>
    </div>
</body>
</html>