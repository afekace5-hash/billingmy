<?php

/**
 * PUBLIC ISOLIR PAGE
 * Halaman yang ditampilkan ke pelanggan yang diisolir
 * Bisa di-akses dari: https://isolir.kimonet.my.id/
 * atau dari IP lokal MikroTik
 */

// Set proper headers
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Frame-Options: SAMEORIGIN');

// Get customer info from query string or MikroTik redirect
$pppoeUsername = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';
$redirectUrl = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '';
$customMessage = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';

// Tentukan warna dan pesan berdasarkan jenis isolir
$isolirType = 'overdue'; // default
if (isset($_GET['type'])) {
    $isolirType = htmlspecialchars($_GET['type']);
}

$titleColor = '#dc3545'; // Red for overdue
$titleText = 'Layanan Terputus';
$descText = 'Layanan PPPoE Anda telah diputus karena pembayaran tagihan belum diterima.';

if ($isolirType === 'maintenance') {
    $titleColor = '#ff9800'; // Orange for maintenance
    $titleText = 'Sedang Dalam Perbaikan';
    $descText = 'Jaringan Anda sedang kami lakukan perbaikan dan perawatan sistem.';
} else if ($isolirType === 'admin') {
    $titleColor = '#dc3545'; // Red for admin action
    $titleText = 'Akses Dibatasi';
    $descText = 'Layanan Anda telah dibatasi atas permintaan administrator.';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $titleText ?> - Kimonet ISP</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            margin-bottom: 30px;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.1);
            color: <?= $titleColor ?>;
            font-size: 48px;
        }

        .icon.maintenance {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        h1 {
            color: <?= $titleColor ?>;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .description {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .info-box .label {
            color: #666;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .info-box .value {
            color: #333;
            font-size: 15px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        .info-box.error {
            background: #fff5f5;
            border-color: #ffebee;
        }

        .steps {
            background: #f0f7ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .steps h3 {
            color: #333;
            font-size: 15px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .steps ol {
            list-style-position: inside;
            color: #555;
            font-size: 14px;
            line-height: 1.8;
        }

        .steps li {
            margin-bottom: 10px;
        }

        .contact-info {
            background: #f0f7ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }

        .contact-info h4 {
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .contact-item {
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .contact-item strong {
            min-width: 60px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .footer {
            color: #999;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-link {
            color: #667eea;
            text-decoration: none;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        .timer {
            text-align: center;
            color: #dc3545;
            font-size: 12px;
            margin-top: 15px;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .steps ol {
                margin-left: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="icon <?= ($isolirType === 'maintenance') ? 'maintenance' : '' ?>">
                <?php if ($isolirType === 'maintenance'): ?>
                    ⚙️
                <?php else: ?>
                    🔒
                <?php endif; ?>
            </div>
            <h1><?= $titleText ?></h1>
        </div>

        <p class="description">
            <?= $descText ?>
        </p>

        <?php if ($pppoeUsername): ?>
            <div class="info-box error">
                <span class="label">Username PPPoE Anda:</span>
                <span class="value"><?= $pppoeUsername ?></span>
            </div>
        <?php endif; ?>

        <?php if ($isolirType === 'overdue'): ?>
            <div class="steps">
                <h3>📋 Langkah-Langkah untuk Mengaktifkan Kembali:</h3>
                <ol>
                    <li><strong>Periksa tagihan Anda</strong> di panel pelanggan</li>
                    <li><strong>Lakukan pembayaran</strong> sesuai dengan jumlah yang tertera</li>
                    <li><strong>Konfirmasi pembayaran</strong> melalui WhatsApp atau panel pelanggan</li>
                    <li><strong>Tunggu verifikasi</strong> hingga 10 menit untuk koneksi aktif kembali</li>
                </ol>
            </div>

            <div class="contact-info">
                <h4>💬 Hubungi Kami:</h4>
                <div class="contact-item">
                    <strong>WhatsApp:</strong>
                    <a href="https://wa.me/62895383112127" style="color: #667eea; text-decoration: none;">+62 895 383 112 127</a>
                </div>
                <div class="contact-item">
                    <strong>Telepon:</strong> <span>+62-274-585-4070</span>
                </div>
                <div class="contact-item">
                    <strong>Email:</strong> <span>support@kimonet.my.id</span>
                </div>
            </div>

            <div class="actions">
                <a href="https://billing.kimonet.my.id" target="_blank" class="btn btn-primary">
                    📊 Panel Pelanggan
                </a>
                <a href="https://wa.me/62895383112127" target="_blank" class="btn btn-secondary">
                    💬 Chat WhatsApp
                </a>
            </div>

        <?php elseif ($isolirType === 'maintenance'): ?>
            <div class="contact-info">
                <h4>⏱️ Estimasi Waktu:</h4>
                <p style="color: #666; margin: 10px 0;">Kami sedang bekerja untuk menyelesaikan perbaikan. Biasanya selesai dalam waktu <strong>1-2 jam</strong>.</p>
            </div>

            <div class="actions">
                <a href="https://billing.kimonet.my.id" target="_blank" class="btn btn-primary">
                    📊 Panel Pelanggan
                </a>
            </div>

        <?php elseif ($isolirType === 'admin'): ?>
            <div class="contact-info">
                <h4>❓ Ada Pertanyaan?</h4>
                <p style="color: #666; margin: 10px 0;">Silakan hubungi tim support kami untuk informasi lebih lanjut mengenai status koneksi Anda.</p>
            </div>

            <div class="actions">
                <a href="https://wa.me/62895383112127" target="_blank" class="btn btn-primary">
                    💬 Chat Support
                </a>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p><strong>Kimonet Internet Service Provider</strong></p>
            <p>© 2026 PT Kimo Sukses Bersama. All rights reserved.</p>
            <p style="margin-top: 10px;">
                <a href="https://kimonet.my.id" class="footer-link">Visit Website</a> |
                <a href="https://billing.kimonet.my.id" class="footer-link">Customer Portal</a>
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds to check if connection is restored
        <?php if ($isolirType === 'overdue'): ?>
            setTimeout(function() {
                // Try to fetch a simple resource to check internet connectivity
                fetch('https://www.google.com/favicon.ico', {
                    method: 'HEAD',
                    cache: 'no-store'
                }).then(function() {
                    // If successful, connection is restored
                    window.location.reload();
                }).catch(function() {
                    // Connection still blocked, reload page to check message again
                    setTimeout(arguments.callee, 30000);
                });
            }, 30000);
        <?php endif; ?>
    </script>
</body>

</html>