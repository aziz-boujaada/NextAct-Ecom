<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stock Alert</title>
</head>

<body style="margin:0; padding:0; background:#f6f8fb; font-family:Arial, sans-serif;">

    <div style="max-width:650px; margin:30px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">

        <!-- HEADER -->
        <div style="background:#1f2937; padding:20px; text-align:center;">
            <h1 style="color:#ffffff; margin:0; font-size:22px;">
                📦 NextAct ERP - Stock Alert
            </h1>
        </div>

        <!-- BODY -->
        <div style="padding:25px;">

            <p style="font-size:16px; color:#333;">
                Hello <strong>Admin</strong>,
            </p>

            <p style="font-size:15px; color:#555;">
                A product has reached a critical stock level and requires your attention.
            </p>

            <div style="margin:20px 0; padding:15px; border:1px solid #eee; border-radius:10px;">

                <p style="margin:6px 0;">
                    <strong>Product:</strong> {{ $product->name }}
                </p>

                <p style="margin:6px 0;">
                    <strong>Current Stock:</strong>
                    <span style="color:#e11d48; font-weight:bold;">
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
                        <span style="color:#dc2626; font-weight:bold;">OUT OF STOCK</span>
                    @else
                        <span style="color:#f59e0b; font-weight:bold;">LOW STOCK</span>
                    @endif
                </p>

            </div>

            <!-- CTA BUTTON -->
            <div style="text-align:center; margin:25px 0;">
                <a href="http://127.0.0.1:5173//products/{{ $product->id }}"
                   style="background:#2563eb; color:#fff; padding:12px 20px; text-decoration:none; border-radius:8px; display:inline-block; font-weight:bold;">
                    View Product
                </a>
            </div>

            <p style="font-size:13px; color:#888; text-align:center;">
                Please take action before it affects sales operations.
            </p>

        </div>

        <!-- FOOTER -->
        <div style="background:#f1f5f9; padding:12px; text-align:center; font-size:12px; color:#777;">
            © {{ date('Y') }} NextAct ERP - All rights reserved
        </div>

    </div>

</body>
</html>