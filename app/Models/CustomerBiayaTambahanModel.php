<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerBiayaTambahanModel extends Model
{
    protected $table = 'customer_biaya_tambahan';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'biaya_tambahan_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all biaya tambahan for a specific customer
     */
    public function getBiayaTambahanByCustomer($customerId)
    {
        return $this->select('customer_biaya_tambahan.*, biaya_tambahan.nama_biaya, biaya_tambahan.jumlah, biaya_tambahan.kategori')
            ->join('biaya_tambahan', 'biaya_tambahan.id = customer_biaya_tambahan.biaya_tambahan_id')
            ->where('customer_biaya_tambahan.customer_id', $customerId)
            ->where('biaya_tambahan.status', 1)
            ->findAll();
    }

    /**
     * Get total biaya tambahan for a specific customer
     */
    public function getTotalBiayaTambahanByCustomer($customerId)
    {
        $result = $this->select('SUM(biaya_tambahan.jumlah) as total')
            ->join('biaya_tambahan', 'biaya_tambahan.id = customer_biaya_tambahan.biaya_tambahan_id')
            ->where('customer_biaya_tambahan.customer_id', $customerId)
            ->where('biaya_tambahan.status', 1)
            ->first();

        return $result['total'] ?? 0;
    }

    /**
     * Add biaya tambahan to customer
     */
    public function addBiayaTambahanToCustomer($customerId, $biayaTambahanIds)
    {
        // Remove existing records for this customer
        $this->where('customer_id', $customerId)->delete();

        // Add new records
        if (!empty($biayaTambahanIds)) {
            $data = [];
            foreach ($biayaTambahanIds as $biayaId) {
                if (!empty($biayaId)) {
                    $data[] = [
                        'customer_id' => $customerId,
                        'biaya_tambahan_id' => $biayaId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }

            if (!empty($data)) {
                return $this->insertBatch($data);
            }
        }

        return true;
    }

    /**
     * Remove all biaya tambahan from customer
     */
    public function removeBiayaTambahanFromCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->delete();
    }

    /**
     * Get customers who have specific biaya tambahan
     */
    public function getCustomersByBiayaTambahan($biayaTambahanId)
    {
        return $this->select('customer_biaya_tambahan.*, customers.nama_pelanggan, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = customer_biaya_tambahan.customer_id')
            ->where('customer_biaya_tambahan.biaya_tambahan_id', $biayaTambahanId)
            ->findAll();
    }
}
