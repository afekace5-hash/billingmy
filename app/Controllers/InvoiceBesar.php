<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Controller untuk Invoice Besar (Standalone Invoice Menu)
 * Menangani invoice generation dan management untuk semua customer
 * @param string|null $periode (format: YYYY-MM)
 * @return array
 */
class InvoiceBesar extends Controller
{
    protected $invoiceModel;
    protected $customerModel;
    protected $packageModel;

    public function __construct()
    {
        $this->invoiceModel = model('InvoiceModel');
        $this->customerModel = model('CustomerModel');
        $this->packageModel = model('PackageProfileModel');
    }

    /**
     * Get active payment methods
     */
    public function getPaymentMethods()
    {
        try {
            $paymentModel = model('PaymentGatewayModel');
            $methods = $paymentModel->getActiveGateways();

            return $this->response->setJSON([
                'success' => true,
                'data' => $methods
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get payment methods error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat metode pembayaran',
                'data' => []
            ]);
        }
    }

    /**
     * Save payment method for invoice
     */
    public function savePaymentMethod()
    {
        try {
            $invoiceId = $this->request->getPost('invoice_id');
            $paymentMethod = $this->request->getPost('payment_method');
            $methodCode = $this->request->getPost('method_code');
            $paymentGateway = $this->request->getPost('payment_gateway');
            $sendNotification = $this->request->getPost('send_notification');

            if (!$invoiceId || !$paymentMethod || !$methodCode || !$paymentGateway) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak lengkap'
                ]);
            }

            // Get invoice data
            $invoice = $this->invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ]);
            }

            // Get customer data
            $customer = $this->customerModel->find($invoice['customer_id']);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Initialize payment request log
            $logModel = new \App\Models\PaymentRequestLogModel();
            $logData = [
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoice['invoice_no'],
                'customer_id' => $customer['id_customers'],
                'customer_name' => $customer['nama_pelanggan'],
                'payment_gateway' => $paymentGateway,
                'payment_method' => $paymentMethod,
                'method_code' => $methodCode,
                'amount' => $invoice['bill'],
                'status' => 'pending',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
            ];

            // Get payment gateway configuration
            $paymentGatewayModel = model('PaymentGatewayModel');
            $gateway = $paymentGatewayModel->where('gateway_type', $paymentGateway)
                ->where('is_active', 1)
                ->first();

            log_message('info', 'Payment Gateway Type: ' . $paymentGateway);
            log_message('info', 'Gateway Found: ' . ($gateway ? 'Yes' : 'No'));

            if (!$gateway) {
                $logData['status'] = 'failed';
                $logData['error_message'] = 'Payment gateway tidak ditemukan atau tidak aktif: ' . $paymentGateway;
                $logModel->insert($logData);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment gateway tidak ditemukan atau tidak aktif: ' . $paymentGateway
                ]);
            }

            // Create payment via gateway
            $paymentService = null;
            if (strtolower($paymentGateway) === 'midtrans') {
                $paymentService = new \App\Libraries\Payment\MidtransService($gateway);
            } elseif (strtolower($paymentGateway) === 'duitku') {
                $paymentService = new \App\Libraries\Payment\DuitkuService($gateway);
            } elseif (strtolower($paymentGateway) === 'flip') {
                $paymentService = new \App\Libraries\Payment\FlipService($gateway);
            }

            log_message('info', 'Payment Service Created: ' . ($paymentService ? 'Yes' : 'No'));

            if (!$paymentService) {
                $logData['status'] = 'failed';
                $logData['error_message'] = 'Payment gateway tidak didukung: ' . $paymentGateway;
                $logModel->insert($logData);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment gateway tidak didukung: ' . $paymentGateway
                ]);
            }

            // Prepare transaction data
            $transactionData = [
                'order_id' => 'INV-' . $invoice['id'] . '-' . time(),
                'amount' => $invoice['bill'],
                'customer_name' => $customer['nama_pelanggan'] ?? $invoice['customer_name'],
                'customer_email' => $customer['email'] ?? '',
                'customer_phone' => $customer['telepphone'] ?? '',
                'item_details' => [
                    [
                        'id' => $invoice['id'],
                        'name' => $invoice['package'] ?? 'Tagihan Internet',
                        'price' => $invoice['bill'],
                        'quantity' => 1
                    ]
                ],
                'payment_type' => $methodCode
            ];

            // Save request data to log
            $logData['request_data'] = json_encode($transactionData);

            log_message('info', 'Creating transaction with data: ' . json_encode($transactionData));

            // Gunakan SNAP API - customer akan redirect untuk dapat kode pembayaran
            $paymentResult = $paymentService->createTransaction($transactionData);

            // Save response data to log
            $logData['response_data'] = json_encode($paymentResult);

            if (!$paymentResult['success']) {
                log_message('error', 'Payment Failed: ' . ($paymentResult['message'] ?? 'Unknown error'));

                $logData['status'] = 'failed';
                $logData['error_message'] = $paymentResult['message'] ?? 'Unknown error';
                $logModel->insert($logData);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal membuat pembayaran: ' . ($paymentResult['message'] ?? 'Unknown error')
                ]);
            }

            // Tentukan payment code berdasarkan gateway
            if (strtolower($paymentGateway) === 'flip') {
                // Flip: langsung tampilkan VA number/kode bayar
                $paymentCode = $paymentResult['payment_code'] ?? 'Lihat detail pembayaran';
            } else {
                // SNAP API untuk Midtrans/Duitku: customer harus klik tombol untuk ke halaman pembayaran
                $paymentCode = 'Klik tombol "Pilih & Bayar" untuk mendapatkan nomor VA';
            }

            $paymentUrl = $paymentResult['payment_url'] ?? '';
            $transactionId = $paymentResult['transaction_id'] ?? '';

            log_message('info', 'Payment URL: ' . $paymentUrl);
            log_message('info', 'Transaction ID: ' . $transactionId);

            // Update log with success status
            $logData['status'] = 'success';
            $logData['payment_code'] = $paymentCode;
            $logData['payment_url'] = $paymentUrl;
            $logData['transaction_id'] = $transactionId;
            $logModel->insert($logData);

            // Update invoice with payment method info
            $updateData = [
                'payment_method' => $paymentMethod,
                'payment_code' => $paymentCode,
                'payment_gateway' => $paymentGateway,
                'payment_url' => $paymentUrl,
                'payment_expired_at' => $paymentResult['expired_at'] ?? $paymentResult['data']['expired_at'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->invoiceModel->update($invoiceId, $updateData);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Metode pembayaran berhasil disimpan',
                    'data' => [
                        'payment_code' => $paymentCode,
                        'payment_url' => $paymentUrl,
                        'gateway' => $paymentGateway
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan metode pembayaran'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Save payment method error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Invoice'
        ];

        return view('invoice/index', $data);
    }

    public function data()
    {
        try {
            $request = \Config\Services::request();
            $db = \Config\Database::connect();

            $draw = $request->getPost('draw') ?? 1;
            $start = $request->getPost('start') ?? 0;
            $length = $request->getPost('length') ?? 10;
            $searchValue = $request->getPost('search')['value'] ?? '';
            $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

            // Get filter parameters
            $filterStatus = $request->getPost('filter_status') ?? '';
            $filterType = $request->getPost('filter_type') ?? '';
            $filterMonth = $request->getPost('filter_month') ?? '';
            $filterIsPaid = $request->getPost('filter_is_paid') ?? '';

            $columns = ['ci.id', 'ci.invoice_no', 'ci.customer_id', 'ci.bill', 'ci.status', 'ci.paid_amount', 'ci.periode', 'ci.created_at'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'ci.id';

            // Base query
            $builder = $db->table('customer_invoices ci');
            $builder->select('ci.id, ci.invoice_no, ci.customer_id, ci.bill as total_amount, ci.status as status_payment, 
                             ci.paid_amount, ci.periode as invoice_month, ci.created_at, ci.is_prorata,
                             c.nama_pelanggan as customer_name, c.tgl_tempo as installed_at, c.status_tagihan,
                             pp.name as package_name');
            $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
            $builder->join('package_profiles pp', 'pp.id = c.id_paket', 'left');

            // Filter untuk bulan berjalan (jika tidak ada filter month)
            if (empty($filterMonth)) {
                $currentMonth = date('Y-m'); // Format: 2025-12
                $builder->where('ci.periode', $currentMonth);
            } else {
                $builder->where('ci.periode', $filterMonth);
            }

            // Apply filters
            if (!empty($filterStatus)) {
                $builder->where('ci.status', $filterStatus);
            }

            // Filter berdasarkan type (Bulanan, Instalasi, Prorate)
            if (!empty($filterType)) {
                if ($filterType == 'Prorate') {
                    $builder->where('ci.is_prorata', 1);
                } elseif ($filterType == 'Instalasi') {
                    $builder->where('c.status_tagihan', 'Instalasi');
                    $builder->where('ci.is_prorata', 0);
                } elseif ($filterType == 'Bulanan') {
                    $builder->where('c.status_tagihan !=', 'Instalasi');
                    $builder->where('ci.is_prorata', 0);
                }
            }

            // Search
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('ci.invoice_no', $searchValue)
                    ->orLike('c.nama_pelanggan', $searchValue)
                    ->orLike('pp.name', $searchValue)
                    ->orLike('ci.periode', $searchValue)
                    ->groupEnd();
            }

            // Count total before pagination
            $totalRecords = $builder->countAllResults(false);

            // Apply ordering and pagination
            $builder->orderBy($orderColumn, $orderDir);
            $builder->limit($length, $start);

            $invoices = $builder->get()->getResultArray();

            // Format data
            foreach ($invoices as &$invoice) {
                // Calculate is_paid and has_claimed
                $invoice['is_paid'] = ($invoice['paid_amount'] >= $invoice['total_amount']) ? 1 : 0;
                $invoice['has_claimed'] = 0; // Default value

                // Apply is_paid filter if set
                if ($filterIsPaid !== '' && $invoice['is_paid'] != $filterIsPaid) {
                    continue;
                }

                // Determine invoice type based on status_tagihan and is_prorata
                if ($invoice['is_prorata'] == 1) {
                    $invoice['type'] = 'Prorate';
                } elseif ($invoice['status_tagihan'] == 'Instalasi') {
                    $invoice['type'] = 'Instalasi';
                } else {
                    $invoice['type'] = 'Bulanan';
                }

                // Format amounts and dates
                $invoice['total'] = 'Rp. ' . number_format($invoice['total_amount'], 0, ',', '.');
                $invoice['installed_at'] = $invoice['installed_at'] ? date('d M Y H:i', strtotime($invoice['installed_at'])) : '-';
                $invoice['customer_name'] = $invoice['customer_name'] ?? '-';
                $invoice['package_name'] = $invoice['package_name'] ?? '-';
                $invoice['invoice_number'] = $invoice['invoice_no'] ?? '-';

                // Format invoice_month dari 2025-12 menjadi Desember 2025
                if (!empty($invoice['invoice_month'])) {
                    $monthNames = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                    ];
                    $parts = explode('-', $invoice['invoice_month']);
                    if (count($parts) == 2) {
                        $year = $parts[0];
                        $month = $parts[1];
                        $invoice['invoice_month'] = ($monthNames[$month] ?? $month) . ' ' . $year;
                    }
                }
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            log_message('error', 'InvoiceKecil data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getWidgetData()
    {
        try {
            $db = \Config\Database::connect();

            // Get current month/year
            $currentMonth = date('Y-m'); // Format: 2025-12

            // Total Invoice
            $totalInvoice = $db->table('customer_invoices')
                ->selectSum('bill')
                ->where('periode', $currentMonth)
                ->get()->getRow()->bill ?? 0;

            // Total Paid
            $totalPaid = $db->table('customer_invoices')
                ->selectSum('paid_amount')
                ->where('periode', $currentMonth)
                ->get()->getRow()->paid_amount ?? 0;

            // Total Unpaid
            $totalUnpaid = $totalInvoice - $totalPaid;

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'total_invoice' => (float)$totalInvoice,
                    'total_paid' => (float)$totalPaid,
                    'total_unpaid' => (float)$totalUnpaid
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'InvoiceKecil getWidgetData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'data' => [
                    'total_invoice' => 0,
                    'total_paid' => 0,
                    'total_unpaid' => 0
                ],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        // Halaman untuk create single invoice
        $customers = $this->customerModel
            ->select('id_customers, nama_pelanggan, nomor_layanan')
            ->where('status_tagihan !=', '')
            ->findAll();

        $data = [
            'title' => 'Create Single Invoice',
            'customers' => $customers
        ];

        return view('invoices_kecil/create', $data);
    }

    public function store()
    {
        $customerId = $this->request->getPost('customer_id');
        $periode = $this->request->getPost('periode');
        $amount = $this->request->getPost('amount');

        // Validation
        if (!$customerId || !$periode || !$amount) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'All fields are required'
            ]);
        }

        // Check duplicate
        $existing = $this->invoiceModel
            ->where('customer_id', $customerId)
            ->where('periode', $periode)
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice already exists for this period'
            ]);
        }

        // Generate invoice number
        $invoiceNumber = 'INV-' . strtoupper(uniqid());

        // Insert invoice
        $data = [
            'customer_id' => $customerId,
            'invoice_no' => $invoiceNumber,
            'periode' => $periode,
            'bill' => $amount,
            'status' => 'pending',
            'paid_amount' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->invoiceModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Invoice created successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create invoice'
        ]);
    }

    public function view($id)
    {
        $db = \Config\Database::connect();

        // Get invoice with customer and package details
        $invoice = $db->table('customer_invoices ci')
            ->select('ci.*, c.nama_pelanggan as customer_name, c.nomor_layanan, c.address, c.telepphone,
                     c.village, c.district, c.email,
                     pp.name as package_name, pp.price as package_price')
            ->join('customers c', 'c.id_customers = ci.customer_id', 'left')
            ->join('package_profiles pp', 'pp.id = c.id_paket', 'left')
            ->where('ci.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get customer data for display
        $customer = null;
        if (!empty($invoice['customer_id'])) {
            $customer = $db->table('customers')
                ->where('id_customers', $invoice['customer_id'])
                ->get()
                ->getRowArray();
        }

        // Get transaction details if paid
        $transaction = null;
        if ($invoice['status'] == 'paid' || $invoice['status'] == 'lunas') {
            $transaction = $db->table('transactions')
                ->where('description LIKE', '%' . $invoice['invoice_no'] . '%')
                ->orWhere('description LIKE', '%Invoice ID: ' . $id . '%')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getRowArray();
        }

        // Get payment history from payment_transactions table
        $paymentHistory = $db->table('payment_transactions')
            ->where('invoice_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get active banks for payment info
        $bankModel = new \App\Models\BankModel();
        $activeBanks = $bankModel->where('is_active', 1)->findAll();

        $data = [
            'title' => 'Detail Invoice',
            'invoice' => $invoice,
            'customer' => $customer,
            'transaction' => $transaction,
            'paymentHistory' => $paymentHistory,
            'activeBanks' => $activeBanks,
            'company' => getCompanyData()
        ];

        return view('invoice/view', $data);
    }

    public function delete($id)
    {
        if ($this->invoiceModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete invoice'
        ]);
    }

    public function broadcast()
    {
        // Broadcast invoice via WhatsApp or other channels
        // Implementation depends on your notification system
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Invoice broadcast initiated'
        ]);
    }

    public function export()
    {
        // Export invoices to Excel/PDF
        // Implementation depends on your export requirements
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Export initiated'
        ]);
    }

    public function generate($periode = null)
    {
        $periode = $periode ?: date('Y-m');
        $db = \Config\Database::connect();
        $customerModel = $this->customerModel;
        $invoiceModel = $this->invoiceModel;

        // Ambil semua customer yang eligible 
        // Include customer dengan status 'Lunas' (sudah bayar/aktif) dan 'aktif'
        // Exclude customer yang 'nonaktif' atau status kosong
        $customers = $customerModel
            ->groupStart()
            ->where('status_tagihan', 'aktif')
            ->orWhere('status_tagihan', 'Lunas')
            ->groupEnd()
            ->findAll();

        $created = 0;
        $skipped = 0;
        $broadcasted = 0;
        $failed = 0;
        $broadcastErrors = [];

        foreach ($customers as $customer) {
            // Cek apakah sudah ada invoice kecil untuk periode ini
            $existing = $invoiceModel
                ->where('customer_id', $customer['id_customers'])
                ->where('periode', $periode)
                ->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            // Get customer package price
            $amount = 0;
            $packageName = '';
            if (!empty($customer['id_paket'])) {
                $paket = $db->table('package_profiles')->where('id', $customer['id_paket'])->get()->getRowArray();
                if ($paket && isset($paket['harga'])) {
                    $amount = $paket['harga'];
                    $packageName = $paket['name'] ?? '';
                }
            }

            // Skip if no valid amount
            if ($amount <= 0) {
                $failed++;
                continue;
            }

            // Generate invoice number
            $invoiceNumber = 'INV-' . strtoupper(uniqid());

            $data = [
                'customer_id' => $customer['id_customers'],
                'invoice_no' => $invoiceNumber,
                'periode' => $periode,
                'bill' => $amount,
                'status' => 'unpaid',
                'paid_amount' => 0,
                'package' => $packageName,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($invoiceModel->insert($data)) {
                $created++;
                // Kirim WhatsApp otomatis (jika ada nomor)
                if (!empty($customer['telepphone'])) {
                    try {
                        $waService = new \App\Services\WhatsAppService();
                        $message = "*TAGIHAN KECIL TERBIT*\n\nHalo {$customer['nama_pelanggan']},\nTagihan kecil periode {$periode} telah terbit.\nNomor Invoice: {$invoiceNumber}\nNominal: Rp " . number_format($amount, 0, ',', '.') . "\n\nSilakan lakukan pembayaran tepat waktu. Terima kasih.";
                        $waService->sendMessage($customer['telepphone'], $message);
                        $broadcasted++;
                    } catch (\Exception $e) {
                        $failed++;
                        $broadcastErrors[] = $customer['nama_pelanggan'] . ': ' . $e->getMessage();
                    }
                }
            } else {
                $failed++;
            }
        }

        return [
            'status' => 'success',
            'created' => $created,
            'skipped' => $skipped,
            'broadcasted' => $broadcasted,
            'failed' => $failed,
            'broadcast_errors' => $broadcastErrors,
            'periode' => $periode
        ];
    }
}
