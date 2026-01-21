<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Payment\PaymentGatewayFactory;

class Payment extends Controller
{
    public function index()
    {
        return view('payment_getway/tester');
    }

    public function setupGuide()
    {
        return view('payment_getway/setup_guide');
    }

    public function demo()
    {
        return view('payment_getway/index');
    }

    public function createInvoice()
    {
        try {
            // Get payment data from request
            $data = [
                'order_id' => 'INV-' . time(),
                'amount' => $this->request->getPost('amount') ?: 50000,
                'customer_name' => $this->request->getPost('customer_name') ?: 'Test Customer',
                'customer_email' => $this->request->getPost('customer_email') ?: 'test@example.com',
                'customer_phone' => $this->request->getPost('customer_phone') ?: '081234567890',
                'description' => $this->request->getPost('description') ?: 'Payment for Internet Service',
                'method' => $this->request->getPost('method') ?: 'auto',
                'return_url' => base_url('payment/success'),
                'callback_url' => base_url('payment/callback')
            ];

            // Use the first available active gateway
            $activeGateways = PaymentGatewayFactory::getActiveGateways();

            if (empty($activeGateways)) {
                return view('error_view', ['error' => 'No payment gateway available']);
            }

            // Get the first active gateway
            $gatewayType = array_key_first($activeGateways);
            $gateway = PaymentGatewayFactory::create($gatewayType);

            if (!$gateway) {
                return view('error_view', ['error' => 'Payment gateway not available']);
            }

            $result = $gateway->createTransaction($data);

            if ($result['success'] && !empty($result['payment_url'])) {
                return redirect()->to($result['payment_url']);
            } else {
                return view('error_view', ['error' => $result['message'] ?? 'Failed to create payment']);
            }
        } catch (\Exception $e) {
            return view('error_view', ['error' => $e->getMessage()]);
        }
    }
    public function callback()
    {
        // Payment callback handler
        $data = $this->request->getPost();

        if (isset($data['status']) && $data['status'] == 'PAID') {
            // Proses pembayaran berhasil
            return view('success_view');
        } else {
            // Proses pembayaran gagal
            return view('failure_view');
        }
    }

    public function testConnection()
    {
        try {
            $gateway = $this->request->getPost('gateway');

            if (empty($gateway)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway tidak dipilih'
                ]);
            }            // Load gateway configuration
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $config = $paymentModel->getGatewayByType($gateway);

