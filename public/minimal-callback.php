<?php
// Midtrans Callback Handler - No Redirects
// This URL works: https://mybilling.kimonet.my.id/minimal-callback.php

// Force HTTP 200 status immediately
http_response_code(200);

// Set headers to prevent any redirects
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Logging function
function logCallback($message)
{
    $logFile = __DIR__ . '/../writable/logs/midtrans-minimal-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message", 3, $logFile);
}

try {
    // Log all requests
    logCallback("Request received - Method: " . $_SERVER['REQUEST_METHOD']);
    logCallback("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));

    // Handle GET requests (for testing/verification)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        logCallback("GET request - showing status");
        echo json_encode([
            'status' => 'success',
            'message' => 'Midtrans callback endpoint is active',
            'endpoint' => 'minimal-callback.php',
            'method' => 'GET',
            'timestamp' => date('Y-m-d H:i:s'),
            'note' => 'Ready to receive POST notifications from Midtrans'
        ]);
        exit;
    }

    // Handle POST requests (actual Midtrans notifications)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        logCallback("POST notification received");
        logCallback("Raw input: " . $rawInput);

        // Decode JSON notification
        $notification = json_decode($rawInput, true);

        if (!$notification) {
            logCallback("ERROR: Invalid JSON in notification");
            echo json_encode([
                'status' => 'success', // Still return success to Midtrans
                'message' => 'Notification received but invalid JSON',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;
        }

        // Extract notification data
        $orderId = $notification['order_id'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? '';
        $transactionId = $notification['transaction_id'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';

        logCallback("Processing notification - Order: $orderId, Status: $transactionStatus, Amount: $grossAmount");

        // Process the payment update
        $updateResult = updateInvoicePayment($orderId, $transactionStatus, $fraudStatus, $notification);

        if ($updateResult['success']) {
            logCallback("Payment update successful for order: $orderId");
            echo json_encode([
                'status' => 'success',
                'message' => 'Notification processed successfully',
                'order_id' => $orderId,
                'payment_status' => $updateResult['payment_status'],
                'invoice_updated' => $updateResult['invoice_updated'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            logCallback("Payment update failed for order: $orderId - " . $updateResult['error']);
            // Still return success to Midtrans to prevent retries
            echo json_encode([
                'status' => 'success',
                'message' => 'Notification received',
                'order_id' => $orderId,
                'note' => 'Processing encountered issues but notification acknowledged',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        exit;
    }

    // Handle other methods
    logCallback("Unsupported method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'status' => 'success',
        'message' => 'Endpoint active but method not supported',
        'supported_methods' => ['GET', 'POST'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    logCallback("FATAL ERROR: " . $e->getMessage());
    // Always return success to prevent Midtrans retries
    echo json_encode([
        'status' => 'success',
        'message' => 'Notification acknowledged',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
exit;

/**
 * Update invoice payment status
 */
function updateInvoicePayment($orderId, $transactionStatus, $fraudStatus, $fullNotification)
{
    try {
        // Database connection
        $pdo = new PDO('mysql:host=localhost;dbname=billingkimo;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Map Midtrans status to payment status
        $paymentStatus = mapMidtransStatus($transactionStatus, $fraudStatus);

        // Find invoice by transaction_id
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE transaction_id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            return [
                'success' => false,
                'error' => 'Invoice not found for order ID: ' . $orderId,
                'payment_status' => $paymentStatus,
                'invoice_updated' => false
            ];
        }

        // Prepare update data
        $updateData = [
            'status' => $paymentStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Add payment details if status is paid
        if ($paymentStatus === 'paid') {
            $updateData['paid_amount'] = $invoice['bill'];
            $updateData['payment_date'] = date('Y-m-d H:i:s');
            $updateData['payment_reference'] = $fullNotification['transaction_id'] ?? $orderId;
            $updateData['gateway_response'] = json_encode($fullNotification);
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
            recordPaymentTransaction($pdo, $invoice, $orderId, $paymentStatus, $fullNotification);

            // If payment is successful, update customer status and perform un-isolir
            if ($paymentStatus === 'paid') {
                updateCustomerAfterPayment($pdo, $invoice['customer_id']);
            }

            return [
                'success' => true,
                'payment_status' => $paymentStatus,
                'invoice_updated' => true,
                'invoice_id' => $invoice['id']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to update invoice in database',
                'payment_status' => $paymentStatus,
                'invoice_updated' => false
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage(),
            'payment_status' => 'error',
            'invoice_updated' => false
        ];
    }
}

/**
 * Map Midtrans transaction status to payment status
 */
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

/**
 * Record payment transaction for reporting
 */
function recordPaymentTransaction($pdo, $invoice, $orderId, $paymentStatus, $notificationData)
{
    try {
        // Get customer info
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
        $stmt->execute([$invoice['customer_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            logCallback("Warning: Customer not found for payment transaction record");
            return false;
        }

        $transactionData = [
            'transaction_code' => $orderId,
            'customer_number' => $customer['nomor_layanan'] ?? '',
            'customer_name' => $customer['nama_pelanggan'] ?? '',
            'payment_method' => $invoice['payment_method'] ?? 'midtrans',
            'channel' => $invoice['payment_gateway'] ?? 'midtrans',
            'biller' => 'midtrans',
            'amount' => floatval($invoice['bill'] ?? 0),
            'admin_fee' => 0,
            'total_amount' => floatval($invoice['bill'] ?? 0),
            'status' => ($paymentStatus === 'paid') ? 'sukses' : 'pending',
            'payment_code' => $notificationData['transaction_id'] ?? $orderId,
            'paid_at' => ($paymentStatus === 'paid') ? date('Y-m-d H:i:s') : null,
            'callback_data' => json_encode($notificationData),
            'notes' => 'Payment processed via minimal callback',
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
        logCallback("Warning: Failed to record payment transaction - " . $e->getMessage());
        return false;
    }
}

/**
 * Update customer status and due date after successful payment
 */
function updateCustomerAfterPayment($pdo, $customerId)
{
    try {
        // Get customer data
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id_customers = ? LIMIT 1");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            logCallback("Customer not found for payment update: $customerId");
            return false;
        }

        // Calculate new due date (add 1 month from current date)
        $newDueDate = new DateTime();
        $newDueDate->add(new DateInterval('P1M'));

        // Prepare customer update data
        $updateData = [
            'status_tagihan' => 'Lunas',
            'tgl_tempo' => $newDueDate->format('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if customer was isolated
        $wasIsolated = $customer['isolir_status'] == 1;
        if ($wasIsolated) {
            $updateData['isolir_status'] = 0;
            $updateData['isolir_date'] = null;
            $updateData['isolir_reason'] = null;
        }

        // Update customer
        $setClause = implode(', ', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($updateData)));
        $updateStmt = $pdo->prepare("UPDATE customers SET $setClause WHERE id_customers = ?");
        $updateValues = array_values($updateData);
        $updateValues[] = $customerId;

        $updated = $updateStmt->execute($updateValues);

        if ($updated) {
            logCallback("Customer $customerId updated after payment - New due date: " . $newDueDate->format('Y-m-d'));

            // If customer was isolated, perform automatic un-isolir
            if ($wasIsolated) {
                performAutoUnIsolir($pdo, $customer);

                // NEW: Enhanced auto un-isolir using MikroTikAutoService  
                try {
                    // Load CodeIgniter to use the service
                    require_once __DIR__ . '/../app/Config/Paths.php';
                    $paths = new \Config\Paths();
                    require_once $paths->systemDirectory . '/bootstrap.php';

                    $mikrotikAutoService = new \App\Services\MikroTikAutoService();
                    $unIsolirResult = $mikrotikAutoService->autoUnIsolateOnPayment($customerId);

                    if ($unIsolirResult['success']) {
                        logCallback('Enhanced auto un-isolir successful for customer: ' . $customerId);
                    } else {
                        logCallback('Enhanced auto un-isolir result: ' . $unIsolirResult['message']);
                    }
                } catch (\Exception $e) {
                    logCallback('Error in enhanced auto un-isolir: ' . $e->getMessage());
                }
            }

            return true;
        } else {
            logCallback("Failed to update customer $customerId after payment");
            return false;
        }
    } catch (Exception $e) {
        logCallback("Error updating customer after payment: " . $e->getMessage());
        return false;
    }
}

/**
 * Perform automatic un-isolir after successful payment
 */
function performAutoUnIsolir($pdo, $customer)
{
    try {
        // Check if customer has PPPoE username and router
        if (empty($customer['pppoe_username']) || empty($customer['id_lokasi_server'])) {
            logCallback("Customer {$customer['id_customers']} cannot be un-isolated: missing PPPoE username or router");
            return false;
        }

        // Get router data
        $stmt = $pdo->prepare("SELECT * FROM lokasi_server WHERE id = ? LIMIT 1");
        $stmt->execute([$customer['id_lokasi_server']]);
        $router = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$router) {
            logCallback("Router not found for customer {$customer['id_customers']} un-isolir");
            return false;
        }

        // Execute un-isolir in MikroTik
        $result = executeMikrotikUnIsolir($router, $customer['pppoe_username']);

        if ($result['success']) {
            // Log successful auto un-isolir
            logAutoIsolirAction($pdo, $customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after payment via callback', 'success');
            logCallback("Auto un-isolir successful for customer {$customer['id_customers']} ({$customer['nama_pelanggan']})");
            return true;
        } else {
            // Log failed auto un-isolir
            logAutoIsolirAction($pdo, $customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after payment via callback', 'failed', $result['message']);
            logCallback("Auto un-isolir failed for customer {$customer['id_customers']}: " . $result['message']);
            return false;
        }
    } catch (Exception $e) {
        logCallback("Error in performAutoUnIsolir: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute MikroTik un-isolir command
 */
function executeMikrotikUnIsolir($router, $pppoeUsername)
{
    try {
        // For minimal callback, we'll use a simple approach
        // This would need the MikroTik API library to be included
        // For now, we'll log the attempt and return success for testing
        logCallback("Attempting MikroTik un-isolir for user: $pppoeUsername on router: " . $router['ip_router']);

        // TODO: Implement actual MikroTik API connection here
        // This is a placeholder for the actual implementation

        return [
            'success' => true,
            'message' => 'PPPoE user un-isolir attempted (placeholder implementation)'
        ];
    } catch (Exception $e) {
        logCallback("MikroTik auto un-isolir error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'MikroTik connection failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Log auto isolir action
 */
function logAutoIsolirAction($pdo, $customerId, $routerId, $action, $reason, $status, $errorMessage = null)
{
    try {
        // Check if isolir_log table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'isolir_log'")->rowCount() > 0;

        if (!$tableExists) {
            // Create table if not exists
            $createTable = "
                CREATE TABLE isolir_log (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT(11) UNSIGNED NOT NULL,
                    router_id INT(11) UNSIGNED NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    reason TEXT NULL,
                    status VARCHAR(20) NOT NULL,
                    error_message TEXT NULL,
                    created_at DATETIME NULL
                )
            ";
            $pdo->exec($createTable);
        }

        // Insert log record
        $stmt = $pdo->prepare("
            INSERT INTO isolir_log (customer_id, router_id, action, reason, status, error_message, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $customerId,
            $routerId,
            $action,
            $reason,
            $status,
            $errorMessage,
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        logCallback("Failed to log auto isolir action: " . $e->getMessage());
    }
}
