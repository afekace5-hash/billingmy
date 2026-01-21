<?php

namespace App\Models;

use CodeIgniter\Model;

class ProrateModel extends Model
{
    protected $table = 'prorate';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'invoice_month',
        'start_date',
        'end_date',
        'prorate_amount',
        'description',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'customer_id' => 'required|integer',
        'invoice_month' => 'required',
        'start_date' => 'required|valid_date',
        'end_date' => 'required|valid_date',
        'prorate_amount' => 'required|decimal'
    ];

    protected $validationMessages = [
        'customer_id' => [
            'required' => 'Customer is required',
            'integer' => 'Customer ID must be an integer'
        ],
        'invoice_month' => [
            'required' => 'Invoice month is required'
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'valid_date' => 'Start date must be a valid date'
        ],
        'end_date' => [
            'required' => 'End date is required',
            'valid_date' => 'End date must be a valid date'
        ],
        'prorate_amount' => [
            'required' => 'Prorate amount is required',
            'decimal' => 'Prorate amount must be a decimal number'
        ]
    ];

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
     * Get prorates with customer information
     */
    public function getProratesWithCustomers()
    {
        return $this->select('prorate.*, customers.nama_pelanggan as customer_name, 
                             customers.nomor_layanan, paket_internet.nama_paket as package_name,
                             paket_internet.harga as package_price')
            ->join('customers', 'customers.id_customers = prorate.customer_id', 'left')
            ->join('paket_internet', 'paket_internet.id_paket = customers.paket_internet_id', 'left')
            ->orderBy('prorate.id', 'DESC')
            ->findAll();
    }

    /**
     * Get prorate by customer and month
     */
    public function getProrateByCustomerMonth($customerId, $invoiceMonth)
    {
        return $this->where('customer_id', $customerId)
            ->where('invoice_month', $invoiceMonth)
            ->first();
    }

    /**
     * Check if prorate exists for customer in specific month
     */
    public function existsForCustomerMonth($customerId, $invoiceMonth)
    {
        return $this->where('customer_id', $customerId)
            ->where('invoice_month', $invoiceMonth)
            ->countAllResults() > 0;
    }

    /**
     * Get total prorate amount for a month
     */
    public function getTotalProrateByMonth($invoiceMonth)
    {
        $result = $this->selectSum('prorate_amount')
            ->where('invoice_month', $invoiceMonth)
            ->first();

        return $result['prorate_amount'] ?? 0;
    }

    /**
     * Get prorate statistics
     */
    public function getProrateStatistics()
    {
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        return [
            'current_month_total' => $this->getTotalProrateByMonth($currentMonth),
            'last_month_total' => $this->getTotalProrateByMonth($lastMonth),
            'current_month_count' => $this->where('invoice_month', $currentMonth)->countAllResults(),
            'total_all_time' => $this->selectSum('prorate_amount')->first()['prorate_amount'] ?? 0
        ];
    }
}
