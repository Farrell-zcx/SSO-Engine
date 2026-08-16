<?php
/**
 * @var string $username
 * @var string $activationLink
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Aktivasi Akun SSO Engine</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f7f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #2563eb; padding: 30px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">SSO Engine</h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333333;">
                            <h2 style="margin-top: 0; font-size: 20px; font-weight: 600; color: #111827;">Halo <?= esc($username) ?>,</h2>
                            <p style="font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 25px;">
                                Akun SSO Anda telah berhasil dibuat oleh Administrator. Silakan klik tombol di bawah ini untuk mengaktifkan akun dan mengatur password Anda.
                            </p>
                            
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="<?= esc($activationLink) ?>" style="background-color: #2563eb; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block;">Aktifkan Akun</a>
                            </div>
                            
                            <p style="font-size: 14px; line-height: 1.5; color: #6b7280; margin-bottom: 10px;">
                                Atau, salin dan tempel tautan berikut di browser Anda:
                            </p>
                            <p style="font-size: 13px; color: #2563eb; word-break: break-all; margin-top: 0; background-color: #f3f4f6; padding: 10px; border-radius: 4px;">
                                <?= esc($activationLink) ?>
                            </p>
                            
                            <p style="font-size: 14px; color: #dc2626; margin-top: 30px; font-weight: 500;">
                                ⚠️ Link aktivasi ini hanya berlaku selama 10 menit.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f9fafb; padding: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                                &copy; <?= date('Y') ?> SSO Engine System. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
