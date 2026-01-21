<?php

namespace App\Models;

use CodeIgniter\Model;

class PppoeAccountModel extends Model
{
    protected $table = 'pppoe_accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'server_id',
        'customer_id',
        'pppoe_id',
        'username',
        'password',
        'profile_name',
        'remote_address',
        'local_address',
        'mac_address',
        'ip_address',
        'status',
        'disabled',
        'last_sync',
        'radius_reply_attributes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'radius_reply_attributes' => '?json'  // ? prefix allows NULL values
    ];
    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'server_id' => 'permit_empty|integer',
        'customer_id' => 'required|integer',
        'username' => 'required|min_length[3]|max_length[64]|is_unique[pppoe_accounts.username,id,{id}]',
        'password' => 'required|min_length[3]|max_length[64]',
        'status' => 'permit_empty|in_list[active,suspended,expired]'
    ];
    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username PPPoE sudah digunakan'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = ['ensureJsonFieldsOnUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Ensure JSON fields are handled properly during updates (including soft deletes)
     */
    protected function ensureJsonFieldsOnUpdate(array $data): array
    {
        // During soft delete, only deleted_at should be in the data
        // We need to ensure radius_reply_attributes is not being set to null
        if (isset($data['data'])) {
            // If radius_reply_attributes is in the data and is null, remove it
            // This prevents the "not nullable" error during soft delete
            if (
                array_key_exists('radius_reply_attributes', $data['data']) &&
                $data['data']['radius_reply_attributes'] === null
            ) {
                unset($data['data']['radius_reply_attributes']);
            }
        }

        return $data;
    }

    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            // For PPPoE, we typically store password in plain text for RADIUS
            // But you can implement MD5/SHA1 if needed
            // $data['data']['password'] = md5($data['data']['password']);
        }
        return $data;
    }

    /**
     * Sync to RADIUS tables after insert/update
     */
    protected function syncToRadius(array $data)
    {
        if (isset($data['id'])) {
            $id = $data['id'];
        } elseif (isset($data['data']['customer_id'])) {
            $id = $this->getInsertID();
        } else {
            return $data;
        }

        $account = $this->find($id);
        if ($account) {
            $this->syncAccountToRadius($account);
        }

        return $data;
    }

    /**
     * Remove from RADIUS before delete
     */
    protected function removeFromRadius(array $data)
    {
        if (isset($data['id'])) {
            $account = $this->find($data['id']);
            if ($account) {
                $this->removeAccountFromRadius($account['username']);
            }
        }
        return $data;
    }

    /**
     * Sync account to RADIUS tables
     */
    public function syncAccountToRadius($account)
    {
        $db = \Config\Database::connect();

        try {
            $db->transBegin();

            $username = $account['username'];

            // Remove existing entries
            $db->table('radcheck')->where('username', $username)->delete();
            $db->table('radreply')->where('username', $username)->delete();

            if ($account['status'] === 'active') {
                // Add authentication entry
                $db->table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $account['password']
                ]);

                // Add profile/bandwidth limit if specified
                if (!empty($account['profile_name'])) {
                    // Get package details for bandwidth
                    $package = $db->table('package_profiles')
                        ->where('bandwidth_profile', $account['profile_name'])
                        ->get()
                        ->getRow();

                    if ($package) {
                        // Extract bandwidth from profile name (e.g., "10 Mbps" -> 10000000)
                        preg_match('/(\d+)\s*mbps/i', $package->bandwidth_profile, $matches);
                        $bandwidth = isset($matches[1]) ? intval($matches[1]) * 1000000 : 1000000;

                        // Add bandwidth limiting attributes
                        $replyAttributes = [
                            [
                                'username' => $username,
                                'attribute' => 'Mikrotik-Rate-Limit',
                                'op' => '=',
                                'value' => $bandwidth . '/' . $bandwidth // Upload/Download
                            ]
                        ];

                        // Add static IP if specified
                        if (!empty($account['ip_address'])) {
                            $replyAttributes[] = [
                                'username' => $username,
                                'attribute' => 'Framed-IP-Address',
                                'op' => '=',
                                'value' => $account['ip_address']
                            ];
                        }

                        // Add custom attributes from JSON
                        if (!empty($account['radius_reply_attributes'])) {
                            $customAttrs = json_decode($account['radius_reply_attributes'], true);
                            if (is_array($customAttrs)) {
                                foreach ($customAttrs as $attr => $value) {
                                    $replyAttributes[] = [
                                        'username' => $username,
                                        'attribute' => $attr,
                                        'op' => '=',
                                        'value' => $value
                                    ];
                                }
                            }
                        }

                        // Insert all reply attributes
                        foreach ($replyAttributes as $attr) {
                            $db->table('radreply')->insert($attr);
                        }
                    }
                }
            }

            $db->transCommit();
            log_message('info', "PPPoE account synced to RADIUS: {$username}");
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Failed to sync PPPoE account to RADIUS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove account from RADIUS tables
     */
    public function removeAccountFromRadius($username)
    {
        $db = \Config\Database::connect();

        try {
            $db->table('radcheck')->where('username', $username)->delete();
            $db->table('radreply')->where('username', $username)->delete();
            log_message('info', "PPPoE account removed from RADIUS: {$username}");
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to remove PPPoE account from RADIUS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get account with customer info
     */
    public function getAccountWithCustomer($id)
    {
        return $this->select('pppoe_accounts.*, customers.nama_pelanggan, customers.telepphone')
            ->join('customers', 'customers.id_customers = pppoe_accounts.customer_id')
            ->find($id);
    }

    /**
     * Get accounts by customer
     */
    public function getAccountsByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->findAll();
    }

    /**
     * Generate unique username
     */
    public function generateUniqueUsername($prefix = 'user')
    {
        do {
            $username = $prefix . '_' . date('Ymd') . '_' . rand(1000, 9999);
        } while ($this->where('username', $username)->first());

        return $username;
    }

    /**
     * Generate random password
     */
    public function generateRandomPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }
}
