<?php

namespace App\Models;

use CodeIgniter\Model;

class WithdrawModel extends Model
{
    protected $table            = 'withdraws';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'amount',
        'bank_name',
        'account_number',
        'account_name',
        'notes',
        'admin_notes',
        'status',
        'disbursement_provider',
        'disbursement_reference',
        'disbursement_status',
        'disbursement_fee',
        'disbursement_response',
        'auto_disburse',
        'requested_by',
        'requested_at',
        'processed_by',
        'processed_at',
        'completed_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // DataTables
    protected $column_order = ['id', 'code', 'amount', 'bank_name', 'status', 'created_at'];
    protected $column_search = ['code', 'bank_name', 'account_name', 'status'];
    protected $order = ['id' => 'DESC'];

    /**
     * Get datatables query
     */
    private function _getDatatablesQuery()
    {
        $builder = $this->db->table($this->table);

        // Handle both GET and POST requests
        $searchValue = $_POST['search']['value'] ?? $_GET['search']['value'] ?? '';

        $i = 0;
        foreach ($this->column_search as $item) {
            if ($searchValue) {
                if ($i === 0) {
                    $builder->groupStart();
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }

                if (count($this->column_search) - 1 == $i) {
                    $builder->groupEnd();
                }
            }
            $i++;
        }

        // Handle ordering - support both GET and POST
        $orderData = $_POST['order'] ?? $_GET['order'] ?? null;
        if (isset($orderData)) {
            $builder->orderBy($this->column_order[$orderData['0']['column']], $orderData['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $builder->orderBy(key($order), $order[key($order)]);
        }

        return $builder;
    }

    /**
     * Get datatables data
     */
    public function getDatatables()
    {
        $builder = $this->_getDatatablesQuery();

        // Support both GET and POST
        $length = $_POST['length'] ?? $_GET['length'] ?? -1;
        $start = $_POST['start'] ?? $_GET['start'] ?? 0;

        if (isset($length) && $length != -1) {
            $builder->limit($length, $start);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }

    /**
     * Count filtered records
     */
    public function countFiltered()
    {
        $builder = $this->_getDatatablesQuery();
        return $builder->countAllResults();
    }

    /**
     * Count all records
     */
    public function countAll()
    {
        return $this->db->table($this->table)->countAll();
    }
}
