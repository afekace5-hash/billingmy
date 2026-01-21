<?php

namespace App\Models;

use CodeIgniter\Model;

class BillingLinkModel extends Model
{
    protected $table      = 'billing_links';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'token',
        'nomor_layanan',
        'customer_id',
        'amount',
        'description',
        'status',
        'payment_gateway',
        'payment_reference',
        'expires_at',
        'paid_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Generate unique token for billing link
     */
    public function generateToken()
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while ($this->where('token', $token)->first());

        return $token;
    }

    /**
     * Create billing link for customer
     */
    public function createBillingLink($data)
    {
        $data['token'] = $this->generateToken();

        // Set default expiry to 24 hours
        if (!isset($data['expires_at'])) {
            $data['expires_at'] = date('Y-m-d H:i:s', strtotime('+24 hours'));
        }

        return $this->insert($data);
    }

    /**
     * Get billing link by token
     */
    public function getByToken($token)
    {
        return $this->select('billing_links.*, customers.nama as customer_name, customers.alamat as customer_address')
            ->join('customers', 'customers.id = billing_links.customer_id', 'left')
            ->where('billing_links.token', $token)
            ->first();
    }

    /**
     * Get billing links by nomor layanan
     */
    public function getByNomorLayanan($nomorLayanan)
    {
        return $this->select('billing_links.*, customers.nama as customer_name')
            ->join('customers', 'customers.id = billing_links.customer_id', 'left')
            ->where('billing_links.nomor_layanan', $nomorLayanan)
            ->orderBy('billing_links.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($token, $status, $paymentData = [])
    {
        $updateData = ['status' => $status];

        if ($status === 'paid') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }

        if (!empty($paymentData)) {
            $updateData = array_merge($updateData, $paymentData);
        }

        return $this->where('token', $token)->set($updateData)->update();
    }

    /**
     * Get expired billing links
     */
    public function getExpiredLinks()
    {
        return $this->where('status', 'pending')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->findAll();
    }

    /**
     * Mark expired links
     */
    public function markExpiredLinks()
    {
        return $this->where('status', 'pending')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->set(['status' => 'expired'])
            ->update();
    }
}
