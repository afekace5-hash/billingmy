<!DOCTYPE html>
<html>

<head>
    <title>Flip Auto-Configuration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .form-group {
            margin: 15px 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }

        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }

        .section {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }

        h1,
        h2 {
            color: #333;
        }

        .step {
            background: #e7f3ff;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Flip Payment Gateway - Auto Configuration</h1>

        <?php
        // Load CodeIgniter
        require_once __DIR__ . '/../vendor/autoload.php';

        // Bootstrap CodeIgniter properly
        $pathsPath = realpath(__DIR__ . '/../app/Config/Paths.php');
        $paths = require $pathsPath;
        $bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
        $app = require $bootstrap;

        // Now we can use CodeIgniter classes
        $db = \Config\Database::connect();

        // Check if form submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $secretKey = $_POST['secret_key'] ?? '';
            $validationToken = $_POST['validation_token'] ?? '';
            $environment = $_POST['environment'] ?? 'production';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            // Admin fees
            $adminFees = [
                'flip_va' => (int) ($_POST['admin_fee_va'] ?? 4000),
                'flip_qris' => (int) ($_POST['admin_fee_qris'] ?? 0),
                'flip_ewallet' => (int) ($_POST['admin_fee_ewallet'] ?? 0),
                'flip_retail' => (int) ($_POST['admin_fee_retail'] ?? 2500),
            ];

            if (!empty($secretKey) && !empty($validationToken)) {
                try {
                    // Check if Flip config already exists
                    $existing = $db->table('payment_gateways')
                        ->where('gateway_type', 'flip')
                        ->get()
                        ->getRowArray();

                    $data = [
                        'gateway_name' => 'Flip',
                        'gateway_type' => 'flip',
                        'is_active' => $isActive,
                        'api_key' => $secretKey,
                        'api_secret' => $validationToken,
                        'environment' => $environment,
                        'admin_fees' => json_encode($adminFees),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($existing) {
                        // Update existing
                        $db->table('payment_gateways')
                            ->where('id', $existing['id'])
                            ->update($data);
                        echo '<div class="success">✅ Konfigurasi Flip berhasil diupdate!</div>';
                    } else {
                        // Insert new
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $db->table('payment_gateways')->insert($data);
                        echo '<div class="success">✅ Konfigurasi Flip berhasil ditambahkan!</div>';
                    }

                    // Test connection
                    echo '<div class="section">';
                    echo '<h3>🔍 Test Koneksi ke Flip API</h3>';

                    try {
                        $flipService = new \App\Libraries\Payment\FlipService($data);
                        $testResult = $flipService->testConnection();

                        if ($testResult['success']) {
                            echo '<div class="success">✅ Koneksi ke Flip API berhasil!</div>';
                            echo '<pre>' . json_encode($testResult, JSON_PRETTY_PRINT) . '</pre>';
                        } else {
                            echo '<div class="error">❌ Koneksi gagal: ' . $testResult['message'] . '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Error testing connection: ' . $e->getMessage() . '</div>';
                    }

                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="error">❌ Secret Key dan Validation Token harus diisi!</div>';
            }
        }

        // Get current config
        $currentConfig = $db->table('payment_gateways')
            ->where('gateway_type', 'flip')
            ->get()
            ->getRowArray();

        $adminFees = [];
        if ($currentConfig && !empty($currentConfig['admin_fees'])) {
            $adminFees = json_decode($currentConfig['admin_fees'], true);
        }
        ?>

        <div class="section">
            <h2>📋 Langkah Setup</h2>
            <div class="step">
                <strong>1.</strong> Login ke dashboard Flip dan dapatkan kredensial API
            </div>
            <div class="step">
                <strong>2.</strong> Isi form di bawah dengan Secret Key dan Validation Token
            </div>
            <div class="step">
                <strong>3.</strong> Setup callback URL di dashboard Flip:
                <br><code><?php echo base_url('payment/callback/flip'); ?></code>
            </div>
            <div class="step">
                <strong>4.</strong> Test koneksi dan verifikasi konfigurasi
            </div>
        </div>

        <div class="section">
            <h2>⚙️ Konfigurasi Flip</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Secret Key:</label>
                    <input type="password" name="secret_key"
                        value="<?php echo $currentConfig['api_key'] ?? ''; ?>"
                        placeholder="Masukkan Secret Key dari dashboard Flip" required>
                    <small>Secret Key digunakan untuk autentikasi API</small>
                </div>

                <div class="form-group">
                    <label>Validation Token:</label>
                    <input type="text" name="validation_token"
                        value="<?php echo $currentConfig['api_secret'] ?? ''; ?>"
                        placeholder="Masukkan Validation Token untuk callback" required>
                    <small>Validation Token untuk verifikasi callback dari Flip</small>
                </div>

                <div class="form-group">
                    <label>Environment:</label>
                    <select name="environment">
                        <option value="production" <?php echo ($currentConfig['environment'] ?? '') === 'production' ? 'selected' : ''; ?>>
                            Production
                        </option>
                        <option value="sandbox" <?php echo ($currentConfig['environment'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>
                            Sandbox (Development)
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active"
                            <?php echo ($currentConfig['is_active'] ?? 0) ? 'checked' : ''; ?>>
                        Aktifkan Flip
                    </label>
                </div>

                <h3>💰 Biaya Admin per Metode</h3>

                <div class="form-group">
                    <label>Virtual Account:</label>
                    <input type="number" name="admin_fee_va"
                        value="<?php echo $adminFees['flip_va'] ?? 4000; ?>"
                        placeholder="4000">
                </div>

                <div class="form-group">
                    <label>QRIS:</label>
                    <input type="number" name="admin_fee_qris"
                        value="<?php echo $adminFees['flip_qris'] ?? 0; ?>"
                        placeholder="0">
                </div>

                <div class="form-group">
                    <label>E-Wallet (OVO, Dana, LinkAja, ShopeePay):</label>
                    <input type="number" name="admin_fee_ewallet"
                        value="<?php echo $adminFees['flip_ewallet'] ?? 0; ?>"
                        placeholder="0">
                </div>

                <div class="form-group">
                    <label>Retail (Alfamart, Indomaret):</label>
                    <input type="number" name="admin_fee_retail"
                        value="<?php echo $adminFees['flip_retail'] ?? 2500; ?>"
                        placeholder="2500">
                </div>

                <button type="submit">💾 Simpan Konfigurasi</button>
            </form>
        </div>

        <?php if ($currentConfig): ?>
            <div class="section">
                <h2>📊 Konfigurasi Saat Ini</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Gateway:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $currentConfig['gateway_name']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Status:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo $currentConfig['is_active'] ? '✅ Aktif' : '❌ Nonaktif'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Environment:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo strtoupper($currentConfig['environment']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Secret Key:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo substr($currentConfig['api_key'], 0, 10) . '***'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Callback URL:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <code><?php echo base_url('payment/callback/flip'); ?></code>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Last Update:</strong></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $currentConfig['updated_at']; ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <div class="info">
            <h3>📚 Dokumentasi</h3>
            <ul>
                <li><strong>Flip API Docs:</strong> <a href="https://docs.flip.id" target="_blank">https://docs.flip.id</a></li>
                <li><strong>Setup Guide:</strong> Lihat file SETUP_FLIP_PAYMENT.md</li>
                <li><strong>SQL Setup:</strong> Lihat file setup_flip_payment.sql</li>
            </ul>
        </div>

        <div class="warning">
            <h3>⚠️ Catatan Penting</h3>
            <ul>
                <li>Pastikan callback URL menggunakan HTTPS (SSL Certificate)</li>
                <li>Jangan share Secret Key dan Validation Token</li>
                <li>Test dengan sandbox mode sebelum production</li>
                <li>Monitor log di <code>writable/logs/</code> untuk troubleshooting</li>
                <li>Backup kredensial API di tempat yang aman</li>
            </ul>
        </div>
    </div>
</body>

</html>