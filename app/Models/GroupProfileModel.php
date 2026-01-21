<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupProfileModel extends Model
{
    protected $table = 'group_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description',
        'data_owner',
        'router_type',
        'ip_pool_module',
        'parent_pool',
        'local_address',
        'ip_range_start',
        'ip_range_end',
        'dns_server',
        'parent_queue',
        'max_users',
        'session_timeout',
        'idle_timeout',
        'simultaneous_use',
        'bandwidth_profile_id',
        'accounting_update_interval',
        'status',
        'ping_status',
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
        'max_users' => 'permit_empty|integer|greater_than[0]',
        'session_timeout' => 'permit_empty|integer|greater_than[0]',
        'idle_timeout' => 'permit_empty|integer|greater_than[0]',
        'simultaneous_use' => 'permit_empty|integer|greater_than[0]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Group profile name is required',
            'min_length' => 'Group profile name must be at least 3 characters',
            'max_length' => 'Group profile name cannot exceed 255 characters'
        ],
        'max_users' => [
            'integer' => 'Max users must be a number',
            'greater_than' => 'Max users must be greater than 0'
        ],
        'session_timeout' => [
            'integer' => 'Session timeout must be a number',
            'greater_than' => 'Session timeout must be greater than 0'
        ],
        'idle_timeout' => [
            'integer' => 'Idle timeout must be a number',
            'greater_than' => 'Idle timeout must be greater than 0'
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
     * Get active group profiles
     */
    public function getActiveGroupProfiles()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get group profile by name
     */
    public function getGroupProfileByName($name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Check if group profile is being used
     */
    public function isUsedInPackages($id)
    {
        $db = \Config\Database::connect();
        $result = $db->table('package_profiles')
            ->where('group_profile_id', $id)
            ->countAllResults();

        return $result > 0;
    }
}
