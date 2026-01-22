<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use CodeIgniter\RESTful\ResourceController;

class InvoiceDiscount extends ResourceController
{
    protected $invoiceModel;
    protected $customerModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
    }

    /**
     * Display invoice discount management
     */
    public function index()
    {
        $data = [
            'title' => 'Kelola Diskon Tagihan',
        ];

        return view('invoice_discount/index', $data);
    }

    /**
     * Get invoices data for DataTable
     */
    public function data()
    {
        $request = \Config\Services::request();
        $db = \Config\Database::connect();

        // DataTables parameters
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $draw = $request->getPost('draw') ?? 1;
        $searchValue = $request->getPost('search')['value'] ?? '';

        // Base query - get unpaid invoices
        $builder = $db->table('customer_invoices ci');
        $builder->select('ci.*, c.nama_pelanggan as customer_name, c.nomor_layanan, 
                         pp.name as package_name, ls.name as server_name');
        $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
        $builder->join('package_profiles pp', 'pp.id = c.id_paket', 'left');
        $builder->join('lokasi_server ls', 'ls.id_lokasi = c.id_lokasi_server', 'left');
        $builder->where('ci.status', 'unpaid');

        // Search
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('ci.invoice_no', $searchValue)
                ->orLike('c.nama_pelanggan', $searchValue)
                ->orLike('c.nomor_layanan', $searchValue)
                ->orLike('ci.periode', $searchValue)
                ->groupEnd();
        }

        // Count total records
        $totalRecords = $builder->countAllResults(false);

        // Apply pagination
        $builder->orderBy('ci.created_at', 'DESC');
        $builder->limit($length, $start);
        $invoices = $builder->get()->getResultArray();

        // Format data for DataTable
        $data = [];
        foreach ($invoices as $invoice) {
            $total = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];

            $data[] = [
                'id' => $invoice['id'],
                'invoice_no' => $invoice['invoice_no'],
                'customer_name' => $invoice['customer_name'],
                'nomor_layanan' => $invoice['nomor_layanan'],
                'periode' => $invoice['periode'],
                'bill' => $invoice['bill'],
                'additional_fee' => $invoice['additional_fee'],
                'current_discount' => $invoice['discount'],
                'total' => $total,
                'status' => $invoice['status'],
                'package_name' => $invoice['package_name']
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Apply discount to invoice
     */
    public function applyDiscount()
    {
        $invoiceId = $this->request->getPost('invoice_id');
        $discountAmount = (float)$this->request->getPost('discount_amount');
        $discountType = $this->request->getPost('discount_type'); // 'fixed' or 'percent'
        $reason = $this->request->getPost('reason') ?? 'Manual discount';

        if (!$invoiceId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice ID tidak valid'
            ]);
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice tidak ditemukan'
            ]);
        }

        // Calculate discount amount
        $finalDiscountAmount = 0;
        if ($discountType === 'percent') {
            $finalDiscountAmount = ((float)$invoice['bill'] * $discountAmount / 100);
        } else {
            $finalDiscountAmount = $discountAmount;
        }

        // Validate discount amount doesn't exceed bill amount
        $maxDiscount = (float)$invoice['bill'] + (float)$invoice['additional_fee'];
        if ($finalDiscountAmount > $maxDiscount) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Diskon tidak boleh melebihi total tagihan'
            ]);
        }

        // Update invoice
        $updateData = [
            'discount' => $finalDiscountAmount,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->invoiceModel->update($invoiceId, $updateData)) {
            // Log the discount activity
            $this->logDiscountActivity($invoiceId, $finalDiscountAmount, $discountType, $reason);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Diskon berhasil diterapkan',
                'new_discount' => $finalDiscountAmount
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menerapkan diskon'
            ]);
        }
    }

    /**
     * Remove discount from invoice
     */
    public function removeDiscount()
    {
        $invoiceId = $this->request->getPost('invoice_id');

        if (!$invoiceId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice ID tidak valid'
            ]);
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice tidak ditemukan'
            ]);
        }

        // Update invoice - remove discount
        $updateData = [
            'discount' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->invoiceModel->update($invoiceId, $updateData)) {
            // Log the removal
            $this->logDiscountActivity($invoiceId, 0, 'removed', 'Discount removed');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Diskon berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus diskon'
            ]);
        }
    }

    /**
     * Bulk apply discount to multiple invoices
     */
    public function bulkDiscount()
    {
        $invoiceIds = $this->request->getPost('invoice_ids');
        $discountAmount = (float)$this->request->getPost('discount_amount');
        $discountType = $this->request->getPost('discount_type');
        $reason = $this->request->getPost('reason') ?? 'Bulk discount';

        if (!is_array($invoiceIds) || empty($invoiceIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pilih minimal 1 invoice'
            ]);
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($invoiceIds as $invoiceId) {
            $invoice = $this->invoiceModel->find($invoiceId);
            if (!$invoice) {
                $failCount++;
                continue;
            }

            // Calculate discount
            $finalDiscountAmount = 0;
            if ($discountType === 'percent') {
                $finalDiscountAmount = ((float)$invoice['bill'] * $discountAmount / 100);
            } else {
                $finalDiscountAmount = $discountAmount;
            }

            // Validate
            $maxDiscount = (float)$invoice['bill'] + (float)$invoice['additional_fee'];
            if ($finalDiscountAmount > $maxDiscount) {
                $failCount++;
                continue;
            }

            // Update
            $updateData = [
                'discount' => $finalDiscountAmount,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->invoiceModel->update($invoiceId, $updateData)) {
                $this->logDiscountActivity($invoiceId, $finalDiscountAmount, $discountType, $reason);
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil: {$successCount}, Gagal: {$failCount}"
        ]);
    }

    /**
     * Log discount activity for audit trail
     */
    private function logDiscountActivity($invoiceId, $discountAmount, $discountType, $reason)
    {
        $db = \Config\Database::connect();

        // Check if discount_logs table exists, if not create it
        if (!$db->tableExists('invoice_discount_logs')) {
            $forge = \Config\Database::forge();

            $fields = [
                'id' => [
                    'type' => 'INT',
                    'auto_increment' => true
                ],
                'invoice_id' => [
                    'type' => 'INT',
                    'null' => false
                ],
                'discount_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0
                ],
                'discount_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'fixed'
                ],
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true
                ],
                'user_id' => [
                    'type' => 'INT',
                    'null' => true
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP'
                ]
            ];

            $forge->addField($fields);
            $forge->addKey('id', true);
            $forge->createTable('invoice_discount_logs');
        }

        // Insert log
        $logData = [
            'invoice_id' => $invoiceId,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
            'reason' => $reason,
            'user_id' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->table('invoice_discount_logs')->insert($logData);
    }
}
