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
            margin: 0;
            padding: 30px;
            background: #f4f7fb;
            color: #1e293b;
            font-size: 12px;
        }

        .page {
            background: #fff;
            border-radius: 20px;
            padding: 35px;
            border: 1px solid #e2e8f0;
        }

        /* HEADER */

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-wrap {
            width: 100%;
        }

        .logo-box {
            width: 75px;
            vertical-align: top;
        }

        .logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .company-meta {
            color: #64748b;
            font-size: 11px;
            line-height: 1.7;
        }

        .invoice-box {
            text-align: right;
        }

        .invoice-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 30px;
            background: #dbeafe;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 34px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .invoice-number {
            font-size: 14px;
            font-weight: 600;
            margin-top: 6px;
        }

        .muted {
            color: #64748b;
        }

        /* PANELS */

        .meta {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px;
            margin-bottom: 28px;
        }

        .panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            min-height: 110px;
        }

        .panel-title {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ITEMS */

        .items {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            border-radius: 16px;
        }

        .items thead th {
            background: #2563eb;
            color: white;
            padding: 14px 12px;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }

        .items tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #eef2f7;
        }

        .items tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .items tbody tr:last-child td {
            border-bottom: none;
        }

        .num {
            text-align: right;
        }

        /* TOTALS */

        .totals-box {
            margin-top: 24px;
            width: 360px;
            margin-left: auto;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
        }

        .totals {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 0;
        }

        .totals .label {
            color: #64748b;
        }

        .totals .value {
            text-align: right;
            font-weight: 600;
        }

        .grand-total td {
            border-top: 1px solid #dbe3ee;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #2563eb;
        }

        /* FOOTER */

        .footer {
            margin-top: 35px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #e2e8f0;
            padding-top: 18px;
        }
    </style>
</head>

<body>

<div class="page">

    <!-- HEADER -->
    <div class="header">
        <table class="header-table">
            <tr>

                <!-- COMPANY -->
                <td width="60%">

                    <table class="company-wrap">
                        <tr>

                            <td class="logo-box">
                                <img
                                    class="logo"
                                    src="{{ public_path('storage/logos/98ffky3eijvVv7xRRLvhCjJA0KQUc9Unfzcf4arZ.png') }}"
                                    alt="logo">
                            </td>

                            <td>

                                <div class="company-name">
                                    {{ $companyInfo->company_name }}
                                </div>

                                <div class="subtitle">
                                    Invoice for completed sale
                                </div>

                                <div class="company-meta">
                                    {{ $companyInfo->company_email }}<br>
                                    {{ $companyInfo->company_phone }}<br>
                                    {{ $companyInfo->company_website }}<br>
                                    {{ $companyInfo->company_address }}
                                </div>

                            </td>

                        </tr>
                    </table>

                </td>

                <!-- INVOICE INFO -->
                <td width="40%" class="invoice-box">

                    <div class="invoice-badge">
                        COMPLETED SALE
                    </div>

                    <div class="invoice-title">
                        INVOICE
                    </div>

                    <div class="invoice-number">
                        {{ $invoice->invoice_number }}
                    </div>

                    <div class="muted">
                        Sale reference:
                        {{ $sale->reference }}
                    </div>

                </td>
            </tr>
        </table>
    </div>

    <!-- CLIENT + DETAILS -->
    <table class="meta">
        <tr>

            <td width="55%">
                <div class="panel">

                    <div class="panel-title">
                        Bill To
                    </div>

                    <strong>
                        {{ $sale->client?->name ?? 'Walk-in Client' }}
                    </strong><br>

                    @if($sale->client?->phone)
                        {{ $sale->client->phone }}<br>
                    @endif

                    @if($sale->client?->address)
                        {{ $sale->client->address }}
                    @endif

                </div>
            </td>

            <td width="45%">
                <div class="panel">

                    <div class="panel-title">
                        Invoice Details
                    </div>

                    Date:
                    {{ $sale->created_at?->format('M d, Y') }}
                    <br>

                    Status:
                    {{ ucfirst($sale->status) }}
                    <br>

                    Total:
                    {{ number_format((float) $invoice->total, 2) }} DH

                </div>
            </td>

        </tr>
    </table>

    <!-- ITEMS -->
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

                <td>
                    {{ $item->product?->name ?? 'Product' }}
                </td>

                <td>
                    {{ $item->product?->reference ?? 'N/A' }}
                </td>

                <td class="num">
                    {{ $item->quantity - $item->refund_quantity}}
                </td>

                <td class="num">
                    {{ number_format((float) $item->price, 2) }}
                </td>

                <td class="num">
                    {{ number_format((float) $item->total - $item->refund_total, 2) }}
                </td>

            </tr>
            @endforeach

        </tbody>
    </table>

    <!-- TOTALS -->
    <div class="totals-box">

        <table class="totals">

            <tr>
                <td class="label">Tax</td>
                <td class="value">
                    {{ number_format((float) $sale->tax_rate ,2)}}%
                </td>
            </tr>

            <tr>
                <td class="label">Tax Amount</td>
                <td class="value">
                    {{ number_format((float) $sale->tax_rate ,2) }} DH
                </td>
            </tr>

            <tr>
                <td class="label">Discount</td>
                <td class="value">
                    {{ number_format((float) $sale->discount_amount ,2)}} DH
                </td>
            </tr>

            <tr class="grand-total">
                <td>Grand Total</td>
                <td class="value">
                    {{ number_format((float) $invoice->total, 2) }} DH
                </td>
            </tr>

        </table>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        Thank you for your business.
    </div>

</div>

</body>
</html>