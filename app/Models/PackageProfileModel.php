<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageProfileModel extends Model
{
    protected $table = 'package_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description',
        'bandwidth_profile_id',
        'group_profile_id',
        'bandwidth_profile',
        'group_profile',
        'price',
        'validity_period',
        'grace_period',
        'auto_renewal',
        'status',
        'default_profile_mikrotik',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'bandwidth_profile_id' => 'permit_empty|integer',
        'group_profile_id' => 'permit_empty|integer',
        'price' => 'required|decimal|greater_than[0]',
        'validity_period' => 'required|integer|greater_than[0]',
        'grace_period' => 'permit_empty|integer|greater_than_equal_to[0]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Package profile name is required',
            'min_length' => 'Package profile name must be at least 3 characters',
            'max_length' => 'Package profile name cannot exceed 255 characters'
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal' => 'Price must be a valid number',
            'greater_than' => 'Price must be greater than 0'
        ],
        'validity_period' => [
            'required' => 'Validity period is required',
            'integer' => 'Validity period must be a number',
            'greater_than' => 'Validity period must be greater than 0'
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
     * Get active package profiles
     */
    public function getActivePackageProfiles()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get package profile with related data
     */
    public function getPackageProfileWithDetails($id = null)
    {
        $builder = $this->db->table($this->table . ' pp');
        $builder->select('pp.*, bp.name as bandwidth_name, gp.name as group_name');
        $builder->join('bandwidth_profiles bp', 'bp.id = pp.bandwidth_profile_id', 'left');
        $builder->join('group_profiles gp', 'gp.id = pp.group_profile_id', 'left');

        if ($id !== null) {
            $builder->where('pp.id', $id);
            return $builder->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get package profile by name
     */
    public function getPackageProfileByName($name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Check if package profile is being used by customers
     */
    public function isUsedByCustomers($id)
    {
        $db = \Config\Database::connect();
        $result = $db->table('customers')
            ->where('package_profile_id', $id)
            ->countAllResults();

        return $result > 0;
    }

    /**
     * Get all package profiles (compatibility method)
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Get MikroTik profile for a specific package (compatibility method)
     */
    public function getMikrotikProfile($packageId)
    {
        $package = $this->find($packageId);
        return $package ? ($package['mikrotik_profile'] ?? 'default') : 'default';
    }

    /**
     * Get package with MikroTik profile information (compatibility method)
     */
    public function getPackageWithProfile($packageId)
    {
        return $this->find($packageId);
    }
}
