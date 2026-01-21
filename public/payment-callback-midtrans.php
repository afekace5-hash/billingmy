<?php

/**
 * Direct Midtrans Callback Handler
 * This file bypasses CodeIgniter routing to avoid any redirect issues
 * Use this URL in Midtrans dashboard: https://mybilling.kimonet.my.id/payment-callback-midtrans.php
 */

// Prevent direct browser access
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['test'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Show endpoint info for GET requests
?>
        <!DOCTYPE html>
        <html lang="id">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Midtrans Callback Endpoint</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 40px;
                }

                .card {
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    max-width: 600px;
                }

                .success {
                    background-color: #d4edda;
                    border-color: #c3e6cb;
                    color: #155724;
                    padding: 10px;
                    border-radius: 4px;
                }

                .info {
                    background-color: #d1ecf1;
                    border-color: #bee5eb;
                    color: #0c5460;
                    padding: 10px;
                    border-radius: 4px;
                }

                code {
                    background-color: #f8f9fa;
                    padding: 2px 4px;
                    border-radius: 3px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }

                table th,
                table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }

                table th {
                    background-color: #f8f9fa;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <h2>Midtrans Callback Endpoint</h2>

                <div class="success">
                    <strong>✓ Endpoint Active!</strong> This callback endpoint is working properly.
                </div>

                <h3>Endpoint Information:</h3>
                <table>
                    <tr>
                        <td><strong>Gateway:</strong></td>
                        <td>Midtrans</td>
                    </tr>
                    <tr>
                        <td><strong>URL:</strong></td>
                        <td><code><?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Method:</strong></td>
                        <td>POST (for notifications), GET (for verification)</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span style="background-color: #28a745; color: white; padding: 2px 8px; border-radius: 3px;">Active</span></td>
                    </tr>
                    <tr>
                        <td><strong>Last Check:</strong></td>
                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                    </tr>
                </table>

                <div class="info">
                    <h4>Setup Instructions:</h4>
                    <p>Configure this URL in your Midtrans dashboard:</p>
                    <code><?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></code>
                    <p><strong>Note:</strong> This is a direct callback handler that bypasses routing to prevent redirect issues.</p>
                </div>

                <h4>Test Links:</h4>
                <p>
                    <a href="<?php echo dirname($_SERVER['REQUEST_URI']); ?>/test-payment-callback">Payment Callback Tester</a> |
                    <a href="?test=1">Test This Endpoint</a>
                </p>
            </div>
        </body>

        </html>
<?php
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Set headers to prevent caching and redirects
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');

// Log the callback
$logFile = __DIR__ . '/../writable/logs/midtrans-callback-' . date('Y-m-d') . '.log';
$logEntry = date('Y-m-d H:i:s') . " - Midtrans callback received\n";
$logEntry .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logEntry .= "Headers: " . json_encode(getallheaders()) . "\n";

// Handle test mode
if (isset($_GET['test'])) {
    $logEntry .= "Mode: TEST\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Test callback received',
        'endpoint' => 'midtrans-direct',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get POST data
$rawInput = file_get_contents('php://input');
$logEntry .= "Raw Input: " . $rawInput . "\n";

try {
    // Decode JSON
    $notification = json_decode($rawInput, true);

    if (!$notification) {
        $logEntry .= "Error: Invalid JSON\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    $orderId = $notification['order_id'] ?? '';
    $transactionStatus = $notification['transaction_status'] ?? '';
    $fraudStatus = $notification['fraud_status'] ?? '';

    $logEntry .= "Order ID: " . $orderId . "\n";
    $logEntry .= "Transaction Status: " . $transactionStatus . "\n";
    $logEntry .= "Fraud Status: " . $fraudStatus . "\n";

    if (empty($orderId)) {
        $logEntry .= "Error: Order ID not found\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Order ID not found']);
        exit;
    }

    // Load CodeIgniter to process the payment
    require_once __DIR__ . '/../vendor/autoload.php';

    // Bootstrap CodeIgniter
    $pathsPath = realpath(__DIR__ . '/../app/Config/Paths.php');
    $paths = require $pathsPath;

    $bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
    $app = require $bootstrap;

    // Process the callback using direct database operations
    $success = processPaymentNotification($notification);

    if ($success) {
        $logEntry .= "Processing completed successfully\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        // Return success response
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Notification processed',
            'order_id' => $orderId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        $logEntry .= "Processing failed\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Processing failed',
            'order_id' => $orderId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
} catch (Exception $e) {
    $logEntry .= "Error: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Process payment notification directly
 */
function processPaymentNotification($notification)
{
    try {
        // Database configuration - adjust as needed
        $dbConfig = [
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'billingkimo',
            'charset' => 'utf8mb4'
        ];

        $dsn = "mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? '';

        // Map transaction status to payment status
        $paymentStatus = mapMidtransStatus($transactionStatus, $fraudStatus);

        // Find invoice by transaction_id
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE transaction_id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            error_log("Invoice not found for order ID: " . $orderId);
            return false;
        }

        // Prepare update data
        $updateData = [
            'status' => $paymentStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($paymentStatus === 'paid') {
            $updateData['paid_amount'] = $invoice['bill'];
            $updateData['payment_date'] = date('Y-m-d H:i:s');
            $updateData['payment_reference'] = $notification['transaction_id'] ?? $orderId;
            $updateData['gateway_response'] = json_encode($notification);
        }

        // Update invoice
        $setClause = implode(', ', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($updateData)));
        $updateStmt = $pdo->prepare("UPDATE invoices SET $setClause WHERE id = ?");
        $updateValues = array_values($updateData);
        $updateValues[] = $invoice['id'];

        $updated = $updateStmt->execute($updateValues);

        if ($updated) {
            // Record payment transaction
            recordPaymentTransaction($pdo, $invoice, $orderId, $paymentStatus, $notification);
            error_log("Invoice payment status updated - ID: {$invoice['id']}, Status: $paymentStatus");

            // Kirim pesan WhatsApp jika pembayaran sukses
            if ($paymentStatus === 'paid') {
                $customerPhone = $invoice['customer_phone'] ?? '';
                if (!$customerPhone) {
                    // Coba ambil dari tabel customer
                    $stmt = $pdo->prepare("SELECT nomor_whatsapp FROM customers WHERE id = ? LIMIT 1");
                    $stmt->execute([$invoice['customer_id']]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $customerPhone = $row['nomor_whatsapp'] ?? '';
                }
                if ($customerPhone) {
                    // Siapkan data pesan WhatsApp
                    $company = 'KimoNet';
                    $customerName = $invoice['customer_name'] ?? '';
                    $noInvoice = $invoice['no_invoice'] ?? $orderId;
                    $tanggal = date('d-m-Y H:i');
                    $total = number_format($invoice['bill'] ?? 0, 0, ',', '.');
                    $periode = $invoice['periode'] ?? '';
                    $tunggakan = '0'; // Bisa diambil dari data lain jika ada
                    $template = "```{$company}```\n\n_Halo {$customerName},_\n\nTerima kasih sudah melakukan pembayaran\n\n*No Invoice*: {$noInvoice}\n*Tanggal*: {$tanggal}\n*Jumlah pembayaran*: {$total}\n*Tunggakan*: {$tunggakan}\n*Periode*: {$periode}\n\n*Terima kasih*";

                    // Kirim pesan via API internal WhatsApp
                    // Ambil API URL dari database settings
                    $apiUrl = '';
                    try {
                        $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'whatsapp_api_url' LIMIT 1");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row && !empty($row['value'])) {
                            $apiUrl = $row['value'];
                        }
                    } catch (Exception $e) {
                        error_log('Gagal mengambil API URL WhatsApp dari database: ' . $e->getMessage());
                    }
                    if (!$apiUrl) {
                        $apiUrl = 'https://wamoo.kimonet.my.id/send-message'; // fallback default using correct API
                    }

                    // Get WhatsApp device configuration
                    $whatsappDevice = null;
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM whatsapp_devices ORDER BY id DESC LIMIT 1");
                        $stmt->execute();
                        $whatsappDevice = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        error_log('Gagal mengambil device WhatsApp: ' . $e->getMessage());
                    }

                    if ($whatsappDevice) {
                        $postData = [
                            'api_key' => $whatsappDevice['api_key'],
                            'sender' => $whatsappDevice['number'],
                            'number' => $customerPhone,
                            'message' => $template
                        ];

                        // Use GET request like the correct API format
                        $queryParams = http_build_query($postData);
                        $fullUrl = $apiUrl . '?' . $queryParams;

                        $ch = curl_init($fullUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($httpCode === 200) {
                            $result = json_decode($response, true);
                            if (isset($result['status']) && $result['status'] === true) {
                                error_log("WhatsApp payment notification sent successfully to {$customerPhone}");
                            } else {
                                error_log("WhatsApp API error response: " . ($result['msg'] ?? 'Unknown error'));
                            }
                        } else {
                            error_log("WhatsApp API HTTP error: {$httpCode}");
                        }
                    } else {
                        error_log("No WhatsApp device configured for payment notifications");
                    }
                }
            }
            return true;
        }

        return false;
    } catch (Exception $e) {
        error_log("Payment processing error: " . $e->getMessage());
        return false;
    }
}

function mapMidtransStatus($transactionStatus, $fraudStatus = '')
{
    switch ($transactionStatus) {
        case 'capture':
            return ($fraudStatus === 'accept') ? 'paid' : 'pending';
        case 'settlement':
            return 'paid';
        case 'pending':
            return 'pending';
        case 'deny':
        case 'cancel':
        case 'expire':
        case 'failure':
            return 'failed';
        default:
            return 'pending';
    }
}

function recordPaymentTransaction($pdo, $invoice, $orderId, $paymentStatus, $notificationData)
{
    try {
        // Get customer info
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
        $stmt->execute([$invoice['customer_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            error_log("Customer not found for payment transaction record");
            return false;
        }

        $transactionData = [
            'transaction_code' => $orderId,
            'customer_number' => $customer['nomor_layanan'] ?? '',
            'customer_name' => $customer['nama_pelanggan'] ?? '',
            'payment_method' => $invoice['payment_method'] ?? 'unknown',
            'channel' => $invoice['payment_gateway'] ?? 'midtrans',
            'biller' => $invoice['payment_gateway'] ?? 'midtrans',
            'amount' => floatval($invoice['bill'] ?? 0),
            'admin_fee' => 0,
            'total_amount' => floatval($invoice['bill'] ?? 0),
            'status' => ($paymentStatus === 'paid') ? 'sukses' : 'pending',
            'payment_code' => $notificationData['transaction_id'] ?? $orderId,
            'paid_at' => ($paymentStatus === 'paid') ? date('Y-m-d H:i:s') : null,
            'callback_data' => json_encode($notificationData),
            'notes' => 'Payment processed via direct callback',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if transaction already exists
        $checkStmt = $pdo->prepare("SELECT id FROM payment_transactions WHERE transaction_code = ? LIMIT 1");
        $checkStmt->execute([$orderId]);
        $existingTransaction = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingTransaction) {
            // Update existing transaction
            $setClause = implode(', ', array_map(function ($key) {
                return "$key = ?";
            }, array_keys($transactionData)));
            $updateStmt = $pdo->prepare("UPDATE payment_transactions SET $setClause WHERE id = ?");
            $updateValues = array_values($transactionData);
            $updateValues[] = $existingTransaction['id'];
            return $updateStmt->execute($updateValues);
        } else {
            // Insert new transaction
            $columns = implode(', ', array_keys($transactionData));
            $placeholders = implode(', ', array_fill(0, count($transactionData), '?'));
            $insertStmt = $pdo->prepare("INSERT INTO payment_transactions ($columns) VALUES ($placeholders)");
            return $insertStmt->execute(array_values($transactionData));
        }
    } catch (Exception $e) {
        error_log("Failed to record payment transaction: " . $e->getMessage());
        return false;
    }
}
?>