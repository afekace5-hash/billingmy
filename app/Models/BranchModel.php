<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table      = 'branches';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'branch_name',
        'city',
        'payment_type',
        'due_date',
        'day_before_due_date',
        'address',
        'description',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'branch_name' => 'required|min_length[3]|max_length[255]',
        'city' => 'required|min_length[3]|max_length[255]',
        'payment_type' => 'required|in_list[Bayar diawal,Bayar diakhir]',
        'due_date' => 'required|integer|greater_than[0]|less_than_equal_to[31]',
        'day_before_due_date' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[30]',
    ];

    protected $validationMessages = [
        'branch_name' => [
            'required' => 'Branch name is required',
            'min_length' => 'Branch name must be at least 3 characters',
            'max_length' => 'Branch name cannot exceed 255 characters'
        ],
        'city' => [
            'required' => 'City is required',
            'min_length' => 'City must be at least 3 characters',
            'max_length' => 'City cannot exceed 255 characters'
        ],
        'payment_type' => [
            'required' => 'Payment type is required',
            'in_list' => 'Invalid payment type selected'
        ],
        'due_date' => [
            'required' => 'Due date is required',
            'integer' => 'Due date must be a number',
            'greater_than' => 'Due date must be greater than 0',
            'less_than_equal_to' => 'Due date cannot be greater than 31'
        ],
        'day_before_due_date' => [
            'required' => 'Day before due date is required',
            'integer' => 'Day before due date must be a number',
            'greater_than_equal_to' => 'Day before due date cannot be less than 0',
            'less_than_equal_to' => 'Day before due date cannot be greater than 30'
        ]
    ];

    /**
     * Get all branches with pagination for DataTables
     */
    public function getDatatables($params = [])
    {
        try {
            $builder = $this->db->table($this->table);

            // Select columns - make sure table exists first
            if (!$this->db->tableExists($this->table)) {
                return [];
            }

            $builder->select('branches.id, branches.branch_name, branches.city, branches.payment_type, branches.due_date, branches.day_before_due_date, branches.created_at, branches.updated_at, branches.created_by');

            // Join users table if exists
            if ($this->db->tableExists('users')) {
                $builder->select('users.name_user as created_by_name', false);
                $builder->join('users', 'users.id_user = branches.created_by', 'left');
            }

            // Search
            if (!empty($params['search'])) {
                $builder->groupStart();
                $builder->like('branch_name', $params['search']);
                $builder->orLike('city', $params['search']);
                $builder->orLike('payment_type', $params['search']);
                $builder->groupEnd();
            }

            // Order
            if (!empty($params['order'])) {
                $builder->orderBy($params['order']['column'], $params['order']['dir']);
            } else {
                $builder->orderBy('branches.id', 'DESC');
            }

            // Pagination
            if (!empty($params['length']) && $params['length'] != -1) {
                $builder->limit($params['length'], $params['start']);
            }

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'BranchModel getDatatables error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total records
     */
    public function countAll()
    {
        try {
            if (!$this->db->tableExists($this->table)) {
                return 0;
            }
            return $this->db->table($this->table)->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'BranchModel countAll error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count filtered records
     */
    public function countFiltered($params = [])
    {
        try {
            $builder = $this->db->table($this->table);

            if (!$this->db->tableExists($this->table)) {
                return 0;
            }

            if (!empty($params['search'])) {
                $builder->groupStart();
                $builder->like('branch_name', $params['search']);
                $builder->orLike('city', $params['search']);
                $builder->orLike('payment_type', $params['search']);
                $builder->groupEnd();
            }

            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'BranchModel countFiltered error: ' . $e->getMessage());
            return 0;
        }
    }
}
