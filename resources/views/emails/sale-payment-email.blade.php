<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Payment Method</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:40px 20px;">
        <tr>
            <td align="center">

                <table width="650" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #e2e8f0;">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#2563eb,#7c3aed);padding:35px;text-align:center;color:white;">

                            <h1 style="margin:0;font-size:30px;font-weight:700;">
                                Payment Required
                            </h1>

                            <p style="margin-top:10px;font-size:14px;opacity:.9;">
                                Choose your preferred payment method
                            </p>

                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">

                            <h2 style="margin-top:0;color:#0f172a;font-size:22px;">
                                Hello {{ $client->name }},
                            </h2>

                            <p style="color:#475569;font-size:15px;line-height:1.7;">
                                Your sale has been prepared successfully.
                                Please review the payment details below and choose
                                your preferred payment method.
                            </p>

                            <!-- Sale Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:28px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:20px;">

                                <tr>
                                    <td style="padding:10px 0;color:#64748b;">
                                        Sale Reference
                                    </td>

                                    <td align="right"
                                        style="padding:10px 0;font-weight:600;color:#0f172a;">
                                        {{ $sale->reference }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px 0;color:#64748b;">
                                        Total Amount
                                    </td>

                                    <td align="right"
                                        style="padding:10px 0;font-size:22px;font-weight:700;color:#2563eb;">
                                        {{ number_format($sale->total,2) }} DH
                                    </td>
                                </tr>

                            </table>

                            <!-- Payment Methods -->
                            <div style="margin-top:35px;">

                                <h3 style="color:#0f172a;margin-bottom:18px;">
                                    Choose Payment Method
                                </h3>

                                <!-- Cash -->
                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #e2e8f0;border-radius:18px;background:#f8fafc;margin-bottom:18px;padding:20px;">
                                    <tr>
                                        <td>

                                            <h4 style="margin:0 0 10px;color:#111827;">
                                                Cash Payment
                                            </h4>

                                            <p style="margin:0;color:#64748b;line-height:1.6;font-size:14px;">
                                                Select cash payment and our team will
                                                review and confirm your payment manually.
                                            </p>

                                            <div style="margin-top:18px;">
                                                <a href="{{ url('/payments/cash/'.$sale->id) }}"
                                                    style="display:inline-block;padding:12px 22px;background:#111827;color:#fff;text-decoration:none;border-radius:12px;font-size:14px;font-weight:600;">
                                                    Choose Cash
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                </table>

                                <!-- Stripe -->
                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #dbeafe;border-radius:18px;background:#eff6ff;padding:20px;">
                                    <tr>
                                        <td>

                                            <h4 style="margin:0 0 10px;color:#1d4ed8;">
                                                Online Payment (Stripe)
                                            </h4>

                                            <p style="margin:0;color:#475569;line-height:1.6;font-size:14px;">
                                                Pay securely online using your bank card.
                                                Your payment will be processed instantly.
                                            </p>

                                            <div style="margin-top:18px;">
<!--{{ url('/payments/stripe/'.$sale->id) }}-->
                                                <a href="#"
                                                    style="display:inline-block;padding:12px 22px;background:#2563eb;color:#fff;text-decoration:none;border-radius:12px;font-size:14px;font-weight:600;">
                                                    Pay Now
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                </table>

                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding:28px;text-align:center;border-top:1px solid #e2e8f0;color:#94a3b8;font-size:12px;">

                            Thank you for choosing us.<br>
                            This email was generated automatically.

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>