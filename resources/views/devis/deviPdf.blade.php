<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $devis->reference }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            padding: 30px;
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .company,
        .devis-info {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .company h2 {
            color: #111827;
            margin-bottom: 8px;
            font-size: 22px;
        }

        .muted {
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }

        .accepted {
            background: #16a34a;
        }

        .pending {
            background: #f59e0b;
        }

        .rejected {
            background: #dc2626;
        }

        .section {
            margin-top: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f3f4f6;
        }

        table th {
            text-align: left;
            padding: 10px;
            font-size: 11px;
            border: 1px solid #e5e7eb;
        }

        table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 320px;
            margin-left: auto;
            margin-top: 20px;
        }

        .totals td {
            padding: 8px;
        }

        .grand-total {
            font-size: 15px;
            font-weight: bold;
            background: #f3f4f6;
        }

        .footer {
            margin-top: 45px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="company">
            <h2>{{ config('app.name') }}</h2>

            <div class="muted">
                Commercial Quotation / Devis
            </div>
        </div>

        <div class="devis-info text-right">
            <h2>DEVIS</h2>

            <p>
                <strong>Reference:</strong>
                {{ $devis->reference }}
            </p>

            <p>
                <strong>Date:</strong>
                {{ \Carbon\Carbon::parse($devis->created_at)->format('d/m/Y') }}
            </p>

            <p>
                <strong>Expiration:</strong>
                {{ $devis->expires_at
                    ? \Carbon\Carbon::parse($devis->expires_at)->format('d/m/Y')
                    : '-' }}
            </p>

            <p style="margin-top:8px;">
                <span class="badge {{ $devis->status }}">
                    {{ ucfirst($devis->status) }}
                </span>
            </p>
        </div>
    </div>

    <!-- CLIENT -->
    <div class="section">
        <div class="section-title">Client Information</div>

        <p>
            <strong>Name:</strong>
            {{ $devis->client->name ?? '-' }}
        </p>

        <p>
            <strong>Phone:</strong>
            {{ $devis->client->phone ?? '-' }}
        </p>

        <p>
            <strong>Address:</strong>
            {{ $devis->client->address ?? '-' }}
        </p>
    </div>

    <!-- ITEMS -->
    <div class="section">
        <div class="section-title">Products / Services</div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Product</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($devis->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            {{ $item->product->reference ?? '-' }}
                        </td>

                        <td>
                            {{ $item->product->name ?? '-' }}
                        </td>

                        <td class="text-right">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            {{ number_format($item->price, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($item->total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- TOTALS -->
    <table class="totals">
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="text-right">
                {{ number_format($devis->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td><strong>Discount</strong></td>
            <td class="text-right">
                - {{ number_format($devis->discount, 2) }}
            </td>
        </tr>

        <tr>
            <td><strong>Tax</strong></td>
            <td class="text-right">
                + {{ number_format($devis->tax, 2) }}
            </td>
        </tr>

        <tr class="grand-total">
            <td>TOTAL</td>
            <td class="text-right">
                {{ number_format($devis->total, 2) }}
            </td>
        </tr>
    </table>

    <!-- NOTES -->
    @if($devis->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <p>{{ $devis->notes }}</p>
        </div>
    @endif

    <!-- CREATED BY -->
    <div class="section">
        <div class="section-title">Generated By</div>

        <p>
            {{ $devis->createdBy->name ?? '-' }}
            ({{ $devis->createdBy->email ?? '-' }})
        </p>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Generated on
        {{ now()->format('d/m/Y H:i') }}
        • {{ config('app.name') }}
    </div>

</body>
</html>