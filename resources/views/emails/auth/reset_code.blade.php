<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset – Campus Buddy</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                <!-- Header -->
                <tr>
                    <td align="center" style="padding-bottom:24px;">
                        <img src="{{ $message->embed(public_path('images/menuicons/Buddy.png')) }}" alt="Campus Buddy Logo" style="width: 70px; height: auto; display: block; margin: 0 auto 8px;">
                        <span style="font-size:24px;font-weight:800;color:#1e3a5f;letter-spacing:-0.5px;display:block;">
                            Campus Buddy
                        </span>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                        <!-- Gradient top bar -->
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="height:6px;background:linear-gradient(90deg,#1e3a5f,#3b82f6,#06b6d4);"></td>
                            </tr>
                        </table>

                        <!-- Body -->
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:40px 48px 32px;">

                                    <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#1e293b;">
                                        Password Reset Request
                                    </p>
                                    <p style="margin:0 0 28px;font-size:15px;color:#64748b;line-height:1.6;">
                                        Hi there! We received a request to reset your Campus Buddy account password.
                                        Use the code below — it expires in <strong>15 minutes</strong>.
                                    </p>

                                    <!-- Code Box -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="padding:28px 0;">
                                                <div style="display:inline-block;">
                                                    <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;text-align:center;">
                                                        Your Reset Code
                                                    </p>
                                                    <div style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);border-radius:12px;padding:22px 48px;text-align:center;">
                                                        <span style="font-size:42px;font-weight:800;color:#ffffff;letter-spacing:10px;font-family:'Courier New',monospace;">
                                                            {{ $code }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Warning -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
                                                <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">
                                                    ⚠️ <strong>Did not request this?</strong> Your account is safe — simply ignore this email. No changes have been made.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin:24px 0 0;font-size:14px;color:#94a3b8;line-height:1.6;border-top:1px solid #f1f5f9;padding-top:24px;">
                                        This code is valid for a single use only and will expire in 15 minutes.<br>
                                        Never share this code with anyone.
                                    </p>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding:28px 0 0;">
                        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.8;">
                            © {{ date('Y') }} Campus Buddy · Daffodil International University<br>
                            This is an automated message — please do not reply.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
