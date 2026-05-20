<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Stock Alert</title>
</head>

<body style="margin:0; padding:0; background:#f4f3ff; font-family:Arial, sans-serif;">

    <div style="max-width:650px; margin:30px auto; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.08);">

        <!-- HEADER GRADIENT -->
        <div style="
        background: linear-gradient(135deg, #6d28d9, #a855f7, #ec4899);
        padding:28px;
        text-align:center;
    ">
            <h1 style="color:#fff; margin:0; font-size:22px; letter-spacing:0.5px;">
                📦 NextAct ERP
            </h1>

            <p style="color:#f3e8ff; margin:6px 0 0; font-size:14px;">
                Smart Inventory Alert System
            </p>
        </div>

        <!-- BODY -->
        <div style="padding:28px;">

            <p style="font-size:16px; color:#111;">
                Hello <strong>Admin</strong>,
            </p>

            <p style="font-size:14px; color:#555; line-height:1.6;">
                A product has reached a critical stock level and needs your attention immediately.
            </p>

            <!-- CARD -->
            <div style="
            margin-top:20px;
            padding:18px;
            border-radius:12px;
            background:#f9f5ff;
            border:1px solid #e9d5ff;
        ">

                <p style="margin:6px 0;">
                    <strong>Product:</strong> {{ $product->name }}
                </p>

                <p style="margin:6px 0;">
                    <strong>Current Stock:</strong>
                    <span style="color:#dc2626; font-weight:bold;">
                        {{ $product->stock }}
                    </span>
                </p>

                <p style="margin:6px 0;">
                    <strong>Alert Threshold:</strong>
                    {{ $product->alert_stock }}
                </p>

                <p style="margin:6px 0;">
                    <strong>Status:</strong>

                    @if($product->stock == 0)
                    <span style="color:#ef4444; font-weight:bold;">
                        OUT OF STOCK
                    </span>
                    @else
                    <span style="color:#f59e0b; font-weight:bold;">
                        LOW STOCK
                    </span>
                    @endif
                </p>

            </div>

            <!-- CTA -->
            <div style="text-align:center; margin:30px 0;">
                <a href="http://127.0.0.1:5173/products/{{ $product->id }}"
                    style="
                    background: linear-gradient(135deg, #7c3aed, #a855f7);
                    color:#fff;
                    padding:12px 22px;
                    text-decoration:none;
                    border-radius:10px;
                    display:inline-block;
                    font-weight:bold;
                    box-shadow:0 6px 15px rgba(124,58,237,0.3);
               ">
                    View Product
                </a>
            </div>

            <p style="font-size:12px; color:#888; text-align:center;">
                Please take action before it affects sales operations.
            </p>

        </div>

        <!-- FOOTER -->
        <div style="
        background:#f5f3ff;
        padding:14px;
        text-align:center;
        font-size:12px;
        color:#6b7280;
    ">
            © {{ date('Y') }} NextAct ERP - All rights reserved
        </div>

    </div>

</body>

</html>