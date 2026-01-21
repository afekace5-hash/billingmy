<?php

namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table      = 'areas';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'branch_id',
        'area_name',
        'latitude',
        'longitude',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'branch_id' => 'required|integer',
        'area_name' => 'required|min_length[3]|max_length[255]',
        'latitude' => 'required|decimal',
        'longitude' => 'required|decimal',
    ];

    protected $validationMessages = [
        'branch_id' => [
            'required' => 'Branch is required',
            'integer' => 'Invalid branch selected'
        ],
        'area_name' => [
            'required' => 'Area name is required',
            'min_length' => 'Area name must be at least 3 characters',
            'max_length' => 'Area name cannot exceed 255 characters'
        ],
        'latitude' => [
            'required' => 'Latitude is required',
            'decimal' => 'Latitude must be a valid decimal number'
        ],
        'longitude' => [
            'required' => 'Longitude is required',
            'decimal' => 'Longitude must be a valid decimal number'
        ]
    ];

    /**
     * Get all areas with branch information
     */
    public function getAreasWithBranch()
    {
        return $this->select('areas.*, branches.branch_name')
            ->join('branches', 'branches.id = areas.branch_id', 'left')
            ->orderBy('areas.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get area by ID with branch information
     */
    public function getAreaWithBranch($id)
    {
        return $this->select('areas.*, branches.branch_name')
            ->join('branches', 'branches.id = areas.branch_id', 'left')
            ->where('areas.id', $id)
            ->first();
    }

    /**
     * Get all areas for DataTables with server-side processing
     */
    public function getDatatables($params = [])
    {
        $builder = $this->db->table($this->table);

        $builder->select('areas.id, areas.area_name, areas.latitude, areas.longitude, areas.created_at, areas.updated_at, branches.branch_name');
        $builder->join('branches', 'branches.id = areas.branch_id', 'left');

        // Search
        if (!empty($params['search'])) {
            $builder->groupStart();
            $builder->like('areas.area_name', $params['search']);
            $builder->orLike('branches.branch_name', $params['search']);
            $builder->orLike('areas.latitude', $params['search']);
            $builder->orLike('areas.longitude', $params['search']);
            $builder->groupEnd();
        }

        // Order
        if (!empty($params['order'])) {
            $builder->orderBy($params['order']['column'], $params['order']['dir']);
        } else {
            $builder->orderBy('areas.created_at', 'DESC');
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
        $builder->join('branches', 'branches.id = areas.branch_id', 'left');

        if (!empty($params['search'])) {
            $builder->groupStart();
            $builder->like('areas.area_name', $params['search']);
            $builder->orLike('branches.branch_name', $params['search']);
            $builder->orLike('areas.latitude', $params['search']);
            $builder->orLike('areas.longitude', $params['search']);
            $builder->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get areas by branch ID
     */
    public function getAreasByBranch($branchId)
    {
        return $this->where('branch_id', $branchId)
            ->orderBy('area_name', 'ASC')
            ->findAll();
    }
}
