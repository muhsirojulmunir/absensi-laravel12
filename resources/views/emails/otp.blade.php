<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); padding: 40px; text-align: center; }
        .header img { height: 50px; width: auto; object-fit: contain; margin-bottom: 16px; display: block; margin-left: auto; margin-right: auto; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.7); font-size: 13px; margin: 8px 0 0; }
        .body { padding: 40px; }
        .greeting { font-size: 15px; color: #334155; font-weight: 600; margin-bottom: 16px; }
        .description { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 32px; }
        .otp-box { background: #f8fafc; border: 2px dashed #3b82f6; border-radius: 16px; padding: 30px; text-align: center; margin: 24px 0; }
        .otp-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }
        .otp-code { font-size: 52px; font-weight: 900; color: #1e40af; letter-spacing: 16px; line-height: 1; }
        .expires { font-size: 12px; color: #f59e0b; font-weight: 600; margin-top: 12px; }
        .warning { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; padding: 16px; font-size: 13px; color: #92400e; line-height: 1.6; margin-top: 24px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; }
        .footer p { font-size: 11px; color: #94a3b8; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>JMN Matrix</h1>
            <p>Sistem Absensi Karyawan</p>
        </div>
        <div class="body">
            <p class="greeting">Halo, {{ $name }}!</p>
            <p class="description">
                Kami menerima permintaan untuk mereset kata sandi akun Anda. Gunakan kode OTP berikut untuk melanjutkan proses reset kata sandi:
            </p>
            <div class="otp-box">
                <p class="otp-label">Kode Verifikasi OTP</p>
                <div class="otp-code">{{ $otp }}</div>
                <p class="expires">⏰ Berlaku selama 15 menit</p>
            </div>
            <div class="warning">
                ⚠️ <strong>Jangan bagikan kode ini kepada siapapun.</strong> Tim JMN Matrix tidak akan pernah meminta kode OTP Anda. Jika Anda tidak merasa meminta reset password, abaikan email ini dan akun Anda akan tetap aman.
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem JMN Matrix.<br>© {{ date('Y') }} JMN Matrix. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
