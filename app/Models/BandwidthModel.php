<?php

namespace App\Models;

use CodeIgniter\Model;

class BandwidthModel extends Model
{
    protected $table = 'bandwidth_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'download_min',
        'download_max',
        'upload_min',
        'upload_max',
        'status',
        'description',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'download_min' => 'required|integer|greater_than[0]',
        'download_max' => 'required|integer|greater_than[0]',
        'upload_min' => 'required|integer|greater_than[0]',
        'upload_max' => 'required|integer|greater_than[0]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Bandwidth profile name is required',
            'min_length' => 'Bandwidth profile name must be at least 3 characters',
            'max_length' => 'Bandwidth profile name cannot exceed 255 characters'
        ],
        'download_min' => [
            'required' => 'Minimum download speed is required',
            'integer' => 'Minimum download speed must be a number',
            'greater_than' => 'Minimum download speed must be greater than 0'
        ],
        'download_max' => [
            'required' => 'Maximum download speed is required',
            'integer' => 'Maximum download speed must be a number',
            'greater_than' => 'Maximum download speed must be greater than 0'
        ],
        'upload_min' => [
            'required' => 'Minimum upload speed is required',
            'integer' => 'Minimum upload speed must be a number',
            'greater_than' => 'Minimum upload speed must be greater than 0'
        ],
        'upload_max' => [
            'required' => 'Maximum upload speed is required',
            'integer' => 'Maximum upload speed must be a number',
            'greater_than' => 'Maximum upload speed must be greater than 0'
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
     * Get active bandwidth profiles
     */
    public function getActiveBandwidthProfiles()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get bandwidth profile by name
     */
    public function getBandwidthProfileByName($name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Check if bandwidth profile is being used
     */
    public function isUsedInPackages($id)
    {
        $db = \Config\Database::connect();
        $result = $db->table('package_profiles')
            ->where('bandwidth_profile_id', $id)
            ->countAllResults();

        return $result > 0;
    }
}
