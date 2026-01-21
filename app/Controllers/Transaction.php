<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use CodeIgniter\Controller;

class Transaction extends Controller
{
    protected $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
    }

    public function transaction()
    {
        $data = [
            'title' => 'Transaction Management',
            'month' => date('m'),
            'year' => date('Y'),
        ];

        return view('transaction/index', $data);
    }

    public function data()
    {
        try {
            $request = \Config\Services::request();

            $draw = $request->getPost('draw') ?? 1;
            $start = $request->getPost('start') ?? 0;
            $length = $request->getPost('length') ?? 10;
            $searchValue = $request->getPost('search')['value'] ?? '';
            $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

            // Get month and year filter
            $month = $request->getPost('month') ?? date('m');
            $year = $request->getPost('year') ?? date('Y');

            $columns = ['', 'id', 'branch', 'date', 'transaction_name', 'payment_method', 'category', 'description', 'type', 'amount', 'created_at'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            if (empty($orderColumn)) {
                $orderColumn = 'id';
            }

            // Base query with filters
            $builder = $this->transactionModel->getDataTable([
                'month' => $month,
                'year' => $year,
                'search' => $searchValue
            ]);

            // Count total
            $totalRecords = $builder->countAllResults(false);

            // Apply ordering and pagination
            $builder->orderBy($orderColumn, $orderDir);
            $builder->limit($length, $start);

            $transactions = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Transaction data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getSummary()
    {
        try {
            $request = \Config\Services::request();
            $month = $request->getPost('month') ?? date('m');
            $year = $request->getPost('year') ?? date('Y');

            $summary = $this->transactionModel->getSummary($month, $year);

            // Debug logging
            log_message('info', 'Summary data: ' . json_encode($summary));

            return $this->response->setJSON([
                'success' => true,
                'data' => $summary,
                'debug' => [
                    'month' => $month,
                    'year' => $year,
                    'raw_data' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Summary error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $rules = [
                'date' => 'required|valid_date',
                'transaction_name' => 'required|min_length[3]',
                'payment_method' => 'required',
                'category' => 'required',
                'type' => 'required|in_list[in,out]',
                'amount' => 'required|decimal|greater_than[0]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $relatedId = $this->request->getPost('related_id');

            $data = [
                'related_id' => $relatedId,
                'branch' => $this->request->getPost('branch'),
                'date' => $this->request->getPost('date'),
                'transaction_name' => $this->request->getPost('transaction_name'),
                'payment_method' => $this->request->getPost('payment_method'),
                'category' => $this->request->getPost('category'),
                'description' => $this->request->getPost('description'),
                'type' => $this->request->getPost('type'),
                'amount' => $this->request->getPost('amount'),
                'created_by' => session()->get('id_user') ?? 1,
            ];

            $this->transactionModel->insert($data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        try {
            $transaction = $this->transactionModel->find($id);

            if (!$transaction) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        try {
            $transaction = $this->transactionModel->find($id);

            if (!$transaction) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction not found'
                ]);
            }

            $rules = [
                'date' => 'required|valid_date',
                'transaction_name' => 'required|min_length[3]',
                'payment_method' => 'required',
                'category' => 'required',
                'type' => 'required|in_list[in,out]',
                'amount' => 'required|decimal|greater_than[0]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $data = [
                'branch' => $this->request->getPost('branch'),
                'date' => $this->request->getPost('date'),
                'transaction_name' => $this->request->getPost('transaction_name'),
                'payment_method' => $this->request->getPost('payment_method'),
                'category' => $this->request->getPost('category'),
                'description' => $this->request->getPost('description'),
                'type' => $this->request->getPost('type'),
                'amount' => $this->request->getPost('amount'),
            ];

            $this->transactionModel->update($id, $data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $transaction = $this->transactionModel->find($id);

            if (!$transaction) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction not found'
                ]);
            }

            $this->transactionModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate unique transaction code
     * Format: 10digitRandom-InvoiceNo-3digitRandom
     * Example: 1758036300-INV-RYI64092520-26-520
     */
    private function generateTransactionCode($relatedId = null)
    {
        // 10 digit random (menggunakan timestamp)
        $part1 = time();

        // Middle part: gunakan invoice_no jika ada related_id
        if ($relatedId) {
            $db = \Config\Database::connect();
            $invoice = $db->table('customer_invoices')
                ->select('invoice_no')
                ->where('id', $relatedId)
                ->get()
                ->getRowArray();

            if ($invoice && !empty($invoice['invoice_no'])) {
                $part2 = $invoice['invoice_no'];
            } else {
                // Jika invoice tidak ditemukan, gunakan format default
                $part2 = 'TRX-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            }
        } else {
            // Jika tidak ada related_id, gunakan format default
            $part2 = 'TRX-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        }

        // 3 digit random
        $part3 = rand(100, 999);

        // Combine all parts
        $code = $part1 . '-' . $part2 . '-' . $part3;

        // Pastikan unique (cek di database)
        $exists = $this->transactionModel->where('code', $code)->first();
        if ($exists) {
            // Jika ada duplikat, tambahkan random lagi
            $code .= '-' . rand(10, 99);
        }

        return $code;
    }
}
