<?php

namespace App\Models;

use CodeIgniter\Model;

class AutoIsolirConfigModel extends Model
{
    protected $table = 'auto_isolir_config';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'router_id',
        'isolir_ip',
        'isolir_page_url',
        'grace_period_days',
        'is_enabled',
        'last_run',
        'pool_name',
        'profile_name',
        'address_list_name',
        'setup_completed',
        'last_setup_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active auto isolir configurations
     */
    public function getActiveConfigs()
    {
        return $this->where('is_enabled', 1)->findAll();
    }

    /**
     * Get config by router ID
     */
    public function getByRouter($routerId)
    {
        return $this->where('router_id', $routerId)->first();
    }

    /**
     * Update last run time
     */
    public function updateLastRun($configId)
    {
        return $this->update($configId, ['last_run' => date('Y-m-d H:i:s')]);
    }
}
