<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CashFlowModel;
use App\Models\KategoriKasModel;

class ArusKas extends ResourceController
{
    protected $cashFlowModel;
    protected $kategoriKasModel;
    protected $db;

    public function __construct()
    {
        $this->cashFlowModel = new CashFlowModel();
        $this->kategoriKasModel = new KategoriKasModel();
        $this->db = \Config\Database::connect();
    }

    // Tampilkan halaman kategori kas
    public function category()
    {
        return view('arus_kas/category');
    }

    // Handler untuk simpan data kas (POST: arus_kas/flowSave)
    public function flowSave()
    {
        if ($this->request->isAJAX()) {
            // Clean amount field first (remove dots for thousand separator)
            $cleanAmount = str_replace(['.', ','], '', $this->request->getPost('amount'));

            // Get ID if editing
            $id = $this->request->getPost('id');

            // Format tanggal ke Y-m-d
            $inputDate = $this->request->getPost('transaction_date');
            $validDate = false;
            $dateObj = null;

            // Try different date formats
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];
            foreach ($formats as $format) {
                $dateObj = \DateTime::createFromFormat($format, $inputDate);
                if ($dateObj && $dateObj->format($format) === $inputDate) {
                    $validDate = true;
                    break;
                }
            }

            if (!$validDate) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => ['transaction_date' => 'Format tanggal tidak valid. Gunakan format: YYYY-MM-DD atau DD/MM/YYYY'],
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }

            $rules = [
                'nama' => 'required',
                'amount' => 'required',
                'category_id' => 'required|numeric|greater_than[0]',
                'description' => 'required',
            ];

            // Custom validation for amount after cleaning
            if (!is_numeric($cleanAmount) || $cleanAmount <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => ['amount' => 'Amount harus berupa angka yang valid dan lebih besar dari 0'],
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }

