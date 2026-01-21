<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Libraries\Payment\PaymentGatewayFactory;

class PaymentCallback extends BaseController
{
    /**
     * Handle payment callback from payment gateways
     */
    public function handleCallback($gateway = null)
    {
        try {
            // Set proper headers to avoid any redirect issues
            $this->response->setHeader('Cache-Control', 'no-cache, must-revalidate');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', '0');

            // Handle GET requests (for testing/verification)
            if ($this->request->getMethod() === 'GET') {
                return $this->showCallbackInfo($gateway);
            }

            log_message('info', 'Payment callback received for gateway: ' . $gateway);
            log_message('info', 'Request method: ' . $this->request->getMethod());
            log_message('info', 'Request URI: ' . $this->request->getUri());
            log_message('info', 'Callback headers: ' . json_encode($this->request->getHeaders()));

            // Get raw input for webhook processing
            $rawInput = $this->request->getBody();
            log_message('info', 'Callback raw input: ' . $rawInput);

            if (empty($gateway)) {
                log_message('error', 'Payment callback: Gateway not specified');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Gateway not specified'
                ]);
            }

            // Validate and process callback based on gateway type
            switch (strtolower($gateway)) {
                case 'midtrans':
                    return $this->handleMidtransCallback($rawInput);
                case 'duitku':
                    return $this->handleDuitkuCallback($rawInput);
                case 'flip':
                    return $this->handleFlipCallback($rawInput);
                default:
                    log_message('error', 'Payment callback: Unsupported gateway - ' . $gateway);
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'Unsupported gateway'
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Payment callback error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Internal server error'
            ]);
        }
    }

    /**
     * Handle Midtrans callback/notification
     */
    private function handleMidtransCallback($rawInput)
    {
        try {
            $notification = json_decode($rawInput, true);

            if (!$notification) {
                log_message('error', 'Midtrans callback: Invalid JSON');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ]);
            }

            $orderId = $notification['order_id'] ?? '';
            $transactionStatus = $notification['transaction_status'] ?? '';
            $fraudStatus = $notification['fraud_status'] ?? '';

            log_message('info', 'Midtrans notification - Order ID: ' . $orderId . ', Status: ' . $transactionStatus);

            if (empty($orderId)) {
                log_message('error', 'Midtrans callback: Order ID not found');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Order ID not found'
                ]);
            }

            // Verify notification dengan Midtrans
            $isValid = $this->verifyMidtransNotification($notification);
            if (!$isValid) {
                log_message('error', 'Midtrans callback: Invalid signature');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid signature'
                ]);
            }

            // Update payment status berdasarkan transaction status
            $paymentStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);
            $this->updateInvoicePaymentStatus($orderId, $paymentStatus, $notification);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'success',
                'message' => 'Notification processed',
                'order_id' => $orderId,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Midtrans callback processing error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Processing error'
            ]);
        }
    }

    /**
     * Map Midtrans transaction status to our payment status
     */
    private function mapMidtransStatus($transactionStatus, $fraudStatus = '')
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
     * Verify Midtrans notification signature
     */
    private function verifyMidtransNotification($notification)
    {
        try {
            // Get Midtrans server key from payment gateway settings
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $midtransConfig = $paymentModel->where('gateway_type', 'midtrans')
                ->where('is_active', 1)
                ->first();

            if (!$midtransConfig) {
                log_message('error', 'Midtrans config not found for signature verification');
                return false;
            }

            $serverKey = $midtransConfig['api_key'];
            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'];
            $grossAmount = $notification['gross_amount'];

            // Create signature hash
            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            $receivedSignature = $notification['signature_key'] ?? '';

            return hash_equals($signatureKey, $receivedSignature);
        } catch (\Exception $e) {
            log_message('error', 'Midtrans signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update invoice payment status
     */
    private function updateInvoicePaymentStatus($orderId, $paymentStatus, $notificationData)
    {
        try {
            $invoiceModel = new InvoiceModel();
            $paymentTransactionModel = new \App\Models\PaymentTransactionModel();

            // Find invoice by transaction_id (order_id)
            $invoice = $invoiceModel->where('transaction_id', $orderId)->first();

            if (!$invoice) {
                log_message('error', 'Invoice not found for order ID: ' . $orderId);
                return false;
            }

            // Prepare update data
            $updateData = [
                'status' => $paymentStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];            // Add payment details if status is paid
            if ($paymentStatus === 'paid') {
                $updateData['paid_amount'] = $invoice['bill'];
                $updateData['payment_date'] = date('Y-m-d H:i:s');
                $updateData['payment_reference'] = $notificationData['transaction_id'] ?? $orderId;
                $updateData['gateway_response'] = json_encode($notificationData);

                log_message('info', 'Payment confirmed for invoice ID: ' . $invoice['id'] . ', Order ID: ' . $orderId);
            }

            // Update invoice
            $updated = $invoiceModel->update($invoice['id'], $updateData);

            if ($updated) {
                log_message('info', 'Invoice payment status updated - ID: ' . $invoice['id'] . ', Status: ' . $paymentStatus);

                // Record payment transaction
                $this->recordPaymentTransaction($invoice, $orderId, $paymentStatus, $notificationData);

                // Send WhatsApp notification if payment is successful
                if ($paymentStatus === 'paid') {
                    // Update customer status and due date
                    // This also calls performAutoUnIsolir() if customer was isolated
                    $this->updateCustomerAfterPayment($invoice['customer_id'], date('Y-m-d'));

                    // Generate next month invoice
                    $this->generateNextMonthInvoice($invoice);

                    // Send WhatsApp notification
                    $this->sendPaymentConfirmationNotification($invoice['id']);
                }

                return true;
            } else {
                log_message('error', 'Failed to update invoice payment status for order ID: ' . $orderId);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Update invoice payment status error: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Send WhatsApp payment confirmation notification
     */
    private function sendPaymentConfirmationNotification($invoiceId)
    {
        try {
            log_message('info', 'Payment confirmation processing for invoice ID: ' . $invoiceId);

            // Load WhatsApp notification service
            $whatsappService = new \App\Controllers\WhatsappBillingNotification();

            // Get invoice detail
            $invoiceModel = new InvoiceModel();
            $invoice = $invoiceModel
                ->select('customer_invoices.*, customers.nama_pelanggan, customers.telepphone, customers.nomor_layanan')
                ->join('customers', 'customers.id_customers = customer_invoices.customer_id', 'left')
                ->find($invoiceId);

            if (!$invoice) {
                log_message('error', 'Invoice not found for payment notification: ' . $invoiceId);
                return false;
            }

            // Check if WhatsApp notification is enabled
            $notifModel = new \App\Models\WhatsappNotifSettingModel();
            $settings = $notifModel->first();

            if (!$settings || !$settings['notif_payment']) {
                log_message('info', 'WhatsApp payment notification is disabled');
                return false;
            }

            // Check if customer has phone number
            if (empty($invoice['telepphone'])) {
                log_message('warning', 'Customer has no phone number for payment notification: ' . $invoice['customer_id']);
                return false;
            }

            // Send payment confirmation message
            $messageLogModel = new \App\Models\WhatsappMessageLogModel();

            // Check if already notified
            $alreadyNotified = $messageLogModel
                ->where('customer_id', $invoice['customer_id'])
                ->where('invoice_id', $invoiceId)
                ->where('notification_type', 'payment_confirmation')
                ->where('DATE(created_at)', date('Y-m-d'))
                ->first();

            if ($alreadyNotified) {
                log_message('info', 'Payment confirmation already sent for invoice: ' . $invoiceId);
                return false;
            }

            // Call WhatsappBillingNotification method to send
            $reflection = new \ReflectionClass($whatsappService);
            $method = $reflection->getMethod('sendPaymentConfirmationMessage');
            $method->setAccessible(true);
            $result = $method->invoke($whatsappService, $invoice);

            if ($result) {
                log_message('info', 'Payment confirmation notification sent successfully for invoice: ' . $invoiceId);
            } else {
                log_message('error', 'Failed to send payment confirmation notification for invoice: ' . $invoiceId);
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Failed to process payment confirmation notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Record payment transaction for reporting
     */
    private function recordPaymentTransaction($invoice, $orderId, $paymentStatus, $notificationData)
    {
        try {
            $paymentTransactionModel = new \App\Models\PaymentTransactionModel();

            // Get customer info
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($invoice['customer_id']);

            if (!$customer) {
                log_message('warning', 'Customer not found for payment transaction record');
                return false;
            }

            // Prepare transaction data
            $transactionData = [
                'transaction_code' => $orderId,
                'customer_number' => $customer['nomor_layanan'] ?? '',
                'customer_name' => $customer['nama_pelanggan'] ?? '',
                'payment_method' => $invoice['payment_method'] ?? 'unknown',
                'channel' => $invoice['payment_gateway'] ?? 'unknown',
                'biller' => $invoice['payment_gateway'] ?? 'unknown',
                'amount' => floatval($invoice['bill'] ?? 0),
                'admin_fee' => 0, // No admin fee for now
                'total_amount' => floatval($invoice['bill'] ?? 0),
                'status' => $this->mapPaymentStatusForTransaction($paymentStatus),
                'payment_code' => $notificationData['transaction_id'] ?? $orderId,
                'expired_at' => null, // Will be set from notification if available
                'paid_at' => ($paymentStatus === 'paid') ? date('Y-m-d H:i:s') : null,
                'callback_data' => json_encode($notificationData),
                'notes' => 'Payment processed via callback'
            ];

            // Check if transaction already exists
            $existingTransaction = $paymentTransactionModel->where('transaction_code', $orderId)->first();

            if ($existingTransaction) {
                // Update existing transaction
                $updated = $paymentTransactionModel->update($existingTransaction['id'], $transactionData);
                log_message('info', 'Updated payment transaction record for order: ' . $orderId);
                return $updated;
            } else {
                // Create new transaction record
                $inserted = $paymentTransactionModel->insert($transactionData);
                log_message('info', 'Created payment transaction record for order: ' . $orderId);
                return $inserted;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to record payment transaction: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Map payment status to transaction status
     */
    private function mapPaymentStatusForTransaction($paymentStatus)
    {
        switch ($paymentStatus) {
            case 'paid':
                return 'sukses';
            case 'pending':
                return 'pending';
            case 'failed':
                return 'gagal';
            case 'expired':
                return 'expired';
            default:
                return 'pending';
        }
    }

    /**
     * Show callback endpoint information (for GET requests)
     */
    private function showCallbackInfo($gateway = null)
    {
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Callback Endpoint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Payment Callback Endpoint</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>Endpoint Active!</strong> This callback endpoint is working properly.
                        </div>
                        
                        <h5>Endpoint Information:</h5>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Gateway:</strong></td>
                                <td>' . ($gateway ?: 'Not specified') . '</td>
                            </tr>
                            <tr>
                                <td><strong>URL:</strong></td>
                                <td><code>' . current_url() . '</code></td>
                            </tr>
                            <tr>
                                <td><strong>Method:</strong></td>
                                <td>POST (for notifications), GET (for verification)</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><strong>Last Check:</strong></td>
                                <td>' . date('Y-m-d H:i:s') . '</td>
                            </tr>
                        </table>

                        <h5>Setup Instructions:</h5>
                        <div class="alert alert-info">
                            <h6>For ' . ucfirst($gateway ?: 'Payment Gateway') . ':</h6>
                            <p>Configure this URL in your payment gateway dashboard:</p>
                            <code>' . current_url() . '</code>
                        </div>

                        <h5>Test Endpoint:</h5>
                        <p>You can test this endpoint using the following tools:</p>
                        <ul>
                            <li><a href="' . base_url('test-payment-callback') . '" class="btn btn-sm btn-outline-primary">Payment Callback Tester</a></li>
                            <li><a href="' . base_url('test-midtrans-callback.php') . '" class="btn btn-sm btn-outline-secondary">Manual Callback Test</a></li>
                        </ul>

                        <h6>Recent Logs:</h6>
                        <p><small>Check <code>writable/logs/</code> for detailed callback logs.</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

        return $this->response->setBody($html);
    }

    /**
     * Update customer status and due date after successful payment
     */
    private function updateCustomerAfterPayment($customerId, $paymentDate)
    {
        try {
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                log_message('error', 'Customer not found for payment update: ' . $customerId);
                return false;
            }

            // Calculate new due date (add 1 month from payment date)
            $paymentDateTime = new \DateTime($paymentDate);
            $newDueDate = clone $paymentDateTime;
            $newDueDate->add(new \DateInterval('P1M')); // Add 1 month

            // Update customer data
            $updateCustomerData = [
                'status_tagihan' => 'Lunas',
                'tgl_tempo' => $newDueDate->format('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if customer was isolated and needs to be un-isolated
            $wasIsolated = $customer['isolir_status'] == 1;
            if ($wasIsolated) {
                // Add un-isolir data to customer update
                $updateCustomerData['isolir_status'] = 0;
                $updateCustomerData['isolir_date'] = null;
                $updateCustomerData['isolir_reason'] = null;
            }

            $result = $customerModel->update($customerId, $updateCustomerData);

            if ($result) {
                log_message('info', "Customer {$customerId} updated after payment - New due date: " . $newDueDate->format('Y-m-d'));

                // If customer was isolated, perform automatic un-isolir
                if ($wasIsolated) {
                    $this->performAutoUnIsolir($customer);
                }

                return true;
            } else {
                log_message('error', "Failed to update customer {$customerId} after payment");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating customer after payment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform automatic un-isolir after successful payment
     */
    private function performAutoUnIsolir($customer)
    {
        try {
            // Check if customer has PPPoE username and router
            if (empty($customer['pppoe_username']) || empty($customer['id_lokasi_server'])) {
                log_message('warning', "Customer {$customer['id_customers']} cannot be un-isolated: missing PPPoE username or router");
                return false;
            }

            // Get router data
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($customer['id_lokasi_server']);

            if (!$router) {
                log_message('error', "Router not found for customer {$customer['id_customers']} un-isolir");
                return false;
            }

            // Execute un-isolir in MikroTik
            $result = $this->executeMikrotikUnIsolir($router, $customer['pppoe_username']);

            if ($result['success']) {
                // Log successful auto un-isolir
                $this->logAutoIsolirAction($customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after payment', 'success');
                log_message('info', "Auto un-isolir successful for customer {$customer['id_customers']} ({$customer['nama_pelanggan']})");
                return true;
            } else {
                // Log failed auto un-isolir
                $this->logAutoIsolirAction($customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after payment', 'failed', $result['message']);
                log_message('error', "Auto un-isolir failed for customer {$customer['id_customers']}: " . $result['message']);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in performAutoUnIsolir: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute MikroTik un-isolir command
     */
    private function executeMikrotikUnIsolir($router, $pppoeUsername)
    {
        try {
            log_message('info', "Starting un-isolir for PPPoE user: $pppoeUsername on router: " . $router['name']);

            // Parse connection details
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            // Initialize MikroTik connection
            $mt = new \App\Libraries\MikrotikNew([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 60,
            ]);

            // Find PPPoE secret
            log_message('info', "Querying PPPoE secret for: $pppoeUsername");
            $secrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);
            if (empty($secrets)) {
                log_message('error', "PPPoE secret tidak ditemukan di router untuk user: $pppoeUsername");
                return [
                    'success' => false,
                    'message' => 'PPPoE secret tidak ditemukan di router'
                ];
            }

            $secretId = $secrets[0]['.id'];
            $currentProfile = $secrets[0]['profile'] ?? '';
            log_message('info', "Found PPPoE secret ID: $secretId, current profile: $currentProfile");

            // Get original profile from isolir log
            $originalProfile = $this->getOriginalProfileFromLog($pppoeUsername, $router['id_lokasi']);
            log_message('info', "Original profile from log: " . ($originalProfile ?? 'null'));

            if (!$originalProfile) {
                // If no log found, try to get from customer database
                $originalProfile = $this->getCustomerOriginalProfile($pppoeUsername);
                log_message('info', "Original profile from customer: " . ($originalProfile ?? 'null'));
            }

            if (!$originalProfile) {
                log_message('warning', "No original profile found for $pppoeUsername, using default");
                $originalProfile = 'default';
            }

            // Enable PPPoE secret and restore original profile
            log_message('info', "Updating PPPoE secret - Setting profile to: $originalProfile, disabled to: no");

            $result = $mt->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'disabled' => 'no',
                'profile' => $originalProfile
            ]);

            log_message('info', "MikroTik API response for /ppp/secret/set: " . json_encode($result));

            log_message('info', "MikroTik API response for /ppp/secret/set: " . json_encode($result));
            log_message('info', "PPPoE $pppoeUsername restored: profile changed from '$currentProfile' to '$originalProfile'");

            return [
                'success' => true,
                'message' => "PPPoE user berhasil dibuka isolirnya dan profile dikembalikan ke '$originalProfile'",
                'data' => [
                    'original_profile' => $originalProfile,
                    'previous_profile' => $currentProfile,
                    'mikrotik_result' => $result
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'MikroTik auto un-isolir error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Koneksi MikroTik gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get original profile from isolir log
     */
    private function getOriginalProfileFromLog($pppoeUsername, $routerId)
    {
        try {
            $db = \Config\Database::connect();

            // Find the last isolir action for this username
            // Note: status field may not exist in all records, so don't filter by it
            $query = $db->table('isolir_log')
                ->where('username', $pppoeUsername)
                ->where('router_id', $routerId)
                ->where('action', 'isolir')
                ->orderBy('created_at', 'DESC')
                ->limit(1);

            $result = $query->get()->getRowArray();

            if ($result && isset($result['old_profile'])) {
                log_message('info', "Found original profile for $pppoeUsername: " . $result['old_profile']);
                return $result['old_profile'];
            }

            log_message('warning', "No isolir log found for username: $pppoeUsername, router: $routerId");
            return null;
        } catch (\Exception $e) {
            log_message('error', 'Error getting original profile from log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customer original profile from database or default
     */
    private function getCustomerOriginalProfile($pppoeUsername)
    {
        try {
            // Get customer data
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->where('pppoe_username', $pppoeUsername)->first();

            if ($customer && !empty($customer['group_profile_id'])) {
                // Get profile name from group_profile table if exists
                $db = \Config\Database::connect();
                $profile = $db->table('group_profile')
                    ->select('profile_name')
                    ->where('id', $customer['group_profile_id'])
                    ->get()
                    ->getRowArray();

                if ($profile && !empty($profile['profile_name'])) {
                    return $profile['profile_name'];
                }
            }

            // Default profile based on customer package or use default
            return 'default';
        } catch (\Exception $e) {
            log_message('error', 'Error getting customer original profile: ' . $e->getMessage());
            return 'default';
        }
    }

    /**
     * Parse connection details from router IP and port
     */
    private function parseConnectionDetails($ipRouter, $portApi)
    {
        $port = !empty($portApi) ? (int)$portApi : 8728; // Default MikroTik API port

        // Remove protocol prefix if present
        $host = preg_replace('/^https?:\/\//', '', $ipRouter);

        // Remove port if already in IP string
        $host = preg_replace('/:\d+$/', '', $host);

        return [
            'host' => $host,
            'port' => $port
        ];
    }

    /**
     * Log auto isolir action
     */
    private function logAutoIsolirAction($customerId, $routerId, $action, $reason, $status, $errorMessage = null)
    {
        try {
            $db = \Config\Database::connect();

            // Check if isolir_log table exists, create if not
            if (!$db->tableExists('isolir_log')) {
                $forge = \Config\Database::forge();

                $forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'customer_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'router_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'action' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                    ],
                    'reason' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'status' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                    ],
                    'error_message' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
                $forge->addPrimaryKey('id');
                $forge->createTable('isolir_log');
            }

            // Insert log record
            $logData = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'action' => $action,
                'reason' => $reason,
                'status' => $status,
                'error_message' => $errorMessage,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('isolir_log')->insert($logData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log auto isolir action: ' . $e->getMessage());
        }
    }

    /**
     * Handle Duitku callback/notification
     */
    private function handleDuitkuCallback($rawInput)
    {
        try {
            // Duitku sends callback as POST data, not JSON
            $postData = $this->request->getPost();

            if (empty($postData)) {
                // Try to parse from raw input if POST is empty
                parse_str($rawInput, $postData);
            }

            log_message('info', 'Duitku callback data: ' . json_encode($postData));

            $merchantOrderId = $postData['merchantOrderId'] ?? '';
            $amount = $postData['amount'] ?? '';
            $resultCode = $postData['resultCode'] ?? '';
            $signature = $postData['signature'] ?? '';

            if (empty($merchantOrderId) || empty($amount) || empty($resultCode) || empty($signature)) {
                log_message('error', 'Duitku callback: Missing required parameters');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Missing required parameters'
                ]);
            }

            log_message('info', 'Duitku notification - Order ID: ' . $merchantOrderId . ', Status: ' . $resultCode . ', Amount: ' . $amount);

            // Get Duitku gateway config
            $gatewayModel = new \App\Models\PaymentGatewayModel();
            $duitkuConfig = $gatewayModel->getActiveGatewayByType('duitku');

            if (!$duitkuConfig) {
                log_message('error', 'Duitku callback: Gateway not configured');
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Duitku gateway not configured'
                ]);
            }

            // Verify signature
            $merchantCode = $duitkuConfig['merchant_code'];
            $apiKey = $duitkuConfig['api_key'];

            // Formula untuk callback: MD5(merchantcode + amount + merchantOrderId + apiKey)
            $calculatedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
            if (!hash_equals($calculatedSignature, $signature)) {
                log_message('error', 'Duitku callback: Invalid signature');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid signature'
                ]);
            }

            // Process payment based on result code
            $paymentStatus = $this->mapDuitkuStatus($resultCode);

            // Update invoice status
            $invoiceModel = new InvoiceModel();
            $invoice = $invoiceModel->where('invoice_no', $merchantOrderId)->first();

            if (!$invoice) {
                log_message('error', 'Duitku callback: Invoice not found - ' . $merchantOrderId);
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ]);
            }

            // Update invoice based on payment status
            if ($paymentStatus === 'paid' && $invoice['status'] !== 'paid') {
                $updateData = [
                    'status' => 'paid',
                    'payment_method' => 'duitku',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'notes' => 'Payment via Duitku - Reference: ' . ($postData['reference'] ?? 'N/A')
                ];

                $result = $invoiceModel->update($invoice['id'], $updateData);

                if ($result) {
                    log_message('info', 'Duitku callback: Invoice ' . $merchantOrderId . ' marked as paid');

                    // Update customer status after successful payment
                    $this->updateCustomerAfterPayment($invoice['customer_id'], date('Y-m-d H:i:s'));

                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Payment processed successfully'
                    ]);
                } else {
                    log_message('error', 'Duitku callback: Failed to update invoice ' . $merchantOrderId);
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to update invoice'
                    ]);
                }
            } else {
                log_message('info', 'Duitku callback: Payment status ' . $paymentStatus . ' for invoice ' . $merchantOrderId);
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Callback received, status: ' . $paymentStatus
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Duitku callback error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Internal server error'
            ]);
        }
    }

    /**
     * Map Duitku result code to payment status
     */
    private function mapDuitkuStatus($resultCode)
    {
        switch ($resultCode) {
            case '00':
                return 'paid';
            case '01':
                return 'pending';
            case '02':
                return 'failed';
            default:
                return 'unknown';
        }
    }

    /**
     * Handle Flip callback/notification
     */
    private function handleFlipCallback($rawInput)
    {
        try {
            $notification = json_decode($rawInput, true);

            if (!$notification) {
                log_message('error', 'Flip callback: Invalid JSON');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ]);
            }

            log_message('info', 'Flip notification received: ' . json_encode($notification));

            // Verify token dari header atau body
            $token = $this->request->getHeaderLine('token')
                ?? $this->request->getGet('token')
                ?? $notification['token'] ?? '';

            // Get Flip config untuk verifikasi
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $flipConfig = $paymentModel->getActiveGatewayByType('flip');

            if (!$flipConfig) {
                log_message('error', 'Flip callback: Configuration not found');
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Gateway configuration not found'
                ]);
            }

            // Verify token (validation token dari Flip)
            $validationToken = $flipConfig['api_secret'] ?? '';
            if (!empty($validationToken) && $token !== $validationToken) {
                log_message('error', 'Flip callback: Invalid token');
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid token'
                ]);
            }

            // Extract data dari callback
            $billId = $notification['bill_link_id'] ?? $notification['id'] ?? '';
            $status = $notification['status'] ?? '';
            $amount = $notification['amount'] ?? 0;

            log_message('info', 'Flip notification - Bill ID: ' . $billId . ', Status: ' . $status);

            if (empty($billId)) {
                log_message('error', 'Flip callback: Bill ID not found');
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Bill ID not found'
                ]);
            }

            // Cari invoice berdasarkan transaction_id yang tersimpan
            $invoiceModel = new InvoiceModel();
            $db = \Config\Database::connect();

            // Bill ID dari Flip disimpan di transaction_id
            $invoice = $db->table('customer_invoices')
                ->where('transaction_id', $billId)
                ->orWhere('invoice_no', $billId)
                ->get()
                ->getRowArray();

            if (!$invoice) {
                log_message('error', 'Flip callback: Invoice not found for bill ID - ' . $billId);
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ]);
            }

            // Map Flip status ke internal status
            $paymentStatus = 'pending';
            switch (strtoupper($status)) {
                case 'SUCCESSFUL':
                    $paymentStatus = 'paid';
                    break;
                case 'PENDING':
                    $paymentStatus = 'pending';
                    break;
                case 'FAILED':
                case 'CANCELLED':
                    $paymentStatus = 'failed';
                    break;
            }

            // Update invoice berdasarkan payment status
            if ($paymentStatus === 'paid' && $invoice['status'] !== 'paid') {
                $updateData = [
                    'status' => 'paid',
                    'payment_method' => 'flip',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'notes' => 'Payment via Flip - Bill ID: ' . $billId
                ];

                $result = $invoiceModel->update($invoice['id'], $updateData);

                if ($result) {
                    log_message('info', 'Flip callback: Invoice ' . $invoice['invoice_no'] . ' marked as paid');

                    // Update payment_transactions table status
                    $paymentCode = $notification['bill_payment']['receiver_bank_account']['account_number']
                        ?? $notification['payment_code']
                        ?? '';

                    if (!empty($paymentCode)) {
                        $db->table('payment_transactions')
                            ->where('invoice_id', $invoice['id'])
                            ->where('payment_code', $paymentCode)
                            ->update([
                                'status' => 'paid',
                                'paid_at' => date('Y-m-d H:i:s'),
                                'payment_response' => json_encode($notification)
                            ]);
                        log_message('info', 'Flip callback: Payment transaction updated for code ' . $paymentCode);
                    }

                    // Update customer status after successful payment
                    // This also calls performAutoUnIsolir() if customer was isolated
                    $this->updateCustomerAfterPayment($invoice['customer_id'], date('Y-m-d H:i:s'));

                    // Generate next month invoice
                    $this->generateNextMonthInvoice($invoice);

                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Payment processed successfully'
                    ]);
                } else {
                    log_message('error', 'Flip callback: Failed to update invoice ' . $invoice['invoice_no']);
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to update invoice'
                    ]);
                }
            } else {
                log_message('info', 'Flip callback: Payment status ' . $paymentStatus . ' for invoice ' . $invoice['invoice_no']);
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Callback received, status: ' . $paymentStatus
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Flip callback error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Internal server error'
            ]);
        }
    }

    /**
     * Generate next month invoice after successful payment
     */
    private function generateNextMonthInvoice($paidInvoice)
    {
        try {
            $invoiceModel = new InvoiceModel();
            $customerModel = new \App\Models\CustomerModel();

            // Get customer data
            $customer = $customerModel->find($paidInvoice['customer_id']);
            if (!$customer) {
                log_message('error', 'Customer not found for generating next invoice: ' . $paidInvoice['customer_id']);
                return false;
            }

            // Calculate next month periode
            $currentPeriode = $paidInvoice['periode']; // Format: YYYY-MM
            $nextPeriode = date('Y-m', strtotime($currentPeriode . '-01 +1 month'));

            // Check if next month invoice already exists
            $existingInvoice = $invoiceModel
                ->where('customer_id', $paidInvoice['customer_id'])
                ->where('periode', $nextPeriode)
                ->first();

            if ($existingInvoice) {
                log_message('info', 'Next month invoice already exists for customer ' . $paidInvoice['customer_id'] . ' periode ' . $nextPeriode);
                return true;
            }

            // Generate invoice number
            $invoiceNo = 'INV-' . strtoupper(substr($customer['nama_pelanggan'] ?? 'CUST', 0, 3)) . '-' . date('ymd') . '-' . str_pad($paidInvoice['customer_id'], 4, '0', STR_PAD_LEFT);

            // Create next month invoice
            $newInvoiceData = [
                'invoice_no' => $invoiceNo,
                'customer_id' => $paidInvoice['customer_id'],
                'customer_name' => $customer['nama_pelanggan'] ?? '',
                'periode' => $nextPeriode,
                'package' => $paidInvoice['package'] ?? '',
                'bill' => $paidInvoice['bill'], // Same amount
                'server' => $paidInvoice['server'] ?? $customer['id_lokasi_server'],
                'status' => 'pending',
                'payment_method' => null,
                'payment_code' => null,
                'payment_gateway' => null,
                'is_prorata' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $invoiceModel->insert($newInvoiceData);

            if ($result) {
                log_message('info', 'Successfully generated next month invoice for customer ' . $paidInvoice['customer_id'] . ' periode ' . $nextPeriode);
                return true;
            } else {
                log_message('error', 'Failed to generate next month invoice for customer ' . $paidInvoice['customer_id']);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error generating next month invoice: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore MikroTik profile from isolir to original profile
     */
    private function restoreMikrotikProfile($customerId)
    {
        try {
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                log_message('error', 'Customer not found for MikroTik restore: ' . $customerId);
                return false;
            }

            // Check if customer has PPPoE username and router
            if (empty($customer['pppoe_username']) || empty($customer['id_lokasi_server'])) {
                log_message('warning', "Customer {$customerId} cannot restore profile: missing PPPoE username or router");
                return false;
            }

            // Get router data
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($customer['id_lokasi_server']);

            if (!$router) {
                log_message('error', "Router not found for customer {$customerId}");
                return false;
            }

            // Get original profile from isolir log
            $isolirLogModel = new \App\Models\IsolirLogModel();
            $lastIsolirLog = $isolirLogModel
                ->where('customer_id', $customerId)
                ->where('action', 'isolir')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$lastIsolirLog || empty($lastIsolirLog['old_profile'])) {
                log_message('warning', "No isolir log found for customer {$customerId}, using package profile");

                // Fallback: get profile from customer package
                $packageModel = new \App\Models\PackageProfileModel();
                $package = $packageModel->find($customer['id_paket']);
                $originalProfile = $package['profile_name'] ?? 'default';
            } else {
                $originalProfile = $lastIsolirLog['old_profile'];
            }

            // Parse connection details
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            // Initialize MikroTik connection
            $mt = new \App\Libraries\MikrotikNew([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 60,
            ]);

            // Find PPPoE secret
            $secrets = $mt->comm('/ppp/secret/print', ['?name' => $customer['pppoe_username']]);
            if (empty($secrets)) {
                log_message('error', "PPPoE secret not found for customer {$customerId} username: " . $customer['pppoe_username']);
                return false;
            }

            $secretId = $secrets[0]['.id'];
            $currentProfile = $secrets[0]['profile'] ?? '';

            // Check if currently using isolir profile
            if (strpos(strtolower($currentProfile), 'isolir') === false) {
                log_message('info', "Customer {$customerId} is not on isolir profile, skipping restore");
                return true;
            }

            // Restore to original profile
            $updateResult = $mt->comm('/ppp/secret/set', [
                '.id' => $secretId,
                'profile' => $originalProfile
            ]);

            if ($updateResult) {
                log_message('info', "Successfully restored MikroTik profile for customer {$customerId} from '{$currentProfile}' to '{$originalProfile}'");

                // Log the restore action
                $isolirLogModel->insert([
                    'customer_id' => $customerId,
                    'customer_name' => $customer['nama_pelanggan'],
                    'pppoe_username' => $customer['pppoe_username'],
                    'router_id' => $router['id_lokasi'],
                    'router_name' => $router['name'],
                    'action' => 'restore',
                    'original_profile' => $originalProfile,
                    'isolir_profile' => $currentProfile,
                    'reason' => 'Auto restore after payment',
                    'status' => 'success',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return true;
            } else {
                log_message('error', "Failed to restore MikroTik profile for customer {$customerId}");

                // Log the failed restore
                $isolirLogModel->insert([
                    'customer_id' => $customerId,
                    'customer_name' => $customer['nama_pelanggan'],
                    'pppoe_username' => $customer['pppoe_username'],
                    'router_id' => $router['id_lokasi'],
                    'router_name' => $router['name'],
                    'action' => 'restore',
                    'original_profile' => $originalProfile,
                    'isolir_profile' => $currentProfile,
                    'reason' => 'Auto restore after payment',
                    'status' => 'failed',
                    'error_message' => 'MikroTik command failed',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error restoring MikroTik profile: ' . $e->getMessage());
            return false;
        }
    }
}
