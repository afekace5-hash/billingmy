<?php

namespace App\Models;

use CodeIgniter\Model;

class OdpModel extends Model
{
    protected $table      = 'odps';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'area_id',
        'district',
        'village',
        'parent_odp',
        'odp_name',
        'customer_active',
        'core',
        'latitude',
        'longitude',
        'address',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'area_id' => 'required|integer',
        'odp_name' => 'required|min_length[3]|max_length[255]',
        'district' => 'required|min_length[2]|max_length[100]',
        'village' => 'required|min_length[2]|max_length[100]',
        'core' => 'required',
    ];

    protected $validationMessages = [
        'area_id' => [
            'required' => 'Area is required',
            'integer' => 'Invalid area selected'
        ],
        'odp_name' => [
            'required' => 'ODP name is required',
            'min_length' => 'ODP name must be at least 3 characters',
            'max_length' => 'ODP name cannot exceed 255 characters'
        ],
        'core' => [
            'required' => 'Core is required',
            'integer' => 'Core must be a number',
            'greater_than' => 'Core must be greater than 0'
        ]
    ];

    /**
     * Get all ODPs with area information
     */
    public function getOdpsWithRelations()
    {
        return $this->select('odps.*, areas.area_name, branches.branch_name')
            ->join('areas', 'areas.id = odps.area_id', 'left')
            ->join('branches', 'branches.id = areas.branch_id', 'left')
            ->orderBy('odps.id', 'DESC')
            ->findAll();
    }

    /**
     * Get ODP by ID with relations
     */
    public function getOdpWithRelations($id)
    {
        return $this->select('odps.*, branches.branch_name, areas.area_name')
            ->join('areas', 'areas.id = odps.area_id', 'left')
            ->join('branches', 'branches.id = areas.branch_id', 'left')
            ->where('odps.id', $id)
            ->first();
    }

    /**
     * Get all ODPs for DataTables with server-side processing
     */
    public function getDatatables($params = [])
    {
        $builder = $this->db->table($this->table);

        $builder->select('odps.id, odps.area_id, odps.odp_name, odps.customer_active, odps.core, odps.latitude, odps.longitude, odps.created_at, odps.updated_at, branches.branch_name, areas.area_name');
        $builder->join('areas', 'areas.id = odps.area_id', 'left');
        $builder->join('branches', 'branches.id = areas.branch_id', 'left');

        // Search
        if (!empty($params['search'])) {
            $builder->groupStart();
            $builder->like('odps.odp_name', $params['search']);
            $builder->orLike('branches.branch_name', $params['search']);
            $builder->orLike('areas.area_name', $params['search']);
            $builder->groupEnd();
        }

        // Order
        if (!empty($params['order'])) {
            $builder->orderBy($params['order']['column'], $params['order']['dir']);
        } else {
            $builder->orderBy('odps.id', 'DESC');
        }

        // Limit
        if (!empty($params['limit'])) {
            $builder->limit($params['limit'], $params['offset'] ?? 0);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get total count for DataTables
     */
    public function countAll()
    {
        return $this->countAllResults();
    }

    /**
     * Get filtered count for DataTables
     */
    public function countFiltered($params = [])
    {
        $builder = $this->db->table($this->table);
        $builder->join('areas', 'areas.id = odps.area_id', 'left');
        $builder->join('branches', 'branches.id = areas.branch_id', 'left');

        if (!empty($params['search'])) {
            $builder->groupStart();
            $builder->like('odps.odp_name', $params['search']);
            $builder->orLike('branches.branch_name', $params['search']);
            $builder->orLike('areas.area_name', $params['search']);
            $builder->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get ODPs by area ID
     */
    public function getOdpsByArea($areaId)
    {
        return $this->where('area_id', $areaId)
            ->orderBy('odp_name', 'ASC')
            ->findAll();
    }

    /**
     * Update customer active count
     */
    public function updateCustomerCount($odpId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('customers')
            ->where('odp_id', $odpId)
            ->where('login', 'enable')
            ->countAllResults();

        return $this->update($odpId, ['customer_active' => $count]);
    }
}