            try {
                $categoryId = $this->request->getPost('category_id');

                log_message('debug', 'flowSave - Request data: ' . json_encode($this->request->getPost()));

                // Get category data
                $category = $this->kategoriKasModel->find($categoryId);
                log_message('debug', 'flowSave - Category data: ' . json_encode($category));

                if (!$category) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'errors' => ['category_id' => 'Kategori kas tidak ditemukan'],
                        'csrfHash' => csrf_hash()
                    ])->setStatusCode(400);
                }                // Convert jenis_kas to type for cash_flow table
                $type = ($category['jenis_kas'] === 'pemasukan') ? 'income' : 'expenditure';

                log_message('debug', 'Category jenis_kas: ' . $category['jenis_kas'] . ', converted to type: ' . $type);

                $data = [
                    'name' => $this->request->getPost('nama'),
                    'amount' => $cleanAmount,
                    'transaction_date' => $dateObj->format('Y-m-d'),
                    'category_id' => $categoryId,
                    'description' => $this->request->getPost('description'),
                    'type' => $type
                ];

                log_message('debug', 'Data to be saved: ' . json_encode($data));

                if ($id) {
                    // Verify data exists before updating
                    $existingData = $this->cashFlowModel->find($id);
                    if (!$existingData) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Data yang akan diupdate tidak ditemukan',
                            'csrfHash' => csrf_hash()
                        ])->setStatusCode(404);
                    }

                    $this->cashFlowModel->update($id, $data);
                    $msg = 'Data kas berhasil diupdate.';
                    log_message('info', 'Data kas diupdate dengan ID: ' . $id);
                } else {
                    $id = $this->cashFlowModel->insert($data, true);
                    $msg = 'Data kas berhasil ditambahkan.';
                    log_message('info', 'Data kas ditambahkan dengan ID: ' . $id . ', Data: ' . json_encode($data));
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'title' => 'Berhasil',
                    'message' => $msg,
                    'id' => $id,
                    'csrfHash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error in flowSave: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(500);
            }
        }

        // Fallback for non-AJAX
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Display the cash flow page (index for resource controller)
     */
    public function index()
    {
        $categoryModel = new \App\Models\KategoriKasModel();
        $categories = $categoryModel->findAll();
        return view('arus_kas/cash_flow', [
            'categories' => $categories
        ]);
    }

    // If you want a separate method for cashFlow, you can add:
    public function cashFlow()
    {
        $categoryModel = new \App\Models\KategoriKasModel();
        $categories = $categoryModel->findAll();
        return view('arus_kas/cash_flow', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'nama' => 'required',
                'amount' => 'required|numeric',
                'transaction_date' => 'required',
                'customer_cash_flow_category_id' => 'required',
                'description' => 'required',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }

            $model = new \App\Models\CashFlowModel();

            $data = [
                'name' => $this->request->getPost('nama'),
                'amount' => str_replace('.', '', $this->request->getPost('amount')),
                'transaction_date' => $this->request->getPost('transaction_date'),
                'category_id' => $this->request->getPost('customer_cash_flow_category_id'),
                'description' => $this->request->getPost('description'),
            ];

            // Insert and get the new id
            $id = $model->insert($data, true); // true returns the insert ID

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data kas berhasil ditambahkan.',
                'id' => $id, // return the new id
                'csrfHash' => csrf_hash()
            ]);
        }

        // Fallback for non-AJAX
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Endpoint for widget cash data (AJAX) - Always return GRAND TOTAL without month filter
    public function getWidgetCashData()
    {
        try {
            log_message('info', 'ArusKas::getWidgetCashData() called - calculating GRAND TOTAL');

            $db = \Config\Database::connect();
            $model = new \App\Models\CashFlowModel();
            $useSoftDeletes = $model->useSoftDeletes;

            // Build combined query for all revenue sources
            $softDeleteCondition = $useSoftDeletes ? "cf.deleted_at IS NULL" : "1=1";

            $allDataQuery = "
                SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expenditure' THEN amount ELSE 0 END) as total_expenditure
                FROM (
                    SELECT 
                        cf.amount,
                        cf.type
                    FROM cash_flow cf
                    WHERE $softDeleteCondition
                    
                    UNION ALL
                    
                    SELECT 
                        COALESCE(ci.paid_amount, ci.bill) as amount,
                        'income' as type
                    FROM customer_invoices ci
                    WHERE ci.status = 'paid' AND COALESCE(ci.paid_amount, ci.bill) > 0
                ) as combined_revenue
            ";

            $summary = $db->query($allDataQuery)->getRowArray();
            $totalIncome = isset($summary['total_income']) ? (int)$summary['total_income'] : 0;
            $totalExpenditure = isset($summary['total_expenditure']) ? (int)$summary['total_expenditure'] : 0;
            $saldo = $totalIncome - $totalExpenditure;

            log_message('info', "Widget GRAND TOTAL calculated - Income: $totalIncome, Expenditure: $totalExpenditure, Saldo: $saldo");

            $data = [
                'status' => true,
                'data' => [
                    'currency' => 'Rp',
                    'income' => number_format($totalIncome, 0, ',', '.'),
                    'expenditure' => number_format($totalExpenditure, 0, ',', '.'),
                    'net_income' => number_format($saldo, 0, ',', '.'),
                ]
            ];

            log_message('info', 'Widget data: ' . json_encode($data));

            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', 'Error in getWidgetCashData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Fallback dummy data
            $data = [
                'status' => false,
                'data' => [
                    'currency' => 'Rp',
                    'income' => '0',
                    'expenditure' => '0',
                    'net_income' => '0',
                ]
            ];
            return $this->response->setJSON($data);
        }
    }

    // DataTable AJAX handler for arus_kas/data
    public function data()
    {
        try {
            // Debug logging
            log_message('info', 'ArusKas::data() called');
            log_message('info', 'Request data: ' . json_encode($this->request->getGet()));
            $db = \Config\Database::connect();
            $model = new \App\Models\CashFlowModel();
            $request = $this->request;

            // Get filters and parameters
            $filterMonth = $request->getVar('month') ?: date('m');
            $filterYear = $request->getVar('year') ?: date('Y');

            // Ensure month is padded to 2 digits
            $filterMonth = str_pad($filterMonth, 2, '0', STR_PAD_LEFT);

            log_message('info', "Filtering for month: $filterMonth, year: $filterYear");
            log_message('info', "Current date: " . date('Y-m-d') . ", Filter will match: $filterYear-$filterMonth");
            $searchValue = $request->getVar('search')['value'] ?? '';
            $orderColumnIdx = $request->getVar('order')[0]['column'] ?? 0;
            $orderDir = $request->getVar('order')[0]['dir'] ?? 'desc';
            $columns = $request->getVar('columns');
            $orderColumn = $columns[$orderColumnIdx]['data'] ?? 'id';

            if ($orderColumn === 'DT_RowIndex') {
                $orderColumn = 'cf.id';
            }

            $start = $request->getVar('start') ?? 0;
            $length = $request->getVar('length') ?? 10;

            // Build combined query for cash flow and invoices only (no payment_transactions)
            $softDeleteCondition = $model->useSoftDeletes ? "cf.deleted_at IS NULL" : "1=1";

            $allDataQuery = "
                SELECT 
                    'cash_flow' as source_type,
                    cf.id,
                    cf.name,
                    cf.transaction_date,
                    cf.amount,
                    cf.type,
                    cf.description,
                    k.nama as category_name,
                    'Manual Entry' as source_description
                FROM cash_flow cf
                LEFT JOIN kategori_kas k ON k.id_category = cf.category_id
                WHERE $softDeleteCondition
                
                UNION ALL
                
                SELECT 
                    'invoice' as source_type,
                    ci.id,
                    CONCAT('Invoice #', ci.invoice_no, ' - ', COALESCE(c.nama_pelanggan, 'Unknown Customer')) as name,
                    ci.payment_date as transaction_date,
                    CASE 
                        WHEN ci.paid_amount IS NULL OR ci.paid_amount = '' THEN ci.bill 
                        ELSE ci.paid_amount 
                    END as amount,
                    'income' as type,
                    CONCAT('Pembayaran invoice ', ci.periode, ' untuk ', COALESCE(c.nama_pelanggan, 'Unknown Customer')) as description,
                    'Pembayaran Invoice' as category_name,
                    CONCAT('Invoice Payment - ', COALESCE(ci.payment_method, 'Manual')) as source_description
                FROM customer_invoices ci
                LEFT JOIN customers c ON c.id_customers = ci.customer_id
                WHERE ci.status = 'paid' 
                  AND (
                    (ci.paid_amount IS NOT NULL AND ci.paid_amount != '' AND ci.paid_amount > 0) 
                    OR 
                    ((ci.paid_amount IS NULL OR ci.paid_amount = '') AND ci.bill > 0)
                  )
                  AND ci.payment_date IS NOT NULL
            ";

            // Create temporary table/view for pagination and filtering
            $filteredQuery = "
                SELECT * FROM ($allDataQuery) as combined_data
                WHERE 1=1
            ";

            // Apply date filters
            if ($filterMonth && $filterYear) {
                $filteredQuery .= " AND MONTH(transaction_date) = '$filterMonth' AND YEAR(transaction_date) = '$filterYear'";
            }

            // Apply search filter
            if (!empty($searchValue)) {
                $escapedSearch = $db->escape('%' . $searchValue . '%');
                $filteredQuery .= " AND (
                    name LIKE $escapedSearch OR 
                    description LIKE $escapedSearch OR 
                    category_name LIKE $escapedSearch OR
                    source_description LIKE $escapedSearch
                )";
            }

            // Get total count for filtered results
            $countQuery = "SELECT COUNT(*) as total FROM ($filteredQuery) as counted_data";
            $totalRecords = $db->query($countQuery)->getRow()->total;
            $recordsFiltered = $totalRecords;

            // Calculate GRAND TOTAL summary for all data (no date filter)
            $grandSummaryQuery = "
                SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as grand_total_income,
                    SUM(CASE WHEN type = 'expenditure' THEN amount ELSE 0 END) as grand_total_expenditure
                FROM ($allDataQuery) as summary_data
            ";

            $grandSummary = $db->query($grandSummaryQuery)->getRowArray();
            $grandTotalIncome = (int)($grandSummary['grand_total_income'] ?? 0);
            $grandTotalExpenditure = (int)($grandSummary['grand_total_expenditure'] ?? 0);
            $grandSaldo = $grandTotalIncome - $grandTotalExpenditure;

            // Apply search if provided
            if (!empty($searchValue)) {
                $escapedSearch = $db->escape('%' . $searchValue . '%');
                $filteredQuery .= " AND (
                    name LIKE $escapedSearch OR 
                    description LIKE $escapedSearch OR 
                    category_name LIKE $escapedSearch OR
                    source_description LIKE $escapedSearch
                )";
            }

            // Apply ordering
            $orderColumns = [
                1 => 'name',
                2 => 'transaction_date',
                3 => 'category_name',
                4 => 'description',
                5 => 'amount', // income column
                6 => 'amount'  // expenditure column
            ];

            if (isset($orderColumns[$orderColumnIdx])) {
                if ($orderColumnIdx == 5) {
                    // For income column, order by amount but only for income type
                    $filteredQuery .= " ORDER BY CASE WHEN type = 'income' THEN amount ELSE 0 END $orderDir";
                } elseif ($orderColumnIdx == 6) {
                    // For expenditure column, order by amount but only for expenditure type
                    $filteredQuery .= " ORDER BY CASE WHEN type = 'expenditure' THEN amount ELSE 0 END $orderDir";
                } else {
                    $filteredQuery .= " ORDER BY " . $orderColumns[$orderColumnIdx] . " $orderDir";
                }
            } else {
                $filteredQuery .= " ORDER BY transaction_date DESC";
            }

            // Apply pagination
            $filteredQuery .= " LIMIT $length OFFSET $start";

            // Get final results
            $results = $db->query($filteredQuery)->getResultArray();

            // Format results for DataTables
            $data = [];
            foreach ($results as $row) {
                $amount = number_format($row['amount'], 0, ',', '.');
                $categoryType = $row['type'];
                $income = $categoryType === 'income' ? 'Rp ' . $amount : '';
                $expenditure = $categoryType === 'expenditure' ? 'Rp ' . $amount : '';

                // Add source indicator
                $sourceIcon = '';
                $sourceColor = '';
                switch ($row['source_type']) {
                    case 'invoice':
                        $sourceIcon = '<i class="bx bx-receipt text-primary"></i> ';
                        $sourceColor = 'text-primary';
                        break;
                    case 'payment_transaction':
                        $sourceIcon = '<i class="bx bx-credit-card text-success"></i> ';
                        $sourceColor = 'text-success';
                        break;
                    case 'cash_flow':
                        $sourceIcon = '<i class="bx bx-money text-info"></i> ';
                        $sourceColor = 'text-info';
                        break;
                }

                // Action buttons - only allow edit/delete for manual cash flow entries
                $actionButtons = '';
                if ($row['source_type'] === 'cash_flow') {
                    $actionButtons = '<button class="btn btn-sm btn-warning editData" data-id="' . $row['id'] . '"><i class="bx bx-edit"></i></button> ' .
                        '<button class="btn btn-sm btn-danger deleteData" data-id="' . $row['id'] . '"><i class="bx bx-trash"></i></button>';
                } else {
                    $actionButtons = '<span class="badge bg-secondary">Auto</span>';
                }

                $data[] = [
                    'DT_RowIndex' => $start + count($data) + 1,
                    'id' => $row['id'],
                    'name' => $sourceIcon . $row['name'],
                    'transaction_date' => $row['transaction_date'],
                    'category_name' => '<span class="' . $sourceColor . '">' . $row['category_name'] . '</span><br><small class="text-muted">' . $row['source_description'] . '</small>',
                    'description' => $row['description'],
                    'amount' => 'Rp ' . $amount,
                    'income' => $income,
                    'expenditure' => $expenditure,
                    'type' => $categoryType,
                    'source_type' => $row['source_type'],
                    'action' => $actionButtons
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'summary' => [
                    'income' => $grandTotalIncome,
                    'expenditure' => $grandTotalExpenditure,
                    'saldo' => $grandSaldo
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ArusKas data: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
                'summary' => [
                    'income' => 0,
                    'expenditure' => 0,
                    'saldo' => 0
                ]
            ])->setStatusCode(500);
        }
    }

    // DataTable AJAX handler for kategori kas
    public function categoryList()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'error' => 'Invalid request'
            ])->setStatusCode(400);
        }

        try {
            $db = \Config\Database::connect();
            $model = new \App\Models\KategoriKasModel();

            $request = $this->request;
            $length = $request->getVar('length') ?? 10;
            $start = $request->getVar('start') ?? 0;
            $search = $request->getVar('search')['value'] ?? '';
            $order = $request->getVar('order')[0] ?? ['column' => 1, 'dir' => 'asc'];

            // Start building query
            $builder = $db->table('kategori_kas');

            // Total records
            $totalRecords = $builder->countAllResults(false);

            // Apply search
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('nama', $search)
                    ->orLike('jenis_kas', $search)
                    ->orLike('keterangan', $search)
                    ->groupEnd();
            }

            // Get filtered count
            $recordsFiltered = $builder->countAllResults(false);

            // Apply ordering
            $columns = ['id_category', 'nama', 'jenis_kas', 'keterangan'];
            $orderColumn = $columns[$order['column']] ?? 'nama';
            $orderDir = $order['dir'] ?? 'asc';
            $builder->orderBy($orderColumn, $orderDir);

            // Apply pagination
            $builder->limit($length, $start);

            // Get results
            $results = $builder->get()->getResultArray();

            // Format data for DataTables
            $data = [];
            foreach ($results as $row) {
                // Create action buttons
                $actionButtons = '<div class="btn-group" role="group">';
                $actionButtons .= '<button type="button" class="btn btn-sm btn-outline-primary editData" data-id="' . $row['id_category'] . '" title="Edit">';
                $actionButtons .= '<i class="bx bx-edit"></i>';
                $actionButtons .= '</button>';
                $actionButtons .= '<button type="button" class="btn btn-sm btn-outline-danger deleteData" data-id="' . $row['id_category'] . '" title="Hapus">';
                $actionButtons .= '<i class="bx bx-trash"></i>';
                $actionButtons .= '</button>';
                $actionButtons .= '</div>';

                $data[] = [
                    'DT_RowIndex' => $start + 1,
                    'id_category' => $row['id_category'],
                    'nama' => $row['nama'],
                    'jenis_kas' => ucfirst($row['jenis_kas'] == 'pemasukan' ? 'Pendapatan' : 'Pengeluaran'),
                    'keterangan' => $row['keterangan'],
                    'action' => $actionButtons
                ];
                $start++;
            }

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'error' => null
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in categoryList: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat memuat data'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get single cash flow record for editing
     */
    public function getFlow($id)
    {
        if ($this->request->isAJAX()) {
            try {
                $db = \Config\Database::connect();

                // Get data with category information
                $builder = $db->table('cash_flow cf')
                    ->select('cf.*, k.nama as category_name, k.jenis_kas as jenis, k.id_category as category_id')
                    ->join('kategori_kas k', 'k.id_category = cf.category_id', 'left')
                    ->where('cf.id', $id);

                $data = $builder->get()->getRowArray();

                if ($data) {
                    // Debug: tampilkan data mentah
                    log_message('debug', 'Raw amount from DB: ' . $data['amount']);

                    // Format amount to Indonesian format
                    $data['amount'] = number_format($data['amount'], 0, ',', '.');

                    // Debug: tampilkan data setelah format
                    log_message('debug', 'Formatted amount: ' . $data['amount']);

                    // Format date to d/m/Y
                    $data['transaction_date'] = date('d/m/Y', strtotime($data['transaction_date']));

                    // Pastikan category_id tersedia untuk select2
                    if (!isset($data['category_id']) && isset($data['category_id'])) {
                        $data['category_id'] = $data['category_id'];
                    }

                    // Debug log untuk memastikan data kategori
                    log_message('debug', 'getFlow data: ' . json_encode($data));

                    return $this->response->setJSON([
                        'status' => 'success',
                        'data' => $data,
                        'csrfHash' => csrf_hash()
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(404);
            } catch (\Exception $e) {
                log_message('error', 'Error in getFlow: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat mengambil data',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(500);
            }
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Get single category data for editing
     */
    public function categoryEdit($id)
    {
        if ($this->request->isAJAX()) {
            try {
                $data = $this->kategoriKasModel->find($id);

                if ($data) {
                    return $this->response->setJSON($data);
                }

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data kategori tidak ditemukan'
                ])->setStatusCode(404);
            } catch (\Exception $e) {
                log_message('error', 'Error in categoryEdit: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat mengambil data kategori'
                ])->setStatusCode(500);
            }
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Save or update category data
     */
    public function categorySave()
    {
        if ($this->request->isAJAX()) {
            try {
                $rules = [
                    'nama' => 'required',
                    'jenis_kas' => 'required|in_list[pemasukan,pengeluaran]',
                    'keterangan' => 'required'
                ];

                if (!$this->validate($rules)) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => $this->validator->getErrors(),
                        'csrfHash' => csrf_hash()
                    ])->setStatusCode(400);
                }

                $id = $this->request->getPost('id_category');
                $data = [
                    'nama' => $this->request->getPost('nama'),
                    'jenis_kas' => $this->request->getPost('jenis_kas'),
                    'keterangan' => $this->request->getPost('keterangan')
                ];

                if ($id) {
                    // Update existing category
                    $this->kategoriKasModel->update($id, $data);
                    $message = 'Kategori kas berhasil diupdate';
                } else {
                    // Insert new category
                    $this->kategoriKasModel->insert($data);
                    $message = 'Kategori kas berhasil ditambahkan';
                }

                return $this->response->setJSON([
                    'status' => true,
                    'title' => 'Berhasil',
                    'message' => $message,
                    'csrfHash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error in categorySave: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => false,
                    'title' => 'Error',
                    'message' => 'Terjadi kesalahan saat menyimpan data kategori',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(500);
            }
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Delete a category
     */
    public function categoryDelete($id)
    {
        if ($this->request->isAJAX()) {
            try {
                // Check if category exists
                $category = $this->kategoriKasModel->find($id);
                if (!$category) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kategori kas tidak ditemukan'
                    ])->setStatusCode(404);
                }

                // Check if category is used in cash flow
                $cashFlowModel = new \App\Models\CashFlowModel();
                $usedInCashFlow = $cashFlowModel->where('category_id', $id)->countAllResults();

                if ($usedInCashFlow > 0) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kategori ini tidak dapat dihapus karena sudah digunakan dalam transaksi kas'
                    ])->setStatusCode(400);
                }

                // Delete the category
                $this->kategoriKasModel->delete($id);

                return $this->response->setJSON([
                    'status' => true,
                    'title' => 'Berhasil',
                    'message' => 'Kategori kas berhasil dihapus',
                    'csrfHash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error in categoryDelete: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Terjadi kesalahan saat menghapus kategori'
                ])->setStatusCode(500);
            }
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Delete a cash flow record
     */
    public function flowDelete($id)
    {
        if ($this->request->isAJAX()) {
            try {
                // Check if record exists
                $existingData = $this->cashFlowModel->find($id);
                if (!$existingData) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Data kas tidak ditemukan'
                    ])->setStatusCode(404);
                }

                // Delete the record
                $this->cashFlowModel->delete($id);

                return $this->response->setJSON([
                    'status' => 'success',
                    'title' => 'Berhasil',
                    'message' => 'Data kas berhasil dihapus',
                    'csrfHash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error in flowDelete: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Terjadi kesalahan saat menghapus data kas',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(500);
            }
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /**
     * Delete all cash flow records for a specific month/year
     */
    public function deleteAll()
    {
        if ($this->request->isAJAX()) {
            try {
                $month = $this->request->getPost('month');
                $year = $this->request->getPost('year');

                if (!$month || !$year) {
                    return $this->response->setJSON([
                        'status' => false,
                        'title' => 'Error',
                        'message' => 'Bulan dan tahun harus diisi'
                    ])->setStatusCode(400);
                }

                // Ensure month is padded to 2 digits
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);

                $db = \Config\Database::connect();
                $builder = $db->table('cash_flow');

                // Delete records for specific month/year
                $builder->where('MONTH(transaction_date)', $month)
                    ->where('YEAR(transaction_date)', $year);

                $deletedCount = $builder->countAllResults(false);
                $builder->delete();

                return $this->response->setJSON([
                    'status' => true,
                    'title' => 'Berhasil',
                    'message' => "Berhasil menghapus {$deletedCount} data kas untuk bulan {$month}/{$year}",
                    'csrfHash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error in deleteAll: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => false,
                    'title' => 'Error',
                    'message' => 'Terjadi kesalahan saat menghapus data kas',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(500);
            }
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}