            if (!$config) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konfigurasi gateway tidak ditemukan untuk: ' . $gateway,
                    'debug' => [
                        'gateway_requested' => $gateway,
                        'all_gateways' => $paymentModel->select('gateway_type, gateway_name, is_active')->findAll()
                    ]
                ]);
            }            // Load appropriate service based on gateway type
            $result = ['success' => false, 'message' => 'Gateway tidak didukung'];

            // Add debug information about the config
            $debugInfo = [
                'gateway_type' => $gateway,
                'config_found' => !empty($config),
                'is_active' => $config['is_active'] ?? null,
                'has_api_key' => !empty($config['api_key'] ?? ''),
                'environment' => $config['environment'] ?? null
            ];

            switch ($gateway) {
                case 'midtrans':
                    $service = new \App\Libraries\Payment\MidtransService($config);
                    $result = $service->testConnection();
                    break;

                case 'duitku':
                    $service = new \App\Libraries\Payment\DuitkuService($config);
                    $result = $service->testConnection();
                    break;

                case 'flip':
                    $service = new \App\Libraries\Payment\FlipService($config);
                    $result = $service->testConnection();
                    break;
            }

            // Add debug info to result
            $result['debug'] = $debugInfo;

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    public function handleCallback($gateway)
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            // Load gateway configuration
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $config = $paymentModel->getActiveGatewayByType($gateway);

            if (!$config) {
                log_message('error', "Gateway configuration not found for: $gateway");
                return $this->response->setStatusCode(404);
            } // Process callback based on gateway type
            switch ($gateway) {


                case 'midtrans':
                    $service = new \App\Libraries\Payment\MidtransService($config);
                    break;

                case 'duitku':
                    $service = new \App\Libraries\Payment\DuitkuService($config);
                    break;

                default:
                    log_message('error', "Unsupported gateway: $gateway");
                    return $this->response->setStatusCode(400);
            }

            $result = $service->handleCallback($data);

            // Process the payment result
            // Update invoice status, send notifications, etc.

            log_message('info', "Payment callback processed for $gateway: " . json_encode($result));

            return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            log_message('error', "Payment callback error: " . $e->getMessage());
            return $this->response->setStatusCode(500);
        }
    }

    public function methods()
    {
        try {
            $methods = PaymentGatewayFactory::getAvailablePaymentMethods();

            return $this->response->setJSON([
                'success' => true,
                'data' => $methods,
                'message' => 'Payment methods loaded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading payment methods: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function gatewayStatus()
    {
        try {
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $gateways = $paymentModel->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => $gateways,
                'message' => 'Gateway status loaded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading gateway status: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function debugGateways()
    {
        try {
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $allGateways = $paymentModel->findAll();
            $activeGateways = $paymentModel->getActiveGateways();

            return $this->response->setJSON([
                'success' => true,
                'all_gateways' => $allGateways,
                'active_gateways' => $activeGateways,
                'total_gateways' => count($allGateways),
                'total_active' => count($activeGateways)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getActiveGateways()
    {
        try {
            log_message('info', 'getActiveGateways called');

            $gatewayModel = model('PaymentGatewayModel');
            $gateways = $gatewayModel->where('is_active', 1)->findAll();

            log_message('info', 'Found ' . count($gateways) . ' active gateways');

            $paymentMethods = [];
            $cache = \Config\Services::cache();

            foreach ($gateways as $gateway) {
                $gatewayType = strtolower($gateway['gateway_type']);
                $methods = [];

                // Get payment methods based on gateway type
                if ($gatewayType === 'flip') {
                    // Try to get from cache first (cache for 1 hour)
                    $cacheKey = 'flip_payment_methods_' . $gateway['id'];
                    $methods = $cache->get($cacheKey);

                    if (!$methods) {
                        // Get from Flip API
                        try {
                            $flipConfig = json_decode($gateway['configuration'] ?? '{}', true);
                            $flipService = new \App\Libraries\Payment\FlipService($flipConfig);
                            $methods = $flipService->getAvailablePaymentMethods();

                            // Cache the result
                            $cache->save($cacheKey, $methods, 3600); // 1 hour
                        } catch (\Exception $e) {
                            log_message('error', 'Failed to get Flip methods: ' . $e->getMessage());
                            // Fallback to default
                            $methods = [
                                ['code' => 'bca', 'name' => 'Bank Transfer BCA'],
                                ['code' => 'bni', 'name' => 'Bank Transfer BNI'],
                                ['code' => 'bri', 'name' => 'Bank Transfer BRI'],
                                ['code' => 'mandiri', 'name' => 'Bank Transfer Mandiri'],
                                ['code' => 'qris', 'name' => 'QRIS'],
                            ];
                        }
                    }
                } elseif ($gatewayType === 'midtrans') {
                    // Midtrans methods - could also be fetched from API if needed
                    $methods = [
                        ['code' => 'bank_transfer', 'name' => 'Bank Transfer'],
                        ['code' => 'bca_va', 'name' => 'BCA Virtual Account'],
                        ['code' => 'bni_va', 'name' => 'BNI Virtual Account'],
                        ['code' => 'bri_va', 'name' => 'BRI Virtual Account'],
                        ['code' => 'mandiri_va', 'name' => 'Mandiri Virtual Account'],
                        ['code' => 'permata_va', 'name' => 'Permata Virtual Account'],
                        ['code' => 'gopay', 'name' => 'GoPay'],
                        ['code' => 'qris', 'name' => 'QRIS'],
                        ['code' => 'shopeepay', 'name' => 'ShopeePay'],
                        ['code' => 'credit_card', 'name' => 'Credit Card'],
                    ];
                } else {
                    // Default methods for other gateways
                    $methods = [
                        ['code' => 'bank_transfer', 'name' => 'Bank Transfer'],
                        ['code' => 'qris', 'name' => 'QRIS'],
                    ];
                }

                // Build payment methods array
                foreach ($methods as $method) {
                    // Skip if method is explicitly disabled
                    if (isset($method['enabled']) && !$method['enabled']) {
                        continue;
                    }

                    $paymentMethods[] = [
                        'gateway_id' => $gateway['id'],
                        'gateway_name' => $gateway['gateway_name'],
                        'gateway_type' => $gateway['gateway_type'],
                        'method_code' => $method['code'],
                        'method_name' => $method['name'],
                        'display_name' => $method['name']
                    ];
                }
            }

            log_message('info', 'Returning ' . count($paymentMethods) . ' payment methods');

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getActiveGateways error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load payment gateways: ' . $e->getMessage()
            ]);
        }
    }

    public function setMethod()
    {
        try {
            $invoiceId = $this->request->getPost('invoice_id');
            $gatewayId = $this->request->getPost('gateway_id');
            $methodCode = $this->request->getPost('method_code');
            $methodName = $this->request->getPost('method_name');
            $sendNotification = $this->request->getPost('send_notification');

            $invoiceModel = model('InvoiceModel');
            $gatewayModel = model('PaymentGatewayModel');
            $customerModel = model('CustomerModel');

            // Get invoice data
            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ]);
            }

            // Get gateway data
            $gateway = $gatewayModel->find($gatewayId);
            if (!$gateway) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Payment gateway not found'
                ]);
            }

            // Get customer data
            $customer = $customerModel->find($invoice['customer_id']);

            $paymentCode = null;
            $paymentUrl = null;
            $transactionId = null;

            // Create payment transaction if Flip
            if (strtolower($gateway['gateway_type']) === 'flip' && !empty($methodCode)) {
                try {
                    // Build config array from individual fields (NOT from configuration field)
                    $flipConfig = [
                        'api_key' => $gateway['api_key'] ?? '',
                        'api_secret' => $gateway['api_secret'] ?? '',
                        'environment' => $gateway['environment'] ?? 'sandbox'
                    ];

                    log_message('debug', 'Flip Config: ' . json_encode([
                        'api_key_length' => strlen($flipConfig['api_key']),
                        'api_secret_length' => strlen($flipConfig['api_secret']),
                        'environment' => $flipConfig['environment']
                    ]));

                    $flipService = new \App\Libraries\Payment\FlipService($flipConfig);

                    // Prepare transaction data
                    $transactionData = [
                        'order_id' => $invoice['invoice_no'] ?? 'INV-' . $invoiceId,
                        'amount' => (float)$invoice['bill'],
                        'customer_name' => $customer['nama_pelanggan'] ?? 'Customer',
                        'customer_email' => $customer['email'] ?? 'customer@example.com',
                        'customer_phone' => $customer['telepphone'] ?? '081234567890',
                        'description' => 'Pembayaran ' . ($invoice['invoice_no'] ?? 'Invoice'),
                        'method' => $methodCode,
                        'payment_type' => $methodCode,
                        'return_url' => base_url('payment/success'),
                        'callback_url' => base_url('payment/callback/flip')
                    ];

                    // Create transaction via Flip
                    $result = $flipService->createTransaction($transactionData);

                    log_message('debug', 'Flip transaction result: ' . json_encode($result));

                    if ($result['success']) {
                        $paymentCode = $result['payment_code'] ?? 'Lihat halaman pembayaran';
                        $paymentUrl = $result['payment_url'] ?? $result['bill_link'] ?? null;
                        $transactionId = $result['bill_payment_id'] ?? $result['transaction_id'] ?? $result['link_id'] ?? null;

                        log_message('debug', 'Extracted values - Code: ' . $paymentCode . ', URL: ' . $paymentUrl . ', TxID: ' . $transactionId);

                        // Save to payment_transactions table
                        $paymentTransactionModel = model('PaymentTransactionModel');
                        $insertData = [
                            'invoice_id' => $invoiceId,
                            'customer_id' => $invoice['customer_id'],
                            'transaction_id' => $transactionId,
                            'payment_gateway' => 'flip',
                            'transaction_code' => $invoice['invoice_no'] ?? 'INV-' . $invoiceId,
                            'customer_number' => $customer['nomor_layanan'] ?? '',
                            'customer_name' => $customer['nama_pelanggan'] ?? 'Customer',
                            'payment_method' => $methodName,
                            'channel' => $methodCode,
                            'biller' => 'FLIP',
                            'amount' => (float)$invoice['bill'],
                            'admin_fee' => 0,
                            'total_amount' => (float)$invoice['bill'],
                            'status' => 'pending',
                            'payment_code' => $paymentCode,
                            'payment_date' => date('Y-m-d H:i:s'),
                            'expired_at' => $result['expired_at'] ?? date('Y-m-d H:i:s', strtotime('+24 hours')),
                            'response_data' => json_encode($result)
                        ];

                        log_message('debug', 'Inserting payment transaction: InvoiceID=' . $invoiceId . ', CustomerID=' . $invoice['customer_id'] . ', PaymentCode=' . $paymentCode);

                        $insertResult = $paymentTransactionModel->insert($insertData);

                        if ($insertResult) {
                            log_message('debug', 'Payment transaction inserted successfully with ID: ' . $insertResult);
                        } else {
                            log_message('error', 'Failed to insert payment transaction: ' . json_encode($paymentTransactionModel->errors()));
                        }
                    } else {
                        log_message('error', 'Flip transaction failed: ' . ($result['message'] ?? 'Unknown error'));
                        // Kembalikan error ke frontend
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Gagal membuat transaksi: ' . ($result['message'] ?? 'Unknown error')
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to create Flip transaction: ' . $e->getMessage());
                    // Continue without failing - payment code will be null
                }
            }

            // Update invoice with payment method and transaction details
            $updateData = [
                'payment_gateway_id' => $gatewayId,
                'payment_method' => $methodName,
                'payment_code' => $paymentCode,
                'payment_url' => $paymentUrl,
                'payment_transaction_id' => $transactionId
            ];

            log_message('debug', 'Updating invoice #' . $invoiceId . ' with data: ' . json_encode($updateData));

            $invoiceModel->update($invoiceId, $updateData);

            // Send notification if requested
            if ($sendNotification == 1 && !empty($customer['telepphone'])) {
                try {
                    $this->sendPaymentNotification($invoice, $customer, $methodName, $paymentCode, $paymentUrl);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to send WhatsApp notification: ' . $e->getMessage());
                    // Continue even if notification fails
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Payment method set successfully',
                'payment_code' => $paymentCode,
                'payment_url' => $paymentUrl,
                'transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to set payment method: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get payment history for an invoice
     */
    public function getPaymentHistory($invoiceId)
    {
        try {
            $paymentTransactionModel = model('PaymentTransactionModel');
            $transactions = $paymentTransactionModel
                ->where('invoice_id', $invoiceId)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load payment history: ' . $e->getMessage()
            ]);
        }
    }

    public function process($invoiceId)
    {
        $invoiceModel = model('InvoiceModel');
        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // If payment URL exists, redirect to it
        if (!empty($invoice['payment_url'])) {
            return redirect()->to($invoice['payment_url']);
        }

        // Otherwise show payment info page
        $data = [
            'title' => 'Proses Pembayaran',
            'invoice' => $invoice
        ];

        return view('payment/process', $data);
    }

    /**
     * Send WhatsApp notification for payment code and instructions
     */
    private function sendPaymentNotification($invoice, $customer, $methodName, $paymentCode, $paymentUrl)
    {
        // Get company info
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();
        $companyName = $company['name'] ?? 'KIMONET';

        // Format amount
        $amount = number_format($invoice['bill'], 0, ',', '.');

        // Build message
        $message = "🔔 *INFORMASI PEMBAYARAN*\n\n";
        $message .= "Halo *{$customer['nama_pelanggan']}*,\n\n";
        $message .= "Tagihan Anda telah tersedia untuk pembayaran:\n\n";
        $message .= "📋 *Detail Tagihan:*\n";
        $message .= "• No. Invoice: {$invoice['invoice_no']}\n";
        $message .= "• Periode: " . date('F Y', strtotime($invoice['periode'] . '-01')) . "\n";
        $message .= "• Jumlah Tagihan: Rp {$amount}\n";
        $message .= "• Metode Pembayaran: {$methodName}\n";

        if ($paymentCode && $paymentCode !== 'Lihat halaman pembayaran') {
            $message .= "\n💳 *Kode Pembayaran:*\n";
            $message .= "`{$paymentCode}`\n";
            $message .= "(Klik untuk menyalin)\n";
        }

        // Add payment instructions based on method type
        $message .= "\n📝 *CARA PEMBAYARAN:*\n\n";

        // Check if it's a bank transfer or e-wallet
        $methodLower = strtolower($methodName);

        if (
            strpos($methodLower, 'bca') !== false || strpos($methodLower, 'bni') !== false ||
            strpos($methodLower, 'mandiri') !== false || strpos($methodLower, 'bri') !== false ||
            strpos($methodLower, 'bank') !== false || strpos($methodLower, 'transfer') !== false
        ) {
            // Bank Transfer Instructions
            $message .= "*Via ATM:*\n";
            $message .= "1. Pilih menu Transfer\n";
            $message .= "2. Pilih Bank Tujuan\n";
            $message .= "3. Masukkan kode pembayaran di atas\n";
            $message .= "4. Konfirmasi dan selesaikan transaksi\n\n";

            $message .= "*Via Mobile Banking:*\n";
            $message .= "1. Login ke aplikasi mobile banking\n";
            $message .= "2. Pilih menu Transfer/Bayar\n";
            $message .= "3. Masukkan kode pembayaran\n";
            $message .= "4. Verifikasi dan konfirmasi pembayaran\n\n";

            $message .= "*Via Internet Banking:*\n";
            $message .= "1. Login ke internet banking\n";
            $message .= "2. Pilih menu Transfer/Pembayaran\n";
            $message .= "3. Masukkan kode pembayaran\n";
            $message .= "4. Ikuti instruksi hingga selesai\n";
        } elseif (strpos($methodLower, 'qris') !== false) {
            // QRIS Instructions
            $message .= "1. Buka aplikasi mobile banking/e-wallet\n";
            $message .= "2. Pilih menu QRIS/Scan QR\n";
            $message .= "3. Scan kode QR yang tersedia\n";
            $message .= "4. Periksa nominal pembayaran\n";
            $message .= "5. Konfirmasi dan selesaikan pembayaran\n";
        } elseif (
            strpos($methodLower, 'gopay') !== false || strpos($methodLower, 'ovo') !== false ||
            strpos($methodLower, 'dana') !== false || strpos($methodLower, 'shopeepay') !== false ||
            strpos($methodLower, 'linkaja') !== false
        ) {
            // E-Wallet Instructions
            $message .= "1. Buka aplikasi e-wallet Anda\n";
            $message .= "2. Pilih menu Bayar/Transfer\n";
            $message .= "3. Masukkan kode pembayaran\n";
            $message .= "   atau scan QR code jika tersedia\n";
            $message .= "4. Periksa detail pembayaran\n";
            $message .= "5. Konfirmasi dengan PIN/biometrik\n";
        } elseif (strpos($methodLower, 'alfamart') !== false || strpos($methodLower, 'indomaret') !== false) {
            // Retail Store Instructions
            $message .= "1. Kunjungi gerai terdekat\n";
            $message .= "2. Berikan kode pembayaran ke kasir\n";
            $message .= "3. Serahkan uang tunai sesuai nominal\n";
            $message .= "4. Simpan struk sebagai bukti pembayaran\n";
        } else {
            // Generic Instructions
            $message .= "1. Buka aplikasi/platform pembayaran\n";
            $message .= "2. Masukkan kode pembayaran:\n";
            $message .= "   `{$paymentCode}`\n";
            $message .= "3. Periksa detail pembayaran\n";
            $message .= "4. Konfirmasi dan selesaikan transaksi\n";
        }

        if ($paymentUrl) {
            $message .= "\n🔗 *Link Pembayaran:*\n";
            $message .= "{$paymentUrl}\n";
        }

        $message .= "\n⚠️ *Penting:*\n";
        $message .= "• Pastikan nominal yang dibayarkan sesuai\n";
        $message .= "• Pembayaran akan diproses otomatis\n";
        $message .= "• Simpan bukti pembayaran Anda\n";

        if (isset($invoice['due_date'])) {
            $dueDate = date('d F Y', strtotime($invoice['due_date']));
            $message .= "• Jatuh tempo: {$dueDate}\n";
        }

        $message .= "\n📞 Butuh bantuan? Hubungi kami:\n";
        $message .= $company['phone'] ?? '085183112127';

        $message .= "\n\nTerima kasih,\n*{$companyName}*";

        // Send WhatsApp message
        return $this->sendWhatsAppMessage($customer['telepphone'], $message, $invoice['customer_id']);
    }

    /**
     * Send WhatsApp message using the configured device
     */
    private function sendWhatsAppMessage($phoneNumber, $message, $customerId = null)
    {
        try {
            // Get active WhatsApp device
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->orderBy('id', 'desc')->first();

            if (!$device) {
                log_message('error', 'No WhatsApp device configured');
                return false;
            }

            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Send via API
            $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
            $url = $baseUrl . '/send-message';
            $data = [
                'api_key' => $device['api_key'],
                'sender' => $device['number'],
                'number' => $phoneNumber,
                'message' => $message
            ];

            $queryParams = http_build_query($data);
            $fullUrl = $url . '?' . $queryParams;

            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                log_message('error', "WhatsApp API CURL error: {$curlError}");
                return false;
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['status']) && $result['status'] === true) {
                    log_message('info', "Payment notification sent successfully to {$phoneNumber}");

                    // Log to WhatsApp message log
                    $logModel = new \App\Models\WhatsappMessageLogModel();
                    $logModel->insert([
                        'customer_id' => $customerId,
                        'phone_number' => $phoneNumber,
                        'message' => $message,
                        'status' => 'sent',
                        'type' => 'payment_notification',
                        'response' => json_encode($result),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    return true;
                } else {
                    log_message('error', "WhatsApp API error response: " . ($result['msg'] ?? $result['message'] ?? 'Unknown error'));
                }
            }

            log_message('error', "WhatsApp API error: HTTP {$httpCode}, Response: {$response}");
            return false;
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Replace leading 0 with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Add 62 if not present
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
