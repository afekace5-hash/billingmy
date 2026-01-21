<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentTransactionModel extends Model
{
    protected $table = 'payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'invoice_id',
        'customer_id',
        'transaction_id',
        'payment_gateway',
        'transaction_code',
        'customer_number',
        'customer_name',
        'payment_method',
        'channel',
        'biller',
        'amount',
        'admin_fee',
        'total_amount',
        'status',
        'payment_code',
        'payment_date',
        'expired_at',
        'paid_at',
        'callback_data',
        'response_data',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [];
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
     * Get transactions for DataTables with filtering
     */
    public function getDataTablesData($filters = [])
    {
        $builder = $this->db->table($this->table . ' pt');

        // Select columns
        $builder->select('
            pt.id,
            pt.transaction_code as transaksi_kode,
            pt.customer_number as nomor_pelanggan,
            pt.customer_name as nama,
            pt.payment_method as metode_pembayaran,
            pt.channel,
            pt.biller,
            pt.amount as tagihan,
            pt.admin_fee as admin,
            pt.total_amount as nominal_bayar,
            pt.status,
            pt.payment_code as kode_bayar,
            pt.expired_at as expired,
            DATE_FORMAT(pt.created_at, "%d/%m/%Y %H:%i:%s") as tanggal,
            pt.created_at,
            pt.updated_at
        ');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $builder->where('DATE(pt.created_at) >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('DATE(pt.created_at) <=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $builder->where('pt.status', $filters['status']);
        }

        if (!empty($filters['biller'])) {
            $builder->where('pt.biller', $filters['biller']);
        }

        if (!empty($filters['channel'])) {
            $builder->where('pt.channel', $filters['channel']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('pt.transaction_code', $filters['search'])
                ->orLike('pt.customer_number', $filters['search'])
                ->orLike('pt.customer_name', $filters['search'])
                ->orLike('pt.payment_code', $filters['search'])
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStats($filters = [])
    {
        $builder = $this->getDataTablesData($filters);

        $builder->select('
            COUNT(*) as total_count,
            SUM(CASE WHEN status = "sukses" THEN total_amount ELSE 0 END) as total_success,
            COUNT(CASE WHEN status = "sukses" THEN 1 END) as count_success,
            SUM(CASE WHEN status = "pending" THEN total_amount ELSE 0 END) as total_pending,
            COUNT(CASE WHEN status = "pending" THEN 1 END) as count_pending,
            SUM(CASE WHEN status IN ("failed", "expired") THEN total_amount ELSE 0 END) as total_failed,
            COUNT(CASE WHEN status IN ("failed", "expired") THEN 1 END) as count_failed,
            SUM(total_amount) as total_amount
        ');

        return $builder->get()->getRowArray();
    }

    /**
     * Get transactions updated after specific time
     */
    public function getUpdatedTransactions($lastCheckTime)
    {
        $builder = $this->db->table($this->table);

        $builder->select('
            id,
            transaction_code,
            customer_name,
            total_amount,
            status,
            payment_method,
            created_at,
            updated_at
        ');

        $builder->where('updated_at >', $lastCheckTime);
        $builder->orderBy('updated_at', 'DESC');
        $builder->limit(10);

        return $builder->get()->getResultArray();
    }

    /**
     * Simulate payment callback (for testing)
     */
    public function simulatePaymentCallback($transactionCode, $status, $callbackData = [])
    {
        $data = [
            'status' => $status,
            'paid_at' => $status === 'sukses' ? date('Y-m-d H:i:s') : null,
            'callback_data' => json_encode($callbackData),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->where('transaction_code', $transactionCode)->set($data)->update();
    }
}
