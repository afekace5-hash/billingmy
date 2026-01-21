<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\PaymentGatewayModel;
use App\Libraries\Payment\PaymentGatewayFactory;

class PublicBilling extends BaseController
{
    public function index()
    {
        // Load company data
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        // Tampilkan form cek tagihan tanpa detail customer
        return view('public/billing_check', [
            'customer' => null,
            'unpaidInvoices' => [],
            'paidInvoices' => [],
            'activeGateways' => [],
            'error' => null,
            'nomor_layanan' => $this->request->getGet('nomor_layanan'),
            'company' => $company
        ]);
    }
    public function directBilling($customerNumber = null)
    {
        // Jika tidak ada nomor pelanggan, redirect ke halaman index (form pencarian)
        if (!$customerNumber) {
            return redirect()->to('/');
        }

        // Langsung lakukan pencarian tagihan untuk nomor layanan yang diberikan
        return $this->checkBill($customerNumber);
    }

    public function checkBill($customerNumber = null)
    {
        // Jika tidak ada nomor layanan dari URL, ambil dari form GET
        if (!$customerNumber) {
            $customerNumber = $this->request->getGet('nomor_layanan');
        }

        // Jika masih tidak ada nomor layanan, tampilkan form pencarian
        if (!$customerNumber) {
            return $this->index();
        }

        $customerModel = new CustomerModel();
        $invoiceModel = new InvoiceModel();

        // Cari customer berdasarkan nomor layanan dengan join ke tabel package_profiles
        $db = \Config\Database::connect();
        $builder = $db->table('customers c');
        $builder->select('c.*, 
                         c.address as alamat, 
                         c.telepphone as nomor_whatsapp,
                         c.status_tagihan,
                         p.name as package_profile_name,
                         p.bandwidth_profile as package_profile_bandwidth,
                         p.price as package_profile_price');
        $builder->join('package_profiles p', 'p.id = c.id_paket', 'left');
        $builder->where('c.nomor_layanan', $customerNumber);
        $customer = $builder->get()->getRowArray();
        if (!$customer) {
            // Load company data for error page
            $companyModel = new \App\Models\CompanyModel();
            $company = $companyModel->first();

            return view('public/billing_check', [
                'error' => 'Nomor pelanggan ' . $customerNumber . ' tidak ditemukan dalam sistem',
                'customer' => null,
                'unpaidInvoices' => [],
                'paidInvoices' => [],
                'activeGateways' => [],
                'nomor_layanan' => $customerNumber,
                'company' => $company
            ]);
        }

        // Add status_label based on status_tagihan
        $customer['status_label'] = ($customer['status_tagihan'] == 'Lunas') ? 'active' : 'inactive';
        // Add status field that the view expects
        $customer['status'] = $customer['status_label'];
        // Add tarif field from package_profile_price
        $customer['tarif'] = $customer['package_profile_price'] ?? 100000;

        // Ambil tagihan yang belum dibayar - FORCE FRESH DATA
        $unpaidInvoices = $invoiceModel->where('customer_id', $customer['id_customers'])
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Log untuk debugging
        log_message('info', 'Customer ' . $customer['nomor_layanan'] . ' - Unpaid invoices count: ' . count($unpaidInvoices));

        // Ambil tagihan yang sudah dibayar (5 terakhir)
        $paidInvoices = $invoiceModel->where('customer_id', $customer['id_customers'])
            ->where('status', 'paid')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();        // Ambil payment gateway yang aktif
        $paymentModel = new PaymentGatewayModel();
        $activeGateways = $paymentModel->getActiveGateways();

        // Load company data
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        // Set no-cache headers untuk prevent browser caching
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        return view('public/billing_check', [
            'customer' => $customer,
            'unpaidInvoices' => $unpaidInvoices,
            'paidInvoices' => $paidInvoices,
            'activeGateways' => $activeGateways,
            'error' => null,
            'company' => $company
        ]);
    }

    public function payInvoice()
    {
        $request = $this->request;
        $invoiceId = $request->getPost('invoice_id');
        $gateway = $request->getPost('gateway');
        $method = $request->getPost('method');
        $adminFee = (int) ($request->getPost('admin_fee') ?? 0); // Biaya admin dari frontend

        if (!$invoiceId || !$gateway) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        // Log untuk debugging
        log_message('info', 'Public Billing Payment - Invoice ID: ' . $invoiceId . ', Gateway: ' . $gateway . ', Method: ' . $method . ', Admin Fee: ' . $adminFee);

        $invoiceModel = new InvoiceModel();
        $customerModel = new CustomerModel();
        $invoice = $invoiceModel->find($invoiceId);
        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        // Check status with safe access
        $invoiceStatus = $invoice['status'] ?? 'unpaid';
        if ($invoiceStatus !== 'unpaid') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan sudah dibayar atau tidak valid'
            ]);
        }
        $customer = $customerModel->find($invoice['customer_id']);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan'
            ]);
        }        // Debug log customer data
        log_message('info', 'Customer data for payment: ' . json_encode($customer));

        // Validate bill amount
        if (empty($invoice['bill']) || $invoice['bill'] <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Jumlah tagihan tidak valid'
            ]);
        }

        try {
            // Buat payment menggunakan gateway yang dipilih
            $paymentFactory = new PaymentGatewayFactory();
            $paymentService = $paymentFactory->create($gateway);

            if (!$paymentService) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment gateway tidak tersedia'
                ]);
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
                'order_id' => 'INV-' . $invoice['id'] . '-' . time() . '-' . substr(microtime(), 2, 6),
                'amount' => $totalAmount, // Total termasuk admin fee
                'customer_name' => $customer['nama_pelanggan'],
                'customer_email' => $customer['email'] ?: 'customer@example.com',
                'customer_phone' => $customer['telepphone'] ?: '08123456789',
                'method' => $method,
                'description' => 'Tagihan Internet - ' . date('F Y', strtotime($invoice['created_at'])) . ($adminFee > 0 ? ' (termasuk biaya admin Rp ' . number_format($adminFee, 0, ',', '.') . ')' : ''),
                'order_items' => [
                    [
                        'name' => $itemName,
                        'price' => $baseAmount,
                        'quantity' => 1
                    ]
                ],
                'return_url' => base_url($customer['nomor_layanan']) . '?payment_success=1',
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

            // Log payment data untuk debugging
            log_message('info', 'Payment data for Midtrans: ' . json_encode($paymentData));

            $result = $paymentService->createTransaction($paymentData);
            if ($result['success']) {
                // Prepare update data
                $updateData = [
                    'transaction_id' => $result['transaction_id'] ?? '',
                    'payment_gateway' => $gateway,
                    'payment_method' => $method,
                    'payment_url' => $result['payment_url'] ?? ''
                ];

                // Log update data
                log_message('info', 'Updating invoice ' . $invoice['id'] . ' with data: ' . json_encode($updateData));

                // Update invoice dengan transaction_id
                $updateResult = $invoiceModel->update($invoice['id'], $updateData);

                if ($updateResult === false) {
                    log_message('error', 'Failed to update invoice: ' . json_encode($invoiceModel->errors()));
                }

                // Create payment transaction record
                $this->createPaymentTransactionRecord($invoice, $customer, $gateway, $method, $paymentData['order_id'], $result, $adminFee);

                return $this->response->setJSON([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'qr_code' => $result['qr_code'] ?? null,
                    'message' => 'Pembayaran berhasil dibuat'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal membuat pembayaran'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Payment creation error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Create payment transaction record for reporting
     */
    private function createPaymentTransactionRecord($invoice, $customer, $gateway, $method, $orderId, $paymentResult, $adminFee = 0)
    {
        try {
            $paymentTransactionModel = new \App\Models\PaymentTransactionModel();
            $paymentModel = new \App\Models\PaymentGatewayModel();

            $transactionId = $paymentResult['bill_payment_id'] ?? $paymentResult['transaction_id'] ?? $orderId;
            $baseAmount = floatval($invoice['bill'] ?? 0);
            $totalAmount = $baseAmount + floatval($adminFee);

            // Get gateway config for expiry setting
            $gatewayConfig = $paymentModel->getActiveGatewayByType($gateway);
            $expiryHours = isset($gatewayConfig['payment_expiry_hours']) ? (int)$gatewayConfig['payment_expiry_hours'] : 24;
            $expiredAt = date('Y-m-d H:i:s', strtotime('+' . $expiryHours . ' hours'));

            $transactionData = [
                'transaction_code' => $orderId,
                'customer_number' => $customer['nomor_layanan'] ?? '',
                'customer_name' => $customer['nama_pelanggan'] ?? '',
                'payment_method' => $method,
                'channel' => $gateway,
                'biller' => $gateway,
                'amount' => $baseAmount, // Amount tanpa admin fee
                'admin_fee' => floatval($adminFee), // Biaya admin
                'total_amount' => $totalAmount, // Total termasuk admin fee
                'status' => 'pending',
                'payment_code' => $transactionId,
                'transaction_id' => $transactionId,
                'expired_at' => $expiredAt,
                'paid_at' => null,
                'callback_data' => json_encode($paymentResult),
                'notes' => 'Payment created via public billing'
            ];

            $inserted = $paymentTransactionModel->insert($transactionData);

            if ($inserted) {
                log_message('info', 'Payment transaction record created for order: ' . $orderId);
            } else {
                log_message('error', 'Failed to create payment transaction record: ' . json_encode($paymentTransactionModel->errors()));
            }

            return $inserted;
        } catch (\Exception $e) {
            log_message('error', 'Error creating payment transaction record: ' . $e->getMessage());
            return false;
        }
    }
}
