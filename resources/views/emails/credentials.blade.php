<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Login JMN Matrix</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); padding: 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.75); font-size: 13px; margin: 8px 0 0; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
        .body { padding: 40px; }
        .greeting { font-size: 16px; color: #1e293b; font-weight: 700; margin-bottom: 12px; }
        .description { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 24px; }
        .credentials-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin: 24px 0; }
        .cred-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-b: 1px solid #f1f5f9; }
        .cred-item:last-child { margin-bottom: 0; padding-bottom: 0; border-b: none; }
        .cred-label { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .cred-value { font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: 700; color: #0f172a; word-break: break-all; }
        .btn-container { text-align: center; margin: 32px 0 16px 0; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; padding: 14px 32px; font-weight: 700; border-radius: 12px; font-size: 14px; box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: all 0.2s ease; }
        .btn:hover { background: #1d4ed8; box-shadow: 0 6px 16px rgba(37,99,235,0.3); }
        .warning { background: #fef2f2; border: 1px solid #fee2e2; border-radius: 16px; padding: 20px; font-size: 13px; color: #991b1b; line-height: 1.6; margin-top: 24px; }
        .warning p { margin: 0; }
        .warning strong { color: #7f1d1d; }
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
                Akun login Anda untuk mengakses sistem absensi **JMN Matrix** telah berhasil disiapkan/diperbarui oleh Admin. Berikut adalah detail kredensial masuk Anda:
            </p>
            <div class="credentials-box">
                <div class="cred-item">
                    <span class="cred-label">Username :</span>
                    <span class="cred-value">{{ $username }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Kata Sandi :</span>
                    <span class="cred-value" style="background: #e2e8f0; padding: 3px 8px; border-radius: 6px; font-weight: bold; letter-spacing: 0.5px;">{{ $password }}</span>
                </div>
            </div>
            
            <div class="btn-container">
                <a href="https://absensi.recordshoes.com/login" class="btn">Masuk ke Aplikasi</a>
            </div>

            <div class="warning">
                <p>⚠️ <strong>PENTING:</strong> Jangan membagikan password pada siapapun.</p>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem JMN Matrix.<br>© {{ date('Y') }} JMN Matrix. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
