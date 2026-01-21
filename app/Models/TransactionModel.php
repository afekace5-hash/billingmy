<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'branch',
        'date',
        'transaction_name',
        'payment_method',
        'category',
        'description',
        'type',
        'amount',
        'created_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'date' => 'required|valid_date',
        'transaction_name' => 'required|min_length[3]|max_length[255]',
        'payment_method' => 'required',
        'category' => 'required',
        'type' => 'required|in_list[in,out]',
        'amount' => 'required|decimal|greater_than[0]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get summary for specific month and year
     * Returns both: system totals (cash_flow + invoices + gateway) AND transactions table totals
     */
    public function getSummary($month = null, $year = null)
    {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');

        // 1. Get from transactions table (manual entries)
        $transBuilder = $this->db->table('transactions');
        $transBuilder->select('
            SUM(CASE WHEN type = "in" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "out" THEN amount ELSE 0 END) as total_outcome
        ');
        $transBuilder->where('MONTH(date)', $month);
        $transBuilder->where('YEAR(date)', $year);
        $transData = $transBuilder->get()->getRowArray();

        // 2. Get from cash_flow table
        $cashFlowBuilder = $this->db->table('cash_flow');
        $cashFlowBuilder->select('
            SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expenditure" THEN amount ELSE 0 END) as total_outcome
        ');
        $cashFlowData = $cashFlowBuilder->get()->getRowArray();

        // 3. Get from customer_invoices table
        $invoiceBuilder = $this->db->table('customer_invoices');
        $invoiceBuilder->select('
            SUM(CASE WHEN status = "paid" THEN bill ELSE 0 END) as total_income
        ');
        $invoiceData = $invoiceBuilder->get()->getRowArray();

        // 4. Get from payment_transactions table
        $gatewayBuilder = $this->db->table('payment_transactions');
        $gatewayBuilder->select('
            SUM(CASE WHEN status = "settlement" OR status = "capture" THEN amount ELSE 0 END) as total_income
        ');
        $gatewayData = $gatewayBuilder->get()->getRowArray();

        // Calculate system totals (for small text)
        $systemIncome = ($cashFlowData['total_income'] ?? 0) +
            ($invoiceData['total_income'] ?? 0) +
            ($gatewayData['total_income'] ?? 0);
        $systemOutcome = ($cashFlowData['total_outcome'] ?? 0);
        $systemSaldo = $systemIncome - $systemOutcome;

        // Calculate transactions totals (for large text)
        $totalIncome = $transData['total_income'] ?? 0;
        $totalOutcome = $transData['total_outcome'] ?? 0;
        $totalSaldo = $totalIncome - $totalOutcome;

        return [
            'total_income' => $totalIncome,
            'total_outcome' => $totalOutcome,
            'total_saldo' => $totalSaldo,
            'system_income' => $systemIncome,
            'system_outcome' => $systemOutcome,
            'system_saldo' => $systemSaldo
        ];
    }

    /**
     * Get transactions for DataTables
     */
    public function getDataTable($params = [])
    {
        $builder = $this->db->table('transactions');

        // Join with branch table to get branch name
        $builder->select('transactions.*, branches.branch_name');
        $builder->join('branches', 'branches.id = transactions.branch', 'left');

        // Filter by month/year
        if (!empty($params['month'])) {
            $builder->where('MONTH(transactions.date)', $params['month']);
        }
        if (!empty($params['year'])) {
            $builder->where('YEAR(transactions.date)', $params['year']);
        }

        // Search
        if (!empty($params['search'])) {
            $builder->groupStart()
                ->like('transactions.transaction_name', $params['search'])
                ->orLike('branches.branch_name', $params['search'])
                ->orLike('transactions.payment_method', $params['search'])
                ->orLike('transactions.category', $params['search'])
                ->orLike('transactions.description', $params['search'])
                ->groupEnd();
        }

        return $builder;
    }
}
