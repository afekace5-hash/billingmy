<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PaymentTransactionModel;

class Laporan extends ResourceController
{
    protected $db;
    protected $paymentModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->paymentModel = new PaymentTransactionModel();
    }

    /**
     * Main reports dashboard
     */
    public function index()
    {
        return view('laporan/index');
    }

    /**
     * Online payments report page
     */
    public function pembayaranOnline()
    {
        return view('laporan/pembayaran_online');
    }

    /**
     * AJAX endpoint for online payments data
     */    public function pembayaranOnlineData()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $request = $this->request;
        $start = $request->getGet('start') ?? 0;
        $length = $request->getGet('length') ?? 10;
        $search = $request->getGet('search')['value'] ?? '';

        // Filter parameters
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');
        $transactionType = $request->getGet('transaction_type') ?? '';
        $category = $request->getGet('category') ?? '';

        try {
            // Get data from multiple sources
            $allTransactions = $this->getFinancialMutationData($startDate, $endDate, $transactionType, $category, $search);

            // Apply pagination
            $total = count($allTransactions);
            $filteredData = array_slice($allTransactions, $start, $length);

            // Format data for DataTables
            $result = [];
            $no = $start + 1;

            foreach ($filteredData as $row) {
                $result[] = [
                    'DT_RowIndex' => $no++,
                    'transaction_date' => $row['transaction_date'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'type' => $row['type'],
                    'debit' => $row['debit'],
                    'credit' => $row['credit'],
                    'balance' => $row['balance'],
                    'source' => $row['source'], // New field to show data source
                    'action' => '<button class="btn btn-sm btn-info btn-detail" data-id="' . $row['id'] . '" data-source="' . $row['source'] . '"><i class="bx bx-show"></i></button>'
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($request->getGet('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching financial mutation data: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Return empty data instead of mock data
            return $this->response->setJSON([
                'draw' => intval($request->getGet('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }

    /**
     * Get real financial mutation data from multiple sources
     */
    private function getFinancialMutationData($startDate, $endDate, $transactionType, $category, $search)
    {
        $allTransactions = [];
        $runningBalance = $this->getInitialBalance($startDate);

        // 1. Get data from cash_flow table (Arus Kas)
        $cashFlowData = $this->getCashFlowData($startDate, $endDate, $transactionType, $category, $search);
        $allTransactions = array_merge($allTransactions, $cashFlowData);

        // 2. Get data from customer_invoices table (Pembayaran Pelanggan)
        $customerPaymentData = $this->getCustomerPaymentData($startDate, $endDate, $transactionType, $search);
        $allTransactions = array_merge($allTransactions, $customerPaymentData);

        // 3. Get data from payment_transactions table (Pembayaran Online)
        $onlinePaymentData = $this->getOnlinePaymentData($startDate, $endDate, $transactionType, $search);
        $allTransactions = array_merge($allTransactions, $onlinePaymentData);

        // Sort by date ascending (lama ke baru)
        usort($allTransactions, function ($a, $b) {
            return strtotime($a['sort_date']) - strtotime($b['sort_date']);
        });

        // Calculate running balance dari bawah ke atas
        foreach ($allTransactions as &$transaction) {
            if ($transaction['type'] === 'Pemasukan') {
                $runningBalance += $transaction['debit'];
            } else {
                $runningBalance -= $transaction['credit'];
            }
            $transaction['balance'] = $runningBalance;
        }
        unset($transaction);

        // Setelah saldo dihitung, balik array agar data terbaru di atas
        $allTransactions = array_reverse($allTransactions);

        return $allTransactions;
    }

    /**
     * Get data from cash_flow table
     */
    private function getCashFlowData($startDate, $endDate, $transactionType, $category, $search)
    {
        $transactions = [];

        if (!$this->db->tableExists('cash_flow')) {
            return $transactions;
        }

        $builder = $this->db->table('cash_flow cf');
        $builder->select('cf.*, kk.nama as category_name')
            ->join('kategori_kas kk', 'kk.id_category = cf.category_id', 'left');

        // Apply date filter
        if ($startDate && $endDate) {
            $builder->where('cf.transaction_date >=', $startDate);
            $builder->where('cf.transaction_date <=', $endDate);
        }

        // Apply type filter
        if ($transactionType) {
            $filterType = ($transactionType === 'pemasukan') ? 'income' : 'expenditure';
            $builder->where('cf.type', $filterType);
        }

        // Apply search filter
        if ($search) {
            $builder->groupStart()
                ->like('cf.name', $search)
                ->orLike('cf.description', $search)
                ->orLike('kk.nama', $search)
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as $row) {
            $isIncome = $row['type'] === 'income';
            $amount = (float) $row['amount'];

            $transactions[] = [
                'id' => 'cash_flow_' . $row['id'],
                'transaction_date' => date('d/m/Y', strtotime($row['transaction_date'])),
                'sort_date' => $row['transaction_date'],
                'description' => $row['name'] . ' - ' . $row['description'],
                'category' => $row['category_name'] ?? 'Umum',
                'type' => $isIncome ? 'Pemasukan' : 'Pengeluaran',
                'debit' => $isIncome ? $amount : 0,
                'credit' => $isIncome ? 0 : $amount,
                'balance' => 0, // Will be calculated later
                'source' => 'Arus Kas'
            ];
        }

        return $transactions;
    }

    /**
     * Get data from customer_invoices table (Customer Payments)
     */
    private function getCustomerPaymentData($startDate, $endDate, $transactionType, $search)
    {
        $transactions = [];

        if (!$this->db->tableExists('customer_invoices')) {
            return $transactions;
        }

        // Only get paid invoices (pemasukan)
        if ($transactionType && $transactionType !== 'pemasukan') {
            return $transactions;
        }
        $builder = $this->db->table('customer_invoices ci');
        $builder->select('ci.*, c.nama_pelanggan as customer_name, c.nomor_layanan as kode_customer')
            ->join('customers c', 'c.id_customers = ci.customer_id', 'left')
            ->where('ci.status', 'paid')
            ->where('ci.payment_date IS NOT NULL');

        // Apply date filter based on payment_date (actual payment date)
        if ($startDate && $endDate) {
            $builder->where('DATE(ci.payment_date) >=', $startDate);
            $builder->where('DATE(ci.payment_date) <=', $endDate);
        }        // Apply search filter
        if ($search) {
            $builder->groupStart()
                ->like('c.nama_pelanggan', $search)
                ->orLike('c.nomor_layanan', $search)
                ->orLike('ci.invoice_no', $search)
                ->orLike('ci.package', $search)
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as $row) {
            $amount = (float) $row['bill'];
            $customerName = $row['customer_name'] ?? 'Unknown Customer';

            $transactions[] = [
                'id' => 'customer_payment_' . $row['id'],
                'transaction_date' => date('d/m/Y', strtotime($row['payment_date'] ?? $row['updated_at'])),
                'sort_date' => date('Y-m-d', strtotime($row['payment_date'] ?? $row['updated_at'])),
                'description' => "Pembayaran {$row['invoice_no']} - {$customerName} ({$row['package']})",
                'category' => 'Pembayaran Pelanggan',
                'type' => 'Pemasukan',
                'debit' => $amount,
                'credit' => 0,
                'balance' => 0, // Will be calculated later
                'source' => 'Pembayaran Pelanggan'
            ];
        }

        return $transactions;
    }

    /**
     * Get data from payment_transactions table (Online Payments)
     */
    private function getOnlinePaymentData($startDate, $endDate, $transactionType, $search)
    {
        $transactions = [];

        if (!$this->db->tableExists('payment_transactions')) {
            return $transactions;
        }

        // Only get successful payments (pemasukan)
        if ($transactionType && $transactionType !== 'pemasukan') {
            return $transactions;
        }

        $builder = $this->db->table('payment_transactions pt');
        $builder->where('pt.status', 'paid');

        // Apply date filter based on payment_date
        if ($startDate && $endDate) {
            $builder->where('DATE(pt.payment_date) >=', $startDate);
            $builder->where('DATE(pt.payment_date) <=', $endDate);
        }

        // Apply search filter
        if ($search) {
            $builder->groupStart()
                ->like('c.nama_pelanggan', $search)
                ->orLike('pt.customer_number', $search)
                ->orLike('pt.transaction_code', $search)
                ->orLike('pt.payment_method', $search)
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as $row) {
            $amount = (float) $row['amount'];
            // No admin_fee column in current table structure
            $adminFee = 0;
            $netAmount = $amount;

            $transactions[] = [
                'id' => 'online_payment_' . $row['id'],
                'transaction_date' => date('d/m/Y', strtotime($row['payment_date'])),
                'sort_date' => date('Y-m-d', strtotime($row['payment_date'])),
                'description' => "Pembayaran Online {$row['transaction_id']} via {$row['payment_method']}",
                'category' => 'Pembayaran Online',
                'type' => 'Pemasukan',
                'debit' => $netAmount,
                'credit' => 0,
                'balance' => 0, // Will be calculated later
                'source' => 'Pembayaran Online'
            ];

            // Add admin fee as separate expenditure if exists
            if ($adminFee > 0) {
                $transactions[] = [
                    'id' => 'admin_fee_' . $row['id'],
                    'transaction_date' => date('d/m/Y', strtotime($row['payment_date'])),
                    'sort_date' => date('Y-m-d', strtotime($row['payment_date'])),
                    'description' => "Biaya Admin Pembayaran Online {$row['transaction_id']} via {$row['payment_method']}",
                    'category' => 'Biaya Admin Payment Gateway',
                    'type' => 'Pengeluaran',
                    'debit' => 0,
                    'credit' => $adminFee,
                    'balance' => 0, // Will be calculated later
                    'source' => 'Pembayaran Online'
                ];
            }
        }

        return $transactions;
    }

    /**
     * Get initial balance before the start date
     */
    private function getInitialBalance($startDate)
    {
        $initialBalance = 0;

        // Calculate balance from cash_flow before start date
        if ($this->db->tableExists('cash_flow')) {
            $builder = $this->db->table('cash_flow');
            $builder->select('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) - SUM(CASE WHEN type = "expenditure" THEN amount ELSE 0 END) as balance');
            $builder->where('transaction_date <', $startDate);
            $result = $builder->get()->getRowArray();
            $initialBalance += (float) ($result['balance'] ?? 0);
        }

        // Add customer payments before start date
        if ($this->db->tableExists('customer_invoices')) {
            $builder = $this->db->table('customer_invoices');
            $builder->select('SUM(bill) as total_payments');
            $builder->where('status', 'paid')
                ->where('payment_date IS NOT NULL');
            $builder->where('DATE(payment_date) <', $startDate);
            $result = $builder->get()->getRowArray();
            $initialBalance += (float) ($result['total_payments'] ?? 0);
        }

        // Add online payments before start date (no admin fees in current table structure)
        if ($this->db->tableExists('payment_transactions')) {
            $builder = $this->db->table('payment_transactions');
            $builder->select('SUM(amount) as net_payments');
            $builder->where('status', 'paid');
            $builder->where('DATE(payment_date) <', $startDate);
            $result = $builder->get()->getRowArray();
            $initialBalance += (float) ($result['net_payments'] ?? 0);
        }
        return $initialBalance;
    }

    /**
     * AJAX endpoint for financial mutations summary
     */
    public function mutasiKeuanganSummary()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $request = $this->request;
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');
        $transactionType = $request->getGet('transaction_type') ?? '';
        $category = $request->getGet('category') ?? '';
        try {
            // Get summary from all sources
            $summary = $this->getFinancialMutationSummary($startDate, $endDate, $transactionType, $category);

            return $this->response->setJSON([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching financial mutation summary: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching summary data',
                'data' => [
                    'total_income' => 0,
                    'total_expense' => 0,
                    'net_balance' => 0,
                    'total_transactions' => 0,
                    'cash_flow_income' => 0,
                    'cash_flow_expense' => 0,
                    'customer_payments' => 0,
                    'online_payments' => 0,
                    'admin_fees' => 0
                ]
            ]);
        }
    }

    /**
     * Get comprehensive financial mutation summary from all sources
     */
    private function getFinancialMutationSummary($startDate, $endDate, $transactionType, $category)
    {
        $summary = [
            'total_income' => 0,
            'total_expense' => 0,
            'net_balance' => 0,
            'total_transactions' => 0,
            'cash_flow_income' => 0,
            'cash_flow_expense' => 0,
            'customer_payments' => 0,
            'online_payments' => 0,
            'admin_fees' => 0
        ];

        // 1. Cash Flow Data
        if ($this->db->tableExists('cash_flow')) {
            $builder = $this->db->table('cash_flow');
            $builder->select('
                SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as cash_income,
                SUM(CASE WHEN type = "expenditure" THEN amount ELSE 0 END) as cash_expense,
                COUNT(*) as cash_transactions
            ');

            if ($startDate && $endDate) {
                $builder->where('transaction_date >=', $startDate);
                $builder->where('transaction_date <=', $endDate);
            }

            // Apply filters
            if ($transactionType) {
                $filterType = ($transactionType === 'pemasukan') ? 'income' : 'expenditure';
                $builder->where('type', $filterType);
            }

            $result = $builder->get()->getRowArray();
            $summary['cash_flow_income'] = (float) ($result['cash_income'] ?? 0);
            $summary['cash_flow_expense'] = (float) ($result['cash_expense'] ?? 0);
            $summary['total_transactions'] += (int) ($result['cash_transactions'] ?? 0);
        }

        // 2. Customer Payments (only if pemasukan is allowed)
        if ($this->db->tableExists('customer_invoices') && (!$transactionType || $transactionType === 'pemasukan')) {
            $builder = $this->db->table('customer_invoices');
            $builder->select('SUM(bill) as total_customer_payments, COUNT(*) as customer_transactions');
            $builder->where('status', 'paid')
                ->where('payment_date IS NOT NULL');

            if ($startDate && $endDate) {
                $builder->where('DATE(payment_date) >=', $startDate);
                $builder->where('DATE(payment_date) <=', $endDate);
            }

            $result = $builder->get()->getRowArray();
            $summary['customer_payments'] = (float) ($result['total_customer_payments'] ?? 0);
            $summary['total_transactions'] += (int) ($result['customer_transactions'] ?? 0);
        }

        // 3. Online Payments (only if pemasukan is allowed)
        if ($this->db->tableExists('payment_transactions') && (!$transactionType || $transactionType === 'pemasukan')) {
            $builder = $this->db->table('payment_transactions');
            $builder->select('
                SUM(amount) as total_online_payments,
                COUNT(*) as online_transactions
            ');
            $builder->where('status', 'paid');

            if ($startDate && $endDate) {
                $builder->where('DATE(payment_date) >=', $startDate);
                $builder->where('DATE(payment_date) <=', $endDate);
            }

            $result = $builder->get()->getRowArray();
            $onlinePayments = (float) ($result['total_online_payments'] ?? 0);

            $summary['online_payments'] = $onlinePayments; // Full amount since no admin_fee column
            $summary['admin_fees'] = 0; // No admin_fee column in current table structure
            $summary['total_transactions'] += (int) ($result['online_transactions'] ?? 0);
        }

        // Calculate totals
        $summary['total_income'] = $summary['cash_flow_income'] + $summary['customer_payments'] + $summary['online_payments'];
        $summary['total_expense'] = $summary['cash_flow_expense'] + $summary['admin_fees'];
        $summary['net_balance'] = $summary['total_income'] - $summary['total_expense'];
        return $summary;
    }

    /**
     * AJAX endpoint for online payments data
     */    public function pembayaranOnlineData()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $request = $this->request;
        $start = $request->getGet('start') ?? 0;
        $length = $request->getGet('length') ?? 10;
        $search = $request->getGet('search')['value'] ?? '';

        // Filter parameters
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');
        $status = $request->getGet('status') ?? '';
        $biller = $request->getGet('biller') ?? '';
        $channel = $request->getGet('channel') ?? '';

        try {
            // Get real data from payment_transactions table
            $builder = $this->db->table('payment_transactions pt');
            $builder->select('pt.*');

            // Apply date filter
            if ($startDate && $endDate) {
                $builder->where('DATE(pt.created_at) >=', $startDate);
                $builder->where('DATE(pt.created_at) <=', $endDate);
            }

            // Apply filters
            if ($status) {
                $builder->where('pt.status', strtolower($status));
            }
            if ($search) {
                $builder->groupStart()
                    ->like('pt.transaction_code', $search)
                    ->orLike('pt.payment_code', $search)
                    ->orLike('pt.customer_name', $search)
                    ->orLike('pt.customer_number', $search)
                    ->groupEnd();
            }

            // Get total count
            $total = $builder->countAllResults(false);

            // Apply pagination and ordering
            $builder->orderBy('pt.created_at', 'DESC');
            $builder->limit($length, $start);

            $data = $builder->get()->getResultArray();

            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                $result[] = [
                    'DT_RowIndex' => $no++,
                    'tanggal' => date('d/m/Y H:i', strtotime($row['created_at'])),
                    'biller' => $row['biller'] ?? 'BILLING SYSTEM',
                    'nomor_pelanggan' => $row['customer_number'] ?? '',
                    'nama' => $row['customer_name'] ?? '',
                    'metode_pembayaran' => $row['payment_method'] ?? '',
                    'tagihan' => (float) ($row['amount'] ?? 0),
                    'admin' => (float) ($row['admin_fee'] ?? 0),
                    'nominal_bayar' => (float) ($row['total_amount'] ?? $row['amount'] ?? 0),
                    'status' => strtoupper($row['status'] ?? ''),
                    'transaksi_kode' => $row['transaction_code'] ?? '',
                    'expired' => $row['expired_at'] ? date('Y-m-d H:i:s', strtotime($row['expired_at'])) : '',
                    'kode_bayar' => $row['payment_code'] ?? ''
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($request->getGet('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching online payments data: ' . $e->getMessage());

            // Return empty data instead of mock data
            return $this->response->setJSON([
                'draw' => intval($request->getGet('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }
    /**
     * AJAX endpoint for online payments summary
     */
    public function pembayaranOnlineSummary()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $request = $this->request;
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');

        try {
            // Get real summary data from payment_transactions table
            $builder = $this->db->table('payment_transactions');
            $builder->select('
                SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_success,
                COUNT(CASE WHEN status = "paid" THEN 1 END) as count_success,
                SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending,
                COUNT(CASE WHEN status = "pending" THEN 1 END) as count_pending,
                SUM(CASE WHEN status = "failed" THEN amount ELSE 0 END) as total_failed,
                COUNT(CASE WHEN status = "failed" THEN 1 END) as count_failed,
                SUM(amount) as total_amount,
                COUNT(*) as total_count
            ');

            // Apply date filter
            if ($startDate && $endDate) {
                $builder->where('DATE(created_at) >=', $startDate);
                $builder->where('DATE(created_at) <=', $endDate);
            }

            $result = $builder->get()->getRowArray();

            $summaryData = [
                'total_success' => (float) ($result['total_success'] ?? 0),
                'count_success' => (int) ($result['count_success'] ?? 0),
                'total_pending' => (float) ($result['total_pending'] ?? 0),
                'count_pending' => (int) ($result['count_pending'] ?? 0),
                'total_failed' => (float) ($result['total_failed'] ?? 0),
                'count_failed' => (int) ($result['count_failed'] ?? 0),
                'total_amount' => (float) ($result['total_amount'] ?? 0),
                'total_count' => (int) ($result['total_count'] ?? 0)
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $summaryData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching online payments summary: ' . $e->getMessage());

            // Return empty data instead of mock data
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching summary data',
                'data' => [
                    'total_success' => 0,
                    'count_success' => 0,
                    'total_pending' => 0,
                    'count_pending' => 0,
                    'total_failed' => 0,
                    'count_failed' => 0,
                    'total_amount' => 0,
                    'total_count' => 0
                ]
            ]);
        }
    }

    /**
     * Export financial mutation data to Excel
     */
    public function exportMutasiKeuangan()
    {
        $request = $this->request;
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');
        $transactionType = $request->getGet('transaction_type') ?? '';
        $category = $request->getGet('category') ?? '';

        try {
            // Get all data for export (no pagination)
            $allData = $this->getFinancialMutationData($startDate, $endDate, $transactionType, $category, '');

            // Set headers for Excel download
            $filename = 'mutasi_keuangan_' . date('Y-m-d_H-i-s') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Pragma: no-cache');
            header('Expires: 0');

            // Create output stream
            $output = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add headers
            fputcsv($output, [
                'No',
                'Tanggal',
                'Keterangan',
                'Kategori',
                'Jenis',
                'Debit',
                'Kredit',
                'Saldo',
                'Sumber'
            ]);

            // Add data rows
            foreach ($allData as $index => $row) {
                fputcsv($output, [
                    $index + 1,
                    $row['transaction_date'],
                    $row['description'],
                    $row['category'],
                    $row['type'],
                    $row['debit'] ? 'Rp ' . number_format($row['debit']) : '',
                    $row['credit'] ? 'Rp ' . number_format($row['credit']) : '',
                    $row['balance'] ? 'Rp ' . number_format($row['balance']) : 'Rp 0',
                    $row['source']
                ]);
            }

            fclose($output);
            exit();
        } catch (\Exception $e) {
            log_message('error', 'Error exporting financial mutation data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data');
        }
    }

    /**
     * Print financial mutation report
     */
    public function printMutasiKeuangan()
    {
        $request = $this->request;
        $startDate = $request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $request->getGet('end_date') ?? date('Y-m-d');
        $transactionType = $request->getGet('transaction_type') ?? '';
        $category = $request->getGet('category') ?? '';

        try {
            // Get all data for print
            $allData = $this->getFinancialMutationData($startDate, $endDate, $transactionType, $category, '');
            $summary = $this->getFinancialMutationSummary($startDate, $endDate, $transactionType, $category);

            $data = [
                'title' => 'Laporan Mutasi Keuangan',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'transaction_type' => $transactionType,
                'category' => $category,
                'transactions' => $allData,
                'summary' => $summary,
                'print_date' => date('d/m/Y H:i:s')
            ];

            return view('laporan/print_mutasi_keuangan', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error printing financial mutation data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak laporan');
        }
    }

    /**
     * Check for new payment updates (real-time callback)
     */
    public function checkPembayaranOnlineUpdates()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $lastCheck = $this->request->getGet('last_check');
        $lastCheckTime = $lastCheck ? date('Y-m-d H:i:s', $lastCheck) : date('Y-m-d H:i:s', strtotime('-1 minute'));
        try {
            // Get recent updated transactions from payment_transactions table
            $builder = $this->db->table('payment_transactions pt');
            $builder->select('pt.*')
                ->where('pt.updated_at >=', $lastCheckTime)
                ->orderBy('pt.updated_at', 'DESC')
                ->limit(10);

            $newPayments = $builder->get()->getResultArray();

            // Format the data
            $formattedPayments = [];
            foreach ($newPayments as $payment) {
                $formattedPayments[] = [
                    'id' => $payment['id'],
                    'nama' => $payment['customer_name'] ?? $payment['customer_number'] ?? '',
                    'nominal_bayar' => (float) ($payment['total_amount'] ?? 0),
                    'status' => strtoupper($payment['status'] ?? ''),
                    'metode_pembayaran' => $payment['payment_method'] ?? '',
                    'transaksi_kode' => $payment['transaction_code'] ?? '',
                    'tanggal' => date('d/m/Y H:i:s', strtotime($payment['created_at']))
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'has_new_payments' => count($newPayments) > 0,
                    'new_payments' => $formattedPayments,
                    'count' => count($newPayments),
                    'last_check_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error checking payment updates: ' . $e->getMessage());

            // Return empty data instead of mock data
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'has_new_payments' => false,
                    'new_payments' => [],
                    'count' => 0,
                    'last_check_time' => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }

    /**
     * Export online payments to Excel
     */
    public function exportPembayaranOnline()
    {
        // Implementation for Excel export
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Export functionality not implemented yet'
        ]);
    }

    /**
     * Print online payments report
     */
    public function printPembayaranOnline()
    {
        // Implementation for print view
        return view('laporan/print_pembayaran_online');
    }
}
