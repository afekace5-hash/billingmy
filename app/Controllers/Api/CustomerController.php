<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $invoiceModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Get customer profile
     * GET /api/customer/profile
     */
    public function profile()
    {
        $customerId = $this->request->customerId;

        $db = \Config\Database::connect();

        $customer = $db->table('customers c')
            ->select('c.id_customers, c.nomor_layanan, c.nama_pelanggan, c.email, c.telepphone, 
                      c.address, c.status_tagihan, c.tgl_tempo, c.tgl_pasang, c.latitude, c.longitude,
                      p.id as package_id, p.name as package_name, p.bandwidth_profile, p.price as package_price,
                      ls.id_lokasi as server_id, ls.name as server_name, ls.alamat as server_address')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->join('lokasi_server ls', 'ls.id_lokasi = c.id_lokasi_server', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data customer tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Calculate days until due date
        $daysUntilDue = null;
        $isDue = false;
        if ($customer['tgl_tempo']) {
            $dueDate = strtotime($customer['tgl_tempo']);
            $today = strtotime(date('Y-m-d'));
            $daysUntilDue = floor(($dueDate - $today) / (60 * 60 * 24));
            $isDue = $daysUntilDue <= 0;
        }

        $customer['days_until_due'] = $daysUntilDue;
        $customer['is_due'] = $isDue;
        $customer['status_active'] = $customer['status_tagihan'] === 'Lunas';

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data customer berhasil dimuat',
            'data' => $customer
        ]);
    }

    /**
     * Update customer profile
     * PUT /api/customer/profile
     * Body: { "email": "new@email.com", "telepphone": "081234567890", "address": "new address" }
     */
    public function updateProfile()
    {
        $customerId = $this->request->customerId;

        $rules = [
            'email' => 'permit_empty|valid_email',
            'telepphone' => 'permit_empty|numeric|min_length[10]|max_length[15]',
            'address' => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $updateData = [];

        if ($this->request->getVar('email')) {
            $updateData['email'] = $this->request->getVar('email');
        }
        if ($this->request->getVar('telepphone')) {
            $updateData['telepphone'] = $this->request->getVar('telepphone');
        }
        if ($this->request->getVar('address')) {
            $updateData['address'] = $this->request->getVar('address');
        }

        if (empty($updateData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada data yang diubah'
            ])->setStatusCode(400);
        }

        $this->customerModel->update($customerId, $updateData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $updateData
        ]);
    }

    /**
     * Get customer status (internet, billing, isolir status)
     * GET /api/customer/status
     */
    public function status()
    {
        $customerId = $this->request->customerId;

        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data customer tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Calculate status
        $daysUntilDue = null;
        $isDue = false;
        $isOverdue = false;

        if ($customer['tgl_tempo']) {
            $dueDate = strtotime($customer['tgl_tempo']);
            $today = strtotime(date('Y-m-d'));
            $daysUntilDue = floor(($dueDate - $today) / (60 * 60 * 24));
            $isDue = $daysUntilDue <= 0;
            $isOverdue = $daysUntilDue < -3; // Overdue more than 3 days
        }

        $status = [
            'internet_status' => $customer['status_tagihan'] === 'Lunas' ? 'active' : 'inactive',
            'payment_status' => $customer['status_tagihan'],
            'is_due' => $isDue,
            'is_overdue' => $isOverdue,
            'days_until_due' => $daysUntilDue,
            'due_date' => $customer['tgl_tempo'],
            'install_date' => $customer['tgl_pasang'],
            'warning_message' => null
        ];

        // Add warning messages
        if ($isOverdue) {
            $status['warning_message'] = 'Tagihan Anda sudah melewati jatuh tempo. Internet akan diisolir jika tidak segera dibayar.';
        } elseif ($isDue) {
            $status['warning_message'] = 'Tagihan Anda sudah jatuh tempo. Mohon segera lakukan pembayaran.';
        } elseif ($daysUntilDue !== null && $daysUntilDue <= 3) {
            $status['warning_message'] = "Tagihan Anda akan jatuh tempo dalam {$daysUntilDue} hari.";
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status customer berhasil dimuat',
            'data' => $status
        ]);
    }

    /**
     * Get customer invoices
     * GET /api/customer/invoices
     * Query params: ?status=paid|unpaid&year=2025&month=12&limit=20&page=1
     */
    public function invoices()
    {
        $customerId = $this->request->customerId;

        // Get query parameters
        $status = $this->request->getGet('status'); // paid, unpaid
        $year = $this->request->getGet('year');
        $month = $this->request->getGet('month');
        $limit = $this->request->getGet('limit') ?? 20;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $db = \Config\Database::connect();
        $builder = $db->table('wstmp_invoices i');

        $builder->select('i.*, c.nomor_layanan, c.nama_pelanggan')
            ->join('customers c', 'c.id_customers = i.id_customers', 'left')
            ->where('i.id_customers', $customerId)
            ->orderBy('i.id_inv', 'DESC');

        // Filter by status
        if ($status === 'paid') {
            $builder->where('i.status', 'Lunas');
        } elseif ($status === 'unpaid') {
            $builder->whereNotIn('i.status', ['Lunas']);
        }

        // Filter by year and month
        if ($year) {
            $builder->where('YEAR(i.duedate)', $year);
        }
        if ($month) {
            $builder->where('MONTH(i.duedate)', $month);
        }

        // Get total count
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults(false);

        // Get paginated data
        $invoices = $builder->limit($limit, $offset)->get()->getResultArray();

        // Add additional info to each invoice
        foreach ($invoices as &$invoice) {
            $dueDate = strtotime($invoice['duedate']);
            $today = strtotime(date('Y-m-d'));
            $daysUntilDue = floor(($dueDate - $today) / (60 * 60 * 24));

            $invoice['days_until_due'] = $daysUntilDue;
            $invoice['is_overdue'] = $daysUntilDue < 0 && $invoice['status'] !== 'Lunas';
            $invoice['can_pay'] = $invoice['status'] !== 'Lunas';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data tagihan berhasil dimuat',
            'data' => [
                'invoices' => $invoices,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }

    /**
     * Get invoice detail
     * GET /api/customer/invoice/{id}
     */
    public function invoiceDetail($invoiceId)
    {
        $customerId = $this->request->customerId;

        $db = \Config\Database::connect();

        $invoice = $db->table('wstmp_invoices i')
            ->select('i.*, c.nomor_layanan, c.nama_pelanggan, c.email, c.telepphone, c.address')
            ->join('customers c', 'c.id_customers = i.id_customers', 'left')
            ->where('i.id_inv', $invoiceId)
            ->where('i.id_customers', $customerId)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Calculate days until due
        $dueDate = strtotime($invoice['duedate']);
        $today = strtotime(date('Y-m-d'));
        $daysUntilDue = floor(($dueDate - $today) / (60 * 60 * 24));

        $invoice['days_until_due'] = $daysUntilDue;
        $invoice['is_overdue'] = $daysUntilDue < 0 && $invoice['status'] !== 'Lunas';
        $invoice['can_pay'] = $invoice['status'] !== 'Lunas';

        // Get payment history for this invoice
        $payments = $db->table('payment_transactions')
            ->where('customer_number', $invoice['nomor_layanan'])
            ->where('status', 'success')
            ->orderBy('paid_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $invoice['payment_history'] = $payments;

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Detail tagihan berhasil dimuat',
            'data' => $invoice
        ]);
    }

    /**
     * Get payment history
     * GET /api/customer/payment-history
     * Query params: ?limit=20&page=1
     */
    public function paymentHistory()
    {
        $customerId = $this->request->customerId;

        // Get customer nomor_layanan
        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ])->setStatusCode(404);
        }

        $limit = $this->request->getGet('limit') ?? 20;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $db = \Config\Database::connect();

        // Get total count
        $total = $db->table('payment_transactions')
            ->where('customer_number', $customer['nomor_layanan'])
            ->countAllResults();

        // Get paginated data
        $payments = $db->table('payment_transactions')
            ->where('customer_number', $customer['nomor_layanan'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Riwayat pembayaran berhasil dimuat',
            'data' => [
                'payments' => $payments,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }
}
