<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\PaymentGatewayModel;
use App\Models\PromoModel;
use App\Libraries\Payment\PaymentGatewayFactory;
use CodeIgniter\Controller;

class CustomerDashboard extends Controller
{
    /**
     * Ambil subdomain dari HTTP_HOST
     * @return string|null
     */
    protected function getSubdomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Contoh: client.difihome.my.id
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            // Ambil bagian paling kiri (subdomain)
            return $parts[0];
        }
        return null;
    }

    /**
     * Override index untuk login via subdomain
     */
    public function subdomainIndex()
    {
        $subdomain = $this->getSubdomain();
        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'difihome' && $subdomain !== 'my') {
            // Cek apakah subdomain valid sebagai customer
            $customer = $this->customerModel->where('subdomain', $subdomain)->first();
            if ($customer) {
                // Bisa langsung redirect ke dashboard jika sudah login
                if ($this->session->get('is_customer_logged_in')) {
                    return redirect()->to(site_url('dashboard'));
                }
                // Tampilkan login khusus customer ini
                return view('customer_dashboard/login', ['customer' => $customer]);
            }
        }
        // Jika bukan subdomain customer, fallback ke login biasa
        return view('customer_dashboard/login');
    }
    protected $customerModel;
    protected $invoiceModel;
    protected $paymentModel;
    protected $promoModel;
    protected $session;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->invoiceModel = new InvoiceModel();
        $this->paymentModel = new PaymentGatewayModel();
        $this->promoModel = new PromoModel();
        $this->session = \Config\Services::session();
    }

    /**
     * Landing page login untuk customer
     */
    public function index()
    {
        return view('customer_dashboard/login');
    }

    /**
     * Proses login customer dengan nomor layanan atau WhatsApp
     */
    public function login()
    {
        $credentials = $this->request->getPost('credentials');

        if (!$credentials) {
            return redirect()->back()->with('error', 'Nomor layanan atau WhatsApp harus diisi');
        }

        // Cari customer berdasarkan nomor layanan atau WhatsApp
        $customer = $this->customerModel->where('nomor_layanan', $credentials)
            ->orWhere('telepphone', $credentials)
            ->first();

        if (!$customer) {
            return redirect()->back()->with('error', 'Nomor layanan atau WhatsApp tidak ditemukan');
        }

        // Set session customer
        $customerData = [
            'customer_id' => $customer['id_customers'],
            'customer_name' => $customer['nama_pelanggan'],
            'customer_number' => $customer['nomor_layanan'],
            'customer_phone' => $customer['telepphone'],
            'is_customer_logged_in' => true
        ];

        $this->session->set($customerData);

        return redirect()->to(site_url('customer-portal/dashboard'));
    }

    /**
     * Dashboard utama customer
     */
    public function dashboard()
    {
        if (!$this->isCustomerLoggedIn()) {
            return redirect()->to(site_url('customer-portal'))->with('error', 'Silakan login terlebih dahulu');
        }

        $customerId = $this->session->get('customer_id');

        // Get customer detail with package info
        $db = \Config\Database::connect();
        $builder = $db->table('customers c');
        $customer = $builder->select('c.*, p.name as package_name, p.price as package_price, 
                                    l.name as server_name')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->join('lokasi_server l', 'l.id_lokasi = c.id_lokasi_server', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        // Get invoice statistics
        $unpaidCount = $this->invoiceModel->where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->countAllResults();

        $totalUnpaidResult = $this->invoiceModel->selectSum('bill')
            ->where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->get()
            ->getRow();

        $totalUnpaid = (float) ($totalUnpaidResult->bill ?? 0);

        $lastPayment = $this->invoiceModel->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->orderBy('payment_date', 'DESC')
            ->first();

        // Get recent invoices
        $recentInvoices = $this->invoiceModel->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Get unpaid invoices for payment modal - EXPLICITLY select id field
        $unpaidInvoices = $this->invoiceModel
            ->select('id, customer_id, invoice_no, periode, bill, arrears, additional_fee, discount, status, created_at')
            ->where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Debug: Log first invoice structure
        if (!empty($unpaidInvoices)) {
            log_message('debug', 'First unpaid invoice keys: ' . implode(', ', array_keys($unpaidInvoices[0])));
            log_message('debug', 'First unpaid invoice ID: ' . ($unpaidInvoices[0]['id'] ?? 'NOT FOUND'));
        }

        // Get active payment gateway configurations (not methods)
        $activeGateways = $this->paymentModel->getActiveGatewayConfigs();

        // Get Midtrans configuration for snap.js
        $midtransConfig = $this->paymentModel->getActiveGatewayByType('midtrans');

        // Get active promos for customer dashboard
        $activePromos = $this->promoModel->getActivePromos();

        return view('customer_dashboard/dashboard', [
            'customer' => $customer,
            'unpaid_count' => $unpaidCount,
            'total_unpaid' => $totalUnpaid,
            'last_payment' => $lastPayment,
            'recent_invoices' => $recentInvoices,
            'unpaid_invoices' => $unpaidInvoices,
            'active_gateways' => $activeGateways,
            'midtrans_config' => $midtransConfig,
            'active_promos' => $activePromos
        ]);
    }

    /**
     * Halaman tagihan customer
     */
    public function invoices()
    {
        if (!$this->isCustomerLoggedIn()) {
            return redirect()->to(site_url('customer-portal'))->with('error', 'Silakan login terlebih dahulu');
        }

        $customerId = $this->session->get('customer_id');

        // Get all invoices
        $invoices = $this->invoiceModel->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Separate paid and unpaid
        $unpaidInvoices = array_filter($invoices, function ($invoice) {
            return $invoice['status'] === 'unpaid';
        });

        $paidInvoices = array_filter($invoices, function ($invoice) {
            return $invoice['status'] === 'paid';
        });

        // Get active payment gateways
        $activeGateways = $this->paymentModel->getActiveGateways();

        return view('customer_dashboard/invoices', [
            'unpaid_invoices' => $unpaidInvoices,
            'paid_invoices' => $paidInvoices,
            'active_gateways' => $activeGateways
        ]);
    }

    /**
     * Proses pembayaran invoice
     */
    public function payInvoice()
    {
        if (!$this->isCustomerLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $invoiceId = $this->request->getPost('invoice_id');
        $gateway = $this->request->getPost('gateway');
        $method = $this->request->getPost('method');
        $adminFee = (int) ($this->request->getPost('admin_fee') ?? 0); // Biaya admin dari frontend

        log_message('info', 'PayInvoice called - Invoice: ' . $invoiceId . ', Gateway: ' . $gateway . ', Method: ' . $method . ', Admin Fee: ' . $adminFee);

        if (!$invoiceId || !$gateway) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        // Verify invoice belongs to logged in customer
        $customerId = $this->session->get('customer_id');
        $invoice = $this->invoiceModel->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        if ($invoice['status'] === 'paid') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan sudah dibayar'
            ]);
        }

        // Get customer data
        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan'
            ]);
        }

        // Validate bill amount
        if (empty($invoice['bill']) || $invoice['bill'] <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Jumlah tagihan tidak valid'
            ]);
        }

        try {
            // Create payment using selected gateway
            $paymentFactory = new PaymentGatewayFactory();
            $paymentService = $paymentFactory->create($gateway);

            if (!$paymentService) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment gateway tidak tersedia'
                ]);
            }

            // Prepare email - ensure valid format
            $customerEmail = $customer['email'];
            if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $customerEmail = 'noreply@billing.com';
            }

            // Prepare phone - ensure valid format
            $customerPhone = $customer['telepphone'];
            if (empty($customerPhone)) {
                $customerPhone = '08123456789';
            }

            // Calculate total amount (bill + admin fee)
            $baseAmount = (int) $invoice['bill'];
            $totalAmount = $baseAmount + $adminFee;

            // Batasi panjang nama item (Midtrans max 50 karakter)
            $itemName = 'Tagihan Internet - ' . date('F Y', strtotime($invoice['created_at']));
            if (strlen($itemName) > 50) {
                $itemName = substr($itemName, 0, 47) . '...';
            }

            $paymentData = [
                'order_id' => 'INV-' . $invoice['id'] . '-' . time(),
                'amount' => $totalAmount, // Total termasuk admin fee
                'customer_name' => $customer['nama_pelanggan'],
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'method' => $method,
                'description' => 'Tagihan Internet - ' . date('F Y', strtotime($invoice['created_at'])) . ($adminFee > 0 ? ' (termasuk biaya admin Rp ' . number_format($adminFee, 0, ',', '.') . ')' : ''),
                'order_items' => [
                    [
                        'name' => $itemName,
                        'price' => $baseAmount,
                        'quantity' => 1
                    ]
                ],
                'return_url' => site_url('customer-portal/invoices?payment_success=1'),
                'callback_url' => base_url('payment/callback/' . $gateway)
            ];

            // Tambahkan biaya admin sebagai item terpisah jika ada
            if ($adminFee > 0) {
                $paymentData['order_items'][] = [
                    'name' => 'Biaya Admin',
                    'price' => $adminFee,
                    'quantity' => 1
                ];
            }

            log_message('info', '=== PAYMENT REQUEST DEBUG ===');
            log_message('info', 'Selected Gateway: ' . $gateway);
            log_message('info', 'Selected Method Code: ' . ($method ?? 'NULL'));
            log_message('info', 'Payment data for ' . $gateway . ': ' . json_encode($paymentData));
            log_message('info', 'Gateway service class: ' . get_class($paymentService));

            $result = $paymentService->createTransaction($paymentData);

            log_message('info', 'Payment result: ' . json_encode($result));

            if ($result['success']) {
                // Update invoice with transaction info
                $updateData = [
                    'transaction_id' => $result['transaction_id'] ?? '',
                    'payment_gateway' => $gateway,
                    'payment_method' => $method,
                    'payment_url' => $result['payment_url'] ?? ''
                ];

                log_message('info', 'Updating invoice ' . $invoice['id'] . ' with data: ' . json_encode($updateData));

                $this->invoiceModel->update($invoice['id'], $updateData);

                return $this->response->setJSON([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'qr_code' => $result['qr_code'] ?? null,
                    'message' => 'Pembayaran berhasil dibuat'
                ]);
            } else {
                log_message('error', 'Payment creation failed: ' . json_encode($result));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal membuat pembayaran'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Payment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Profile customer
     */
    public function profile()
    {
        if (!$this->isCustomerLoggedIn()) {
            return redirect()->to(site_url('customer-portal'))->with('error', 'Silakan login terlebih dahulu');
        }

        $customerId = $this->session->get('customer_id');

        // Get customer detail with package info
        $db = \Config\Database::connect();
        $builder = $db->table('customers c');
        $customer = $builder->select('c.*, p.name as package_name, p.price as package_price, 
                                    l.name as server_name')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->join('lokasi_server l', 'l.id_lokasi = c.id_lokasi_server', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        return view('customer_dashboard/profile', [
            'customer' => $customer
        ]);
    }

    /**
     * Logout customer
     */
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(site_url('customer-portal'))->with('success', 'Logout berhasil');
    }

    /**
     * Check if customer is logged in
     */
    private function isCustomerLoggedIn()
    {
        return $this->session->get('is_customer_logged_in') === true;
    }

    /**
     * Check session for AJAX calls
     */
    public function checkSession()
    {
        if ($this->isCustomerLoggedIn()) {
            return $this->response->setJSON(['status' => true]);
        } else {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
    }

    /**
     * API endpoint to get invoice details for payment
     */
    public function getInvoiceDetails($invoiceId)
    {
        if (!$this->isCustomerLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $customerId = $this->session->get('customer_id');
        $invoice = $this->invoiceModel->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Process payment
     */
    public function processPayment()
    {
        if (!$this->isCustomerLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $json = $this->request->getJSON();
        $invoiceId = $json->invoice_id ?? null;
        $gateway = $json->gateway ?? null;
        $paymentCode = $json->payment_code ?? null;
        $amount = $json->amount ?? 0;
        $adminFee = $json->admin_fee ?? 0; // Biaya admin dari frontend

        // Debug logging
        log_message('debug', 'Process Payment - Invoice: ' . $invoiceId . ', Gateway: ' . $gateway . ', Code: ' . $paymentCode . ', Amount: ' . $amount . ', Admin Fee: ' . $adminFee);

        if (!$invoiceId || !$gateway || !$paymentCode || !$amount) {
            log_message('debug', 'Incomplete data - Invoice: ' . var_export($invoiceId, true) . ', Gateway: ' . var_export($gateway, true) . ', Code: ' . var_export($paymentCode, true) . ', Amount: ' . var_export($amount, true));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap - Invoice: ' . ($invoiceId ? 'OK' : 'KOSONG') . ', Gateway: ' . ($gateway ? 'OK' : 'KOSONG') . ', Code: ' . ($paymentCode ? 'OK' : 'KOSONG') . ', Amount: ' . ($amount ? 'OK' : 'KOSONG')
            ]);
        }

        $customerId = $this->session->get('customer_id');
        $invoice = $this->invoiceModel->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        // Process payment based on gateway
        if ($gateway === 'midtrans') {
            return $this->processMidtransPayment($invoice, $paymentCode, $amount, $adminFee);
        } elseif ($gateway === 'duitku') {
            return $this->processDuitkuPayment($invoice, $paymentCode, $amount, $adminFee);
        } elseif ($gateway === 'flip') {
            return $this->processFlipPayment($invoice, $paymentCode, $amount, $adminFee);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gateway tidak didukung'
        ]);
    }

    private function processMidtransPayment($invoice, $paymentCode, $amount, $adminFee = 0)
    {
        try {
            $gatewayConfig = $this->paymentModel->getActiveGatewayByType('midtrans');

            if (!$gatewayConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway Midtrans tidak aktif'
                ]);
            }

            // Log configuration
            log_message('debug', 'Midtrans Config - Server Key: ' . substr($gatewayConfig['api_key'], 0, 10) . '..., Environment: ' . $gatewayConfig['environment']);
            log_message('debug', 'Midtrans Payment - Amount: ' . $amount . ', Admin Fee: ' . $adminFee);

            // Set Midtrans configuration
            \Midtrans\Config::$serverKey = $gatewayConfig['api_key'];
            \Midtrans\Config::$isProduction = ($gatewayConfig['environment'] === 'production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Initialize curlOptions as empty array first to prevent undefined key error
            if (!is_array(\Midtrans\Config::$curlOptions)) {
                \Midtrans\Config::$curlOptions = [];
            }

            // Disable SSL verification for development
            \Midtrans\Config::$curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            \Midtrans\Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;

            // Hitung base amount (amount tanpa admin fee)
            $baseAmount = (int) $amount - (int) $adminFee;

            // Batasi panjang nama item (Midtrans max 50 karakter)
            $itemName = 'Tagihan Internet - ' . $invoice['periode'];
            if (strlen($itemName) > 50) {
                $itemName = substr($itemName, 0, 47) . '...';
            }

            // Batasi panjang invoice_no sebagai id (Midtrans max 50 karakter)
            $itemId = $invoice['invoice_no'];
            if (strlen($itemId) > 50) {
                $itemId = substr($itemId, 0, 50);
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $invoice['invoice_no'] . '-' . time(),
                    'gross_amount' => (int) $amount, // Total amount sudah termasuk admin fee
                ],
                'customer_details' => [
                    'first_name' => $this->session->get('customer_name'),
                    'phone' => $this->session->get('customer_phone'),
                ],
                'item_details' => [
                    [
                        'id' => $itemId,
                        'price' => $baseAmount,
                        'quantity' => 1,
                        'name' => $itemName
                    ]
                ],
            ];

            // Tambahkan biaya admin sebagai item terpisah jika ada
            if ($adminFee > 0) {
                $params['item_details'][] = [
                    'id' => 'admin-fee',
                    'price' => (int) $adminFee,
                    'quantity' => 1,
                    'name' => 'Biaya Admin' // Singkat, max 50 karakter
                ];
            }

            log_message('debug', 'Midtrans params: ' . json_encode($params));

            try {
                $snapToken = \Midtrans\Snap::getSnapToken($params);

                log_message('debug', 'Midtrans snap token generated: ' . substr($snapToken, 0, 20) . '...');

                return $this->response->setJSON([
                    'success' => true,
                    'snap_token' => $snapToken,
                    'message' => 'Silakan lanjutkan pembayaran'
                ]);
            } catch (\Throwable $snapError) {
                log_message('error', 'Midtrans Snap error: ' . $snapError->getMessage());
                log_message('error', 'Midtrans Snap error trace: ' . $snapError->getTraceAsString());

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error koneksi ke Midtrans. Silakan coba lagi.'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Midtrans payment error: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.'
            ]);
        }
    }

    private function processDuitkuPayment($invoice, $paymentCode, $amount, $adminFee = 0)
    {
        try {
            $gatewayConfig = $this->paymentModel->getActiveGatewayByType('duitku');

            if (!$gatewayConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway Duitku tidak aktif'
                ]);
            }

            $customer = $this->customerModel->find($invoice['customer_id']);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan'
                ]);
            }

            // Log payment details
            log_message('debug', 'Duitku Payment - Amount: ' . $amount . ', Admin Fee: ' . $adminFee);

            // Initialize Duitku service
            $duitkuService = new \App\Libraries\Payment\DuitkuService($gatewayConfig);

            // Prepare transaction data
            $transactionData = [
                'order_id' => $invoice['invoice_no'],
                'amount' => (int) $amount, // Total amount sudah termasuk admin fee
                'method' => $paymentCode, // Payment method code dari Duitku
                'customer_name' => $customer['nama_pelanggan'],
                'customer_email' => $customer['email'] ?? 'customer@example.com',
                'customer_phone' => $customer['no_hp'] ?? '',
                'description' => 'Pembayaran Invoice ' . $invoice['invoice_no'] . ($adminFee > 0 ? ' (termasuk biaya admin Rp ' . number_format($adminFee, 0, ',', '.') . ')' : ''),
                'callback_url' => base_url('payment/callback/duitku'),
                'return_url' => base_url('customer/dashboard')
            ];

            // Create transaction
            $result = $duitkuService->createTransaction($transactionData);

            if ($result['success']) {
                // Hitung base amount (amount tanpa admin fee)
                $baseAmount = (int) $amount - (int) $adminFee;

                // Get expiry time from gateway config
                $expiryHours = isset($gatewayConfig['payment_expiry_hours']) ? (int)$gatewayConfig['payment_expiry_hours'] : 24;
                $expiredAt = date('Y-m-d H:i:s', strtotime('+' . $expiryHours . ' hours'));

                // Save payment record
                $paymentData = [
                    'invoice_id' => $invoice['id'],
                    'customer_id' => $invoice['customer_id'],
                    'transaction_code' => $invoice['invoice_no'],
                    'customer_number' => $customer['nomor_layanan'] ?? '',
                    'customer_name' => $customer['nama_pelanggan'] ?? '',
                    'payment_gateway' => 'duitku',
                    'payment_method' => $paymentCode,
                    'channel' => 'duitku',
                    'biller' => 'BILLING SYSTEM',
                    'transaction_id' => $result['transaction_id'] ?? '',
                    'amount' => $baseAmount, // Amount tanpa admin fee
                    'admin_fee' => (int) $adminFee, // Biaya admin
                    'total_amount' => (int) $amount, // Total termasuk admin fee
                    'status' => 'pending',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'response_data' => json_encode($result['data']),
                    'payment_code' => $result['transaction_id'] ?? '',
                    'expired_at' => $expiredAt
                ];

                $paymentTransactionModel = new \App\Models\PaymentTransactionModel();
                $paymentTransactionModel->insert($paymentData);

                return $this->response->setJSON([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'transaction_id' => $result['transaction_id'],
                    'va_number' => $result['va_number'] ?? null
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal membuat transaksi Duitku'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Duitku payment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available payment methods from Duitku
     */
    public function getDuitkuPaymentMethods()
    {
        try {
            $gatewayConfig = $this->paymentModel->getActiveGatewayByType('duitku');

            if (!$gatewayConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway Duitku tidak aktif'
                ]);
            }

            $duitkuService = new \App\Libraries\Payment\DuitkuService($gatewayConfig);
            $methods = $duitkuService->getAvailablePaymentMethods();

            return $this->response->setJSON([
                'success' => true,
                'methods' => $methods
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test Duitku connection
     */
    public function testDuitkuConnection()
    {
        try {
            $gatewayConfig = $this->paymentModel->getActiveGatewayByType('duitku');

            if (!$gatewayConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway Duitku tidak aktif',
                    'config' => 'Not found'
                ]);
            }

            // Log config (hide sensitive data)
            log_message('info', 'Duitku Config - Merchant Code: ' . ($gatewayConfig['merchant_code'] ?? 'NOT SET') . ', API Key: ' . (isset($gatewayConfig['api_key']) ? substr($gatewayConfig['api_key'], 0, 10) . '...' : 'NOT SET'));

            $duitkuService = new \App\Libraries\Payment\DuitkuService($gatewayConfig);
            $result = $duitkuService->testConnection();

            return $this->response->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'],
                'config_status' => [
                    'merchant_code' => !empty($gatewayConfig['merchant_code']),
                    'api_key' => !empty($gatewayConfig['api_key']),
                    'environment' => $gatewayConfig['environment'] ?? 'not set'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Test Duitku error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process Flip payment
     */
    private function processFlipPayment($invoice, $paymentCode, $amount, $adminFee = 0)
    {
        try {
            $gatewayConfig = $this->paymentModel->getActiveGatewayByType('flip');

            if (!$gatewayConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway Flip tidak aktif'
                ]);
            }

            $customer = $this->customerModel->find($invoice['customer_id']);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan'
                ]);
            }

            // Log payment details
            log_message('debug', 'Flip Payment - Amount: ' . $amount . ', Admin Fee: ' . $adminFee);

            // Initialize Flip service
            $flipService = new \App\Libraries\Payment\FlipService($gatewayConfig);

            // Prepare transaction data
            $transactionData = [
                'order_id' => $invoice['invoice_no'],
                'amount' => (int) $amount, // Total amount sudah termasuk admin fee
                'method' => $paymentCode, // Payment method code dari Flip
                'customer_name' => $customer['nama_pelanggan'],
                'customer_email' => $customer['email'] ?? 'customer@example.com',
                'customer_phone' => $customer['no_hp'] ?? '',
                'description' => 'Pembayaran Invoice ' . $invoice['invoice_no'] . ($adminFee > 0 ? ' (termasuk biaya admin Rp ' . number_format($adminFee, 0, ',', '.') . ')' : ''),
                'callback_url' => base_url('payment/callback/flip'),
                'return_url' => base_url('customer/dashboard')
            ];

            // Create transaction
            $result = $flipService->createTransaction($transactionData);

            if ($result['success']) {
                // Hitung base amount (amount tanpa admin fee)
                $baseAmount = (int) $amount - (int) $adminFee;

                // Get expiry time from gateway config
                $expiryHours = isset($gatewayConfig['payment_expiry_hours']) ? (int)$gatewayConfig['payment_expiry_hours'] : 24;
                $expiredAt = date('Y-m-d H:i:s', strtotime('+' . $expiryHours . ' hours'));

                // Save payment record
                $transactionId = $result['bill_payment_id'] ?? $result['transaction_id'] ?? '';
                $paymentData = [
                    'invoice_id' => $invoice['id'],
                    'customer_id' => $invoice['customer_id'],
                    'transaction_code' => $invoice['invoice_no'],
                    'customer_number' => $customer['nomor_layanan'] ?? '',
                    'customer_name' => $customer['nama_pelanggan'] ?? '',
                    'payment_gateway' => 'flip',
                    'payment_method' => $paymentCode,
                    'channel' => 'flip',
                    'biller' => 'BILLING SYSTEM',
                    'transaction_id' => $transactionId,
                    'amount' => $baseAmount, // Amount tanpa admin fee
                    'admin_fee' => (int) $adminFee, // Biaya admin
                    'total_amount' => (int) $amount, // Total termasuk admin fee
                    'status' => 'pending',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'response_data' => json_encode($result['data']),
                    'payment_code' => $transactionId,
                    'expired_at' => $expiredAt
                ];

                $paymentTransactionModel = new \App\Models\PaymentTransactionModel();
                $paymentTransactionModel->insert($paymentData);

                return $this->response->setJSON([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'transaction_id' => $transactionId,
                    'va_number' => $result['va_number'] ?? null
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal membuat transaksi Flip'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Flip payment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get invoice detail with customer information
     */
    public function getInvoiceDetail($invoiceId = null)
    {
        if (!$this->isCustomerLoggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        if (!$invoiceId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice ID tidak ditemukan'
            ]);
        }

        $customerId = $this->session->get('customer_id');

        // Get invoice details
        $invoice = $this->invoiceModel->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        // Get customer details with package info
        $db = \Config\Database::connect();
        $builder = $db->table('customers c');
        $customer = $builder->select('c.*, p.name as package_name, p.price as package_price')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        // Add package name to invoice for display
        if ($customer && isset($customer['package_name'])) {
            $invoice['package_name'] = $customer['package_name'];
        }

        return $this->response->setJSON([
            'success' => true,
            'invoice' => $invoice,
            'customer' => $customer
        ]);
    }

    /**
     * Download/Print invoice untuk customer
     */
    public function downloadInvoice($invoiceId = null)
    {
        if (!$this->isCustomerLoggedIn()) {
            return redirect()->to(site_url('customer-portal'))->with('error', 'Silakan login terlebih dahulu');
        }

        if (!$invoiceId) {
            return redirect()->back()->with('error', 'Invoice ID tidak ditemukan');
        }

        $customerId = $this->session->get('customer_id');

        // Get invoice details - pastikan invoice milik customer yang login dan sudah dibayar
        $invoice = $this->invoiceModel->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice tidak ditemukan atau belum dibayar');
        }

        // Get customer details dengan package info
        $db = \Config\Database::connect();
        $builder = $db->table('customers c');
        $customer = $builder->select('c.*, p.name as package_name, p.price as package_price')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        // Get active bank accounts untuk ditampilkan di invoice
        $bankModel = new \App\Models\BankModel();
        $activeBanks = $bankModel->where('is_active', 1)->findAll();

        // Calculate total
        $total = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];

        // Prepare data untuk view (format sama dengan admin)
        $data = [
            'invoice' => (object) [
                'invoice_no' => $invoice['invoice_no'],
                'periode' => $invoice['periode'],
                'bill' => (float)($invoice['bill'] ?? 0),
                'arrears' => (float)($invoice['arrears'] ?? 0),
                'additional_fee' => (float)($invoice['additional_fee'] ?? 0),
                'discount' => (float)($invoice['discount'] ?? 0),
                'package' => $invoice['package'] ?? $customer['package_name'],
                'status' => $invoice['status'],
                'paid_at' => $invoice['payment_date'] ?? $invoice['updated_at'],
                'customer_no' => $customer['nomor_layanan'] ?? '-',
                'customer_name' => $customer['nama_pelanggan'] ?? '-',
                'customer_address' => $customer['address'] ?? '-',
                'customer_phone' => $customer['telepphone'] ?? '-',
                'usage_period' => $this->getUsagePeriod($invoice['periode']),
                'keterangan' => $invoice['keterangan'] ?? '-',
                'payment_url' => base_url($customer['nomor_layanan'] ?? '')
            ],
            'activeBanks' => $activeBanks,
            'is_customer_portal' => true
        ];

        // Return view khusus untuk customer (tanpa layout admin)
        return view('customer_dashboard/print_invoice', $data);
    }

    /**
     * Helper untuk periode pemakaian
     */
    private function getUsagePeriod($periode)
    {
        // Format: "2024-01" -> "01 Januari 2024 - 31 Januari 2024"
        if (empty($periode)) {
            return '-';
        }

        try {
            $date = new \DateTime($periode . '-01');
            $startDate = $date->format('01 F Y');
            $endDate = $date->format('t F Y');

            // Translate month names to Indonesian
            $months = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];

            $startDate = str_replace(array_keys($months), array_values($months), $startDate);
            $endDate = str_replace(array_keys($months), array_values($months), $endDate);

            return $startDate . ' - ' . $endDate;
        } catch (\Exception $e) {
            return $periode;
        }
    }
}
